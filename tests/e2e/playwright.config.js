// Playwright configuration for the end-to-end tests. See tests/e2e/README.md.
const fs = require( 'fs' );
const path = require( 'path' );
const { defineConfig, devices } = require( '@playwright/test' );

// Port 9400 (the default of Playground) is inside a reserved range on some Windows machines.
const multisiteRun = '1' === process.env.ET_E2E_MULTISITE;
const port = process.env.ET_E2E_PORT || ( multisiteRun ? '80' : '8881' );
const php = process.env.ET_E2E_PHP || '8.3';
const wp = process.env.ET_E2E_WP || 'latest';
const baseURL = '80' === port ? 'http://127.0.0.1' : `http://127.0.0.1:${ port }`;

// Folder for files that the tests create (not in Git).
const tmpDir = path.join( __dirname, '.tmp' );

// Playground CLI 3.1 ignores --php and --wp when the blueprint is a file: only "preferredVersions" in the
// blueprint counts. So the tests start Playground with a copy of blueprint.json that has the wanted versions.
const blueprint = JSON.parse( fs.readFileSync( path.join( __dirname, 'blueprint.json' ), 'utf8' ) );
blueprint.preferredVersions = { php, wp };
// Multisite run (npm run test:e2e:multisite): the same site, converted to a network by Playground.
const multisite = '1' === process.env.ET_E2E_MULTISITE;
if ( multisite ) {
	blueprint.steps.push( { step: 'enableMultisite' } );
}
fs.mkdirSync( tmpDir, { recursive: true } );
fs.writeFileSync( path.join( tmpDir, 'blueprint.json' ), JSON.stringify( blueprint, null, '	' ) );

module.exports = defineConfig( {
	testDir: path.join( __dirname, 'specs' ),
	globalSetup: path.join( __dirname, 'global-setup.js' ),
	outputDir: path.join( __dirname, 'test-results' ),
	// All tests use the same WordPress site and change its settings, so they must run one after another.
	workers: 1,
	fullyParallel: false,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	// A request to Playground takes 2 to 3 seconds on a busy machine, some tests need several page loads.
	timeout: 180000,
	expect: { timeout: 20000 },
	reporter: [
		[ 'list' ],
		[ 'html', { outputFolder: path.join( __dirname, 'playwright-report' ), open: 'never' } ],
	],
	use: {
		baseURL,
		// Login cookies of the administrator, written by global-setup.js.
		storageState: path.join( tmpDir, 'state.json' ),
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			// Default: the calendar host is replaced by the fixtures in tests/e2e/fixtures.
			name: 'mock',
			testIgnore: /(live|multisite)\.spec\.js$/,
			use: { ...devices[ 'Desktop Chrome' ] },
		},
		{
			// Optional: uninstall in a multisite (npm run test:e2e:multisite starts the site as a network).
			name: 'multisite',
			testMatch: /multisite\.spec\.js$/,
			use: { ...devices[ 'Desktop Chrome' ] },
		},
		{
			// Optional: a few checks against the real www.evangelische-termine.de (npm run test:e2e:live).
			name: 'live',
			testMatch: /live\.spec\.js$/,
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
	webServer: {
		command: `npx wp-playground-cli server --auto-mount --login --port=${ port }${ multisiteRun ? " --site-url=http://127.0.0.1" : "" } --php=${ php } --wp=${ wp } --blueprint=tests/e2e/.tmp/blueprint.json`,
		cwd: path.join( __dirname, '..', '..' ),
		// A static file: pages of WordPress redirect to the automatic login, which the check of Playwright cannot follow.
		url: `${ baseURL }/wp-includes/images/blank.gif`,
		reuseExistingServer: ! process.env.CI,
		// The first start downloads WordPress and can take a few minutes.
		timeout: 300000,
		stdout: 'pipe',
		stderr: 'pipe',
	},
} );
