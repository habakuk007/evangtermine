import contextlib
import io
import json
import subprocess
import tempfile
import unittest
from pathlib import Path

import cross_review as cr


class SettingsTest(unittest.TestCase):
    def setUp(self):
        self.dir = tempfile.TemporaryDirectory()
        self.addCleanup(self.dir.cleanup)
        self.path = Path(self.dir.name) / "sub" / "cross-review.json"

    def write(self, text):
        self.path.parent.mkdir(parents=True, exist_ok=True)
        self.path.write_text(text, encoding="utf-8")

    def test_missing_file_means_off(self):
        s = cr.read_settings(self.path, mode_override="")
        self.assertEqual((s["mode"], s["fileExists"], s["autoFix"], s["autoPush"]), ("off", False, "full", True))

    def test_valid_file_is_read(self):
        self.write('{"mode": "both-tools", "opencodeModel": "p/m-1", "autoFix": "blocking", "autoPush": false}')
        s = cr.read_settings(self.path, mode_override="")
        self.assertEqual((s["mode"], s["opencodeModel"], s["autoFix"], s["autoPush"], s["problems"]),
                         ("both-tools", "p/m-1", "blocking", False, []))

    def test_unknown_values_fall_back_and_are_reported(self):
        self.write('{"mode": "always", "autoFix": "x", "autoPush": "yes", "claudeModel": "a b"}')
        s = cr.read_settings(self.path, mode_override="")
        self.assertEqual((s["mode"], s["autoFix"], s["autoPush"], s["claudeModel"]), ("off", "full", True, ""))
        self.assertEqual(len(s["problems"]), 4)

    def test_broken_json_means_off_with_a_problem(self):
        self.write("{nope")
        s = cr.read_settings(self.path, mode_override="")
        self.assertEqual(s["mode"], "off")
        self.assertEqual(len(s["problems"]), 1)

    def test_env_overrides_only_the_mode(self):
        self.write('{"mode": "same-tool", "autoFix": "none"}')
        s = cr.read_settings(self.path, mode_override=" Other-Tool ")
        self.assertEqual((s["mode"], s["autoFix"], s["modeFromEnv"]), ("other-tool", "none", True))

    def test_invalid_env_mode_means_off(self):
        s = cr.read_settings(self.path, mode_override="maybe")
        self.assertEqual((s["mode"], len(s["problems"])), ("off", 1))

    def test_setup_writes_a_file_that_reads_back(self):
        s = cr.write_settings(self.path, "ask", claude_model="m-2", auto_fix="none", auto_push=False)
        self.assertEqual((s["mode"], s["claudeModel"], s["autoFix"], s["autoPush"], s["fileExists"]), ("ask", "m-2", "none", False, True))
        self.assertEqual(json.loads(self.path.read_text(encoding="utf-8"))["mode"], "ask")

    def test_setup_refuses_bad_values(self):
        for kwargs in ({"mode": "x"}, {"mode": "off", "auto_fix": "x"}, {"mode": "off", "opencode_model": "a b; rm"}):
            with self.assertRaises(cr.CrossReviewError):
                cr.write_settings(self.path, **kwargs)
        self.assertFalse(self.path.exists())

    def test_to_bool(self):
        self.assertEqual([cr.to_bool(v, "x") for v in ("true", "Yes", "1", "off", "no")], [True, True, True, False, False])
        with self.assertRaises(cr.CrossReviewError):
            cr.to_bool("maybe", "x")


class ReviewTextTest(unittest.TestCase):
    def test_takes_the_last_marker_pair(self):
        raw = "REVIEW_BEGIN\nold\nREVIEW_END\nnoise\nREVIEW_BEGIN\nnew\nREVIEW_END\ntrailing"
        self.assertEqual(cr.review_text(raw), "new")

    def test_markers_inside_a_line_do_not_count(self):
        self.assertIsNone(cr.review_text("text REVIEW_BEGIN text\nREVIEW_END"))

    def test_missing_or_empty_review(self):
        self.assertIsNone(cr.review_text("no markers"))
        self.assertIsNone(cr.review_text("REVIEW_BEGIN\nREVIEW_END"))

    def test_ansi_escapes_are_removed(self):
        self.assertEqual(cr.review_text("\x1b[32mREVIEW_BEGIN\x1b[0m\nok\nREVIEW_END"), "ok")


