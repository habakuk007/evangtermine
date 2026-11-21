# WordPress.org directory artwork

`make_assets.py` builds the files in `.wordpress-org/` (`icon-128x128.png`, `icon-256x256.png`,
`banner-772x250.png`, `banner-1544x500.png`) from the two source images in `source/`. It crops
the icon tile with rounded transparent corners, crops and scales the banner, and sets the title text.

- **Source:** `source/icon-source.jpg` and `source/banner-source.jpg` are illustrations
  created for this plugin (calendar page with a plain cross, people at a gathering). They contain no logo of Evangelische Termine, Vernetzte Kirche, the EKD or a Landeskirche.
- **Copyright and license:** Stefan Wagner, trumpkin.de, created with the help of AI. The icon and banner artwork (`.wordpress-org/icon-*.png`, `.wordpress-org/banner-*.png` and the files in `source/`) is licensed under [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/). It is not part of the plugin ZIP (`.distignore`), so the plugin itself stays GPL-2.0-or-later. `make_assets.py`, the blueprint and the rest of the repository are GPL-2.0-or-later as well.
- **Banner text:** set in Vollkorn (SIL Open Font License; rendered into the PNG, the font file is not distributed). Without Vollkorn installed the script falls back to Arial or another system font.
- Regenerate: `python .github/wporg-assets/make_assets.py` (needs Pillow).

`.wordpress-org/blueprints/blueprint.json` is the Live Preview blueprint (WordPress Playground). The 10up deploy action copies the whole `.wordpress-org/` folder, including `blueprints/`, to SVN `assets/`; `.distignore` keeps it out of the release ZIP.
