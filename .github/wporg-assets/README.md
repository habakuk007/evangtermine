# WordPress.org directory artwork

`make_assets.py` builds the files in `.wordpress-org/` (`icon-128x128.png`, `icon-256x256.png`,
`banner-772x250.png`, `banner-1544x500.png`) from the two source images in `source/`. It crops
the icon tile with rounded transparent corners, crops and scales the banner, and sets the title text.

- **Source:** `source/icon-source.jpg` and `source/banner-source.jpg` are AI-generated illustrations
  created for this plugin (calendar page with a plain cross, people at a gathering). They contain no
  logo of Evangelische Termine, Vernetzte Kirche, the EKD or a Landeskirche.
- **Copyright:** Stefan Wagner, trumpkin.de, created with the help of AI. Published under
  GPL-2.0-or-later, same as the plugin.
- **Tool and terms:** TODO(maintainer): record the image generator and the date, and confirm that its
  terms allow publishing the output under GPL-2.0-or-later.
- **Banner text:** set in Arial (system font, rendered into the PNG; the font is not distributed).
- Regenerate: `python .github/wporg-assets/make_assets.py` (needs Pillow).

`.wordpress-org/blueprints/blueprint.json` is the Live Preview blueprint (WordPress Playground). The
10up deploy action copies the whole `.wordpress-org/` folder, including `blueprints/`, to SVN `assets/`;
`.distignore` keeps it out of the release ZIP.