class PostableReviewTest(unittest.TestCase):
    def test_mentions_are_wrapped(self):
        self.assertEqual(cr.postable_review("ask @alice-b and @bob.x."), "ask `@alice-b` and `@bob.x`.")

    def test_images_and_html_are_dropped(self):
        out = cr.postable_review('a ![x](http://e.com/i.png) b <img src="x"> c <script>d</script>')
        self.assertEqual(out, "a  b  c d")

    def test_links_stay_only_when_the_diff_has_them(self):
        diff = "see https://known.example/doc here"
        self.assertEqual(cr.postable_review("[doc](https://known.example/doc)", diff), "[doc](https://known.example/doc)")
        self.assertEqual(cr.postable_review("[doc](https://evil.example/x)", diff), "doc")
        self.assertEqual(cr.postable_review("go https://evil.example/x.", diff), "go [link removed]")
        self.assertEqual(cr.postable_review("go https://known.example/doc.", diff), "go https://known.example/doc.")
        self.assertEqual(cr.postable_review("go https://known.example/doc/more", diff), "go [link removed]")

    def test_other_link_forms_are_removed(self):
        diff = "see https://known.example/doc"
        out = cr.postable_review("[a][1] and <https://evil.example/x> and [b](javascript:alert(1))\n\n[1]: https://evil.example/y", diff)
        self.assertNotIn("evil.example", out)
        self.assertNotIn("javascript:", out)
        self.assertNotIn("](", out)
        self.assertNotIn("[a][1]", out)
        self.assertNotIn("[1]:", out)
        self.assertIn("a and", out)

    def test_control_characters_are_removed(self):
        self.assertEqual(cr.postable_review("a\x00b\x07c\x7f"), "abc")


class SecretTest(unittest.TestCase):
    def test_kinds(self):
        samples = {
            "private key block": "-----BEGIN RSA PRIVATE KEY-----",
            "AWS access key": "AKIAABCDEFGHIJKLMNOP",
            "GitHub token": "ghp_" + "a" * 36,
            "Bearer token": "Authorization: Bearer abcdefghijklmnopqrstuvwxyz",
            "credential assignment": 'password = "hunter2hunter2"',
        }
        for kind, text in samples.items():
            self.assertEqual(cr.find_secrets(text), [kind], kind)

    def test_plain_text_is_clean(self):
        self.assertEqual(cr.find_secrets("The password is checked in the HAL layer."), [])

    def test_extract_refuses_a_secret_and_a_missing_review(self):
        with tempfile.TemporaryDirectory() as d:
            out = Path(d) / "review-claude.txt"
            self.assertFalse(cr.extract(out)["Ok"])
            out.write_text("REVIEW_BEGIN\nkey AKIAABCDEFGHIJKLMNOP\nREVIEW_END\n", encoding="utf-8")
            self.assertFalse(cr.extract(out)["Ok"])
            out.write_text("REVIEW_BEGIN\n## Review\nissue: x\nREVIEW_END\n", encoding="utf-8")
            result = cr.extract(out)
            self.assertEqual((result["Ok"], result["Text"]), (True, "## Review\nissue: x"))

    def test_extract_refuses_an_oversized_review(self):
        with tempfile.TemporaryDirectory() as d:
            out = Path(d) / "review-claude.txt"
            out.write_text("REVIEW_BEGIN\n" + "x" * (cr.MAX_REVIEW_BYTES + 1) + "\nREVIEW_END\n", encoding="utf-8")
            self.assertFalse(cr.extract(out)["Ok"])


