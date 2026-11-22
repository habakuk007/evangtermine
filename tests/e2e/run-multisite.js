// Runs the multisite E2E test: starts the test site as a network (ET_E2E_MULTISITE=1) and runs the project "multisite".
// A small wrapper, so the same command works in bash, PowerShell and CI. See tests/e2e/README.md.
const { spawnSync } = require( 'child_process' );

const args = [ 'playwright', 'test', '--config=tests/e2e/playwright.config.js', '--project=multisite', ...process.argv.slice( 2 ) ];
const result = spawnSync( 'npx', args, {
	stdio: 'inherit',
	shell: true,
	env: { ...process.env, ET_E2E_MULTISITE: '1' },
} );
process.exit( null === result.status ? 1 : result.status );
