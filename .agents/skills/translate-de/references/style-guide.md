# German Style Guide: Summary

Summary of the Style Guide of the German Polyglots team (https://de.wordpress.org/team/handbook/polyglots-team/style-guide/, read 2026-11-21), in our own words. When the guide and this summary differ, the guide wins. The guide applies to everything on translate.wordpress.org; the glossary takes precedence over the guide.

## Basics

- Spelling follows the official rules of the Rat für deutsche Rechtschreibung. For compounds and separate/joined writing use Duden online or LEO.
- Default locale `de_DE` addresses the reader with lower-case `du`. The formal variant `de_DE_formal` uses capitalised `Sie` and is a separate branch.
- Open questions go to the German `#polyglots` Slack channel.

## Brand names and examples

- `WordPress`, `WordPress.org`, `WordPress.com` always written like this, never split.
- Plugin and theme names are treated as brand names and are not translated (users must be able to find them).
- Example names: avoid real persons; use neutral invented names (`Hans Mustermann`).

## Placeholders, HTML

- `%s`, `%d`, `%1$s`, `%(author)s` stay unchanged and may be moved within the sentence.
- Do not add tags or entities yourself. Keep HTML from the source where it makes sense; replace entities for apostrophes or em dashes by the German characters.

## Dates, times, numbers

- Date `TT.MM.JJJJ`, or written out: `13. Juni 2013`. No leading zero: `6. Mai 2015`.
- Time in 24-hour format: `9:00 Uhr`, `21:00 Uhr`. PHP/Moment.js format strings are re-arranged, not translated literally. Letters inside PHP formats that must stay literal are escaped one by one (`j. F Y \u\m G:i:s \U\h\r`). `g:i a` becomes `G:i`.
- English `@` between date and time becomes a comma (`M j, Y @ G:i` becomes `j. M Y, G:i`).
- Convert imperial units to metric. Do not convert currencies.
- Thousands separator `.` from four digits (`1.000.000`). Currency sign after the amount, separated by a non-breaking space.
- One- and two-syllable number words may be written out (`zwei Kopien`), digits are always allowed. Before units, weights, currencies and abbreviations always digits (`1 km`, `20 EUR`).

## Non-breaking space

- Between number and `%` or unit (`40 %`, `25 kg`), within two-part abbreviations (`z. B.`) and before a dash.
- Type the character on the keyboard; do not use an HTML entity.

## Punctuation

- Typographic quotation marks `„…“`. Windows: Alt+0132 / Alt+0147; Mac: Shift+Alt+w / Alt+2; Linux: AltGr+v / AltGr+b.
- The dash (Gedankenstrich) marks a clear pause; do not confuse it with the hyphen. A non-breaking space stands before it.
- `&` is rare in German: write `und`.
- Bullet lists: the bullet does not count for punctuation, keep capitalisation and punctuation consistent. Full sentences: capital letter and full stop. List that continues an introductory sentence: lower case, semicolons, last item with the closing punctuation. Other lists (no sentences): capital letter, no full stop.

## Compounds

- A word group is written with spaces, a compound is joined or hyphenated, without spaces around the hyphen. Avoid very long compounds, rephrase (`die Entwicklung des WordPress-Cores`). In titles keep them short.
- **Hyphen required:** single letters and digits (`T-Shirt`, `20-jährig`), abbreviations (`Kfz-Papiere`, `WP-CLI`), more than two parts (`Vitamin-D-Einnahme`, `Open-Source-Lizenz`), a part that already has a hyphen (`S-Bahn-Strecke`), proper names and product names (`WordPress-Dashboard`, `WordPress.org-Download`), three identical letters in a row in nouns (`Schiff-Fahrt` style, e.g. `Kaffee-Ernte`), two coordinated adjectives (`englisch-deutsch`, `schwarz-weiß`), to avoid misreading (`Druck-Erzeugnis`).
- **Plugin and theme names inside a compound** are coupled with hyphens: `WP-Super-Cache-Aktualisierung` for `WP Super Cache`.
- **Written together, no hyphen:** nominalised two-part infinitives (`das Musikhören`), adjective that qualifies the next one (`hellgrau`), colour mixtures (`grünblau`), three identical letters in adjectives (`schnelllebig`).
- **English loan phrases:** keep the space as in Duden (`Open Source`, `Corporate Identity`, `Hot Spot`); with an added part couple them (`Open-Source-Lizenz`, `Top-Ten-Hit`).
- **Optional hyphen:** two nouns (`Downloadstatus` or `Download-Status`), verb plus particle as noun (`Backup` or `Back-up`; only `Make-up`, `Go-in` with hyphen), linking elements (`Hochgeschwindigkeitsinternetzugang` or with hyphen). Joined writing preferred.

## Style and tone

- Informal, but not colloquial, dialect or flippant. No word-for-word translation: re-phrase meaning in clear German.
- Short, active sentences; split long English sentences.
- Do not carry over jokes and idioms that do not work in German (`Howdy`, `Hello Dolly`).
- No slang contractions: `für das` (not `fürs`), `vor dem` (not `vorm`), `darauf` (not `drauf`), `noch einmal` (not `nochmal`).
- Avoid jargon and buzzwords, except glossary terms (`Feed`, `Theme`, `Trackback`) and common technical terms.
- Do not attribute human traits to software or hardware: no feelings (`ablehnen`, `wollen`), behaviour (`durchsetzen`, `versuchen`), or intelligence (`wissen`, `erkennen`). Programs and commands may "do" something.
- Gender: for general texts aim at equal treatment (Duden volume 9); in product and service texts use the generic masculine throughout (`Benutzer`, `Anwender`) because there is no practical alternative. Prefer neutral rephrasing where it is easy: `Du erhältst möglicherweise keine Antwort, da dieser Kontakt den Status „Abwesend“ hat.`

## Imperative, UI, headings

- English imperative stays imperative (`Drücke F1, um die Hilfe aufzurufen`). In UI elements (buttons, menus) use the infinitive (`Benutzer hinzufügen`).
- Headings use nouns rather than imperatives: `Add users` becomes `Hinzufügen von Benutzern`. Gerund or infinitive becomes a noun without article.
- `About …` becomes `Informationen zu …`; `How to …` / `To …` becomes `So …` with du-form (`So fügst du eine Rolle hinzu`).
- Titles: short, not much longer than the source, no parentheses or inserted clauses, no final full stop. Links to untranslated English pages: translated title plus `(engl.)`.

## Abbreviations and acronyms

- Use only common abbreviations: `z. B.` (e.g.), `d. h.` (i.e.), `bzw.`. A non-breaking space stands between the parts.
- Translated acronym that is not obvious: write it out at first use, acronym in brackets, afterwards the acronym alone. Untranslated acronym (technical texts): acronym first, the English expansion in brackets, a German explanation if needed.

## Not covered by the guide

Capitalisation in titles (the examples use sentence case), ellipsis characters, quotation marks inside lists. Ask in `#polyglots` or follow the existing translation.