class ArgumentTest(unittest.TestCase):
    def test_unsafe_values_are_refused(self):
        for kwargs in ({"pr_number": "7; rm"}, {"repo_name": "a b"}, {"destination": "x;y"}, {"issue_id": "12-3"}):
            with self.assertRaises(cr.CrossReviewError):
                cr.check_arguments(**kwargs)

    def test_safe_values_pass(self):
        cr.check_arguments("7", "evangtermine", "release/1.0", "154")

    def test_reviewer_commands(self):
        with tempfile.TemporaryDirectory() as d:
            (Path(d) / "AGENTS.md.txt").write_text("x", encoding="utf-8")
            args, stdin = cr.reviewer_command("claude", "repo", d, "7", "m-1")
            self.assertIn("Read,Grep,Glob", args)
            self.assertEqual(args[args.index("--permission-mode") + 1], "dontAsk")
            self.assertEqual(args[args.index("--agent") + 1], "cross-reviewer")
            self.assertEqual(stdin, Path(d) / "prompt.md")
            args, stdin = cr.reviewer_command("opencode", "repo", d, "7")
            self.assertEqual((args[:2], stdin, "-m" in args), (["opencode", "run"], None, False))
            self.assertEqual(args[args.index("--agent") + 1], "cross-reviewer")
            self.assertIn(str(Path(d) / "AGENTS.md.txt"), args)
            with self.assertRaises(cr.CrossReviewError):
                cr.reviewer_command("claude", "repo", d, "7", "bad model;")


class ReviewerEnvTest(unittest.TestCase):
    def test_service_tokens_and_secrets_are_not_passed_on(self):
        env = cr.reviewer_env({
            "PATH": "p", "HOME": "h", "ANTHROPIC_API_KEY": "k", "CLAUDE_CODE_OAUTH_TOKEN": "t",
            "GH_TOKEN": "x", "GITHUB_TOKEN": "x", "HABAKUK_AI_TOKEN": "x", "NPM_TOKEN": "x",
            "DB_PASSWORD": "x", "MY_SECRET": "x"})
        self.assertEqual(sorted(env), ["ANTHROPIC_API_KEY", "CLAUDE_CODE_OAUTH_TOKEN", "HOME", "PATH"])


class AgentFallbackTest(unittest.TestCase):
    def test_detects_the_silent_fallback_to_the_default_agent(self):
        log = "agent \"x\" is a subagent, not a primary agent. Falling back to default agent"
        self.assertTrue(cr.fell_back_to_default_agent("! " + log + "\n"))
        self.assertTrue(cr.fell_back_to_default_agent(log))
        self.assertTrue(cr.fell_back_to_default_agent('agent "x" not found. Falling back to default agent'))

    def test_a_normal_log_is_not_a_fallback(self):
        self.assertFalse(cr.fell_back_to_default_agent("> cross-reviewer - model\nRead docs/x.md"))


