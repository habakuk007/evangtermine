# WordPress.org directory artwork

`make_assets.py` draws the files in `.wordpress-org/`: `icon-128x128.png`, `icon-256x256.png`,
`banner-772x250.png` and `banner-1544x500.png`.

- **Source:** original artwork by the plugin maintainers, drawn from basic shapes (calendar page,
  binder rings, a plain cross, dots). No logo of Evangelische Termine, Vernetzte Kirche, the EKD or a
  Landeskirche and no third-party image is used.
- **License:** GPL-2.0-or-later, same as the plugin.
- **Banner text:** set in Arial (system font, rendered into the PNG; the font is not distributed).
- Regenerate: `python .github/wporg-assets/make_assets.py` (needs Pillow).

`.wordpress-org/blueprints/blueprint.json` is the Live Preview blueprint (WordPress Playground). The
10up deploy action copies the whole `.wordpress-org/` folder, including `blueprints/`, to SVN `assets/`;
`.distignore` keeps it out of the release ZIP.