class GitTest(unittest.TestCase):
    """Preflight and prepare against a throwaway repository with a local 'origin'."""

    def run_git(self, cwd, *args):
        subprocess.run(["git", "-c", "user.name=t", "-c", "user.email=t@example.com", *args],
                       cwd=cwd, check=True, capture_output=True)

    def setUp(self):
        self.dir = tempfile.TemporaryDirectory()
        self.addCleanup(self.dir.cleanup)
        root = Path(self.dir.name)
        self.origin, self.repo, self.base = root / "origin.git", root / "repo", root / "tmp"
        self.base.mkdir()
        self.run_git(root, "init", "--bare", "-b", "develop", str(self.origin))
        self.run_git(root, "clone", str(self.origin), str(self.repo))
        (self.repo / "AGENTS.md").write_text("rules\n", encoding="utf-8")
        self.run_git(self.repo, "add", "-A")
        self.run_git(self.repo, "commit", "-m", "base")
        self.run_git(self.repo, "push", "origin", "HEAD:develop")
        self.run_git(self.repo, "checkout", "-b", "feature/1-x")
        (self.repo / "a.txt").write_text("one\n", encoding="utf-8")
        self.run_git(self.repo, "add", "-A")
        self.run_git(self.repo, "commit", "-m", "change")

    def test_preflight_reports_an_unpushed_branch(self):
        result = cr.preflight(self.repo, "develop")
        self.assertEqual((result["Ok"], result["Problems"]), (False, ["Branch 'feature/1-x' is not pushed to origin."]))

    def test_preflight_ok_when_pushed_and_clean(self):
        self.run_git(self.repo, "push", "origin", "feature/1-x")
        self.assertEqual(cr.preflight(self.repo, "develop")["Ok"], True)

    def test_preflight_reports_a_dirty_tree_and_a_bad_destination(self):
        self.run_git(self.repo, "push", "origin", "feature/1-x")
        (self.repo / "b.txt").write_text("x\n", encoding="utf-8")
        self.assertEqual(cr.preflight(self.repo, "develop")["Problems"], ["The working tree is not clean."])
        self.assertIn("Destination branch origin/nope does not resolve.", cr.preflight(self.repo, "nope")["Problems"])

    def test_prepare_builds_the_input_outside_the_repository(self):
        result = cr.prepare(self.repo, "develop", "7", "evangtermine", "claude", issue_id="42", base_directory=self.base)
        tmp = Path(result["Tmp"])
        self.assertTrue(cr.TMP_RE.match(tmp.name))
        self.assertEqual(sorted(p.name for p in tmp.iterdir()), ["AGENTS.md.txt", "commits.txt", "context.md", "pr.diff", "prompt.md"])
        self.assertIn("+one", (tmp / "pr.diff").read_text(encoding="utf-8"))
        self.assertIn("## GitHub issue 42", (tmp / "context.md").read_text(encoding="utf-8"))
        self.assertIn("REVIEW_BEGIN", (tmp / "prompt.md").read_text(encoding="utf-8"))
        cr.cleanup(tmp)
        self.assertFalse(tmp.exists())

    def test_prepare_refuses_a_temp_folder_inside_the_repository(self):
        with self.assertRaises(cr.CrossReviewError):
            cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.repo)

    def test_prepare_refuses_a_large_diff_and_secret_like_text(self):
        with self.assertRaises(cr.CrossReviewError) as big:
            cr.prepare(self.repo, "develop", "7", "r", "claude", max_diff_bytes=10, base_directory=self.base)
        self.assertIn("above the limit", str(big.exception))
        (self.repo / "c.txt").write_text('password = "hunter2hunter2"\n', encoding="utf-8")
        self.run_git(self.repo, "add", "-A")
        self.run_git(self.repo, "commit", "-m", "secret")
        with self.assertRaises(cr.CrossReviewError) as secret:
            cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        self.assertIn("credential assignment", str(secret.exception))
        self.assertNotIn("hunter2", str(secret.exception))
        self.assertEqual(list(self.base.iterdir()), [])
        cr.prepare(self.repo, "develop", "7", "r", "claude", allow_secret_like_text=True, base_directory=self.base)

    def commit_files(self, files, message="more"):
        for name, text in files.items():
            path = self.repo / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text(text, encoding="utf-8")
        self.run_git(self.repo, "add", "-A")
        self.run_git(self.repo, "commit", "-m", message)

    def test_prepare_leaves_generated_and_bundled_files_out_of_the_diff(self):
        self.commit_files({"assets/ui/lib.js": "bundled\n", "languages/x.pot": "msgid\n", "application/a.php": "<?php\n"})
        result = cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        tmp = Path(result["Tmp"])
        diff = (tmp / "pr.diff").read_text(encoding="utf-8")
        self.assertIn("application/a.php", diff)
        self.assertNotIn("bundled", diff)
        self.assertNotIn("msgid", diff)
        self.assertEqual(result["Skipped"], ["assets/ui/lib.js", "languages/x.pot"])
        context = (tmp / "context.md").read_text(encoding="utf-8")
        self.assertIn("- assets/ui/lib.js", context)
        self.assertIn("generated or bundled files, not reviewed", context)

    def test_the_plugins_own_assets_stay_in_the_diff(self):
        self.commit_files({"assets/scripts/et_calendar.js": "own code" + chr(10), "assets/styles/publicintegration.css": ".et_x{}" + chr(10)})
        result = cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        diff = (Path(result["Tmp"]) / "pr.diff").read_text(encoding="utf-8")
        self.assertIn("own code", diff)
        self.assertIn(".et_x", diff)
        self.assertEqual(result["Skipped"], [])

    def test_repo_state_changes_when_the_tree_or_head_changes(self):
        before = cr.repo_state(self.repo)
        (self.repo / "dirty.txt").write_text("x", encoding="utf-8")
        self.assertNotEqual(cr.repo_state(self.repo), before)

    def test_prepare_with_only_noise_still_builds_an_input(self):
        self.run_git(self.repo, "reset", "--hard", "develop")
        self.commit_files({"languages/y.pot": "msgid\n"})
        result = cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        self.assertIn("only generated or bundled files changed", (Path(result["Tmp"]) / "pr.diff").read_text(encoding="utf-8"))

    def test_prepare_copies_the_matching_instruction_files_and_the_security_agent(self):
        self.commit_files({
            ".agents/instructions/php.instructions.md": "---\napplyTo: '**/*.php'\n---\nphp rules\n",
            ".agents/instructions/css.instructions.md": "---\napplyTo: '**/*.css'\n---\ncss rules\n",
            ".agents/agents/code-review.agent.md": "criteria\n",
            ".agents/agents/wp-security-review.agent.md": "security criteria\n",
            "CLAUDE.md": "project\n",
        }, "rules")
        self.run_git(self.repo, "push", "origin", "HEAD:develop")
        self.run_git(self.repo, "fetch", "origin")
        self.commit_files({"application/models/class.evangtermine_query.php": "<?php\n"})
        result = cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        files = result["Files"]
        self.assertIn(".agents__instructions__php.instructions.md.txt", files)
        self.assertNotIn(".agents__instructions__css.instructions.md.txt", files)
        self.assertIn(".agents__agents__code-review.agent.md.txt", files)
        self.assertIn(".agents__agents__wp-security-review.agent.md.txt", files)
        self.assertIn("CLAUDE.md.txt", files)

    def test_prepare_skips_the_security_agent_for_other_files(self):
        self.commit_files({".agents/agents/wp-security-review.agent.md": "security criteria\n", "readme.txt": "x\n"})
        result = cr.prepare(self.repo, "develop", "7", "r", "claude", base_directory=self.base)
        self.assertEqual([f for f in result["Files"] if "security" in f], [])

    def test_cleanup_refuses_other_folders(self):
        with self.assertRaises(cr.CrossReviewError):
            cr.cleanup(self.repo)
        self.assertTrue(self.repo.exists())


class GlobTest(unittest.TestCase):
    def test_star_stays_in_a_folder_and_double_star_crosses(self):
        self.assertTrue(cr.glob_regex("**/*.php").match("a/b/c.php"))
        self.assertTrue(cr.glob_regex("**/*.php").match("c.php"))
        self.assertFalse(cr.glob_regex("*.php").match("a/c.php"))
        self.assertTrue(cr.glob_regex("application/controllers/**").match("application/controllers/class.x.php"))
        self.assertFalse(cr.glob_regex("readme.txt").match("readmeXtxt"))

    def test_apply_to_reads_the_frontmatter_line(self):
        self.assertEqual(cr.apply_to("---\napplyTo: 'a.txt, **/*.php'\n---\n"), ["a.txt", "**/*.php"])
        self.assertEqual(cr.apply_to("no frontmatter"), [])

    def test_the_real_instruction_files_are_readable(self):
        folder = Path(__file__).resolve().parents[3] / "instructions"
        for path in folder.glob("*.instructions.md"):
            self.assertTrue(cr.apply_to(path.read_text(encoding="utf-8")), path.name)


class CommandLineTest(unittest.TestCase):
    def test_missing_parameter_is_reported_as_json(self):
        with contextlib.redirect_stdout(io.StringIO()) as out:
            self.assertEqual(cr.main(["preflight"]), 1)
        self.assertFalse(json.loads(out.getvalue())["Ok"])

    def test_settings_action_succeeds(self):
        with tempfile.TemporaryDirectory() as d:
            with contextlib.redirect_stdout(io.StringIO()) as out:
                self.assertEqual(cr.main(["settings", "--settings-path", str(Path(d) / "none.json")]), 0)
            self.assertEqual(json.loads(out.getvalue())["mode"], "off")


if __name__ == "__main__":
    unittest.main()
