"""Builds the WordPress.org directory artwork in .wordpress-org/ from the source images.

Sources (AI-generated illustrations, see README.md) are in .github/wporg-assets/source/:
  icon-source.jpg    1024x1024, purple tile on a light background
  banner-source.jpg  1792x592, illustration on the left, empty paper on the right for the title

Run from the repository root:  python .github/wporg-assets/make_assets.py
Needs Python 3 and Pillow. The title uses Arial Bold / Arial if found, else a fallback font.
"""
import os
from PIL import Image, ImageDraw, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, '..', '..', '.wordpress-org')
PURPLE = (58, 24, 100)
GOLD = (224, 170, 40)
LANCZOS = getattr(Image, 'Resampling', Image).LANCZOS  # Image.Resampling needs Pillow >= 9.1
ICON_SOURCE_SIZE = (1024, 1024)
BANNER_SOURCE_SIZE = (1792, 592)


def font(size, bold):
    names = ['arialbd.ttf', 'Arial Bold.ttf', 'LiberationSans-Bold.ttf', 'DejaVuSans-Bold.ttf'] if bold else \
            ['arial.ttf', 'Arial.ttf', 'LiberationSans-Regular.ttf', 'DejaVuSans.ttf']
    for n in names:
        try:
            return ImageFont.truetype(n, size)
        except OSError:
            continue
    try:
        return ImageFont.load_default(size=size)  # scalable, Pillow >= 10.1
    except TypeError:
        # Older Pillow: tiny fixed-size bitmap font. Install Arial, Liberation Sans or DejaVu Sans.
        return ImageFont.load_default()


def make_icon():
    src = Image.open(os.path.join(HERE, 'source', 'icon-source.jpg')).convert('RGB')
    if src.size != ICON_SOURCE_SIZE:
        raise SystemExit('icon-source.jpg must be %dx%d (the crop box is tuned for it), got %dx%d' % (ICON_SOURCE_SIZE + src.size))
    # Crop the purple tile (the source has a light background and a soft shadow around it).
    box = (134, 134, 890, 890)
    tile = src.crop(box)
    n = tile.size[0]
    mask = Image.new('L', (n * 4, n * 4), 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, n * 4 - 1, n * 4 - 1), radius=int(n * 4 * 0.215), fill=255)
    mask = mask.resize((n, n), LANCZOS)
    tile = tile.convert('RGBA')
    tile.putalpha(mask)
    for size in (128, 256):
        tile.resize((size, size), LANCZOS).save(os.path.join(OUT, 'icon-%dx%d.png' % (size, size)), optimize=True)


def make_banner(w, h):
    src = Image.open(os.path.join(HERE, 'source', 'banner-source.jpg')).convert('RGB')
    if src.size != BANNER_SOURCE_SIZE:
        raise SystemExit('banner-source.jpg must be %dx%d (the title position is tuned for it), got %dx%d' % (BANNER_SOURCE_SIZE + src.size))
    sw, sh = src.size
    ch = round(sw * h / w)                      # crop to the banner ratio, keep the full width
    top = (sh - ch) // 2
    img = src.crop((0, top, sw, top + ch)).resize((w, h), LANCZOS)
    k = w / 1544.0
    d = ImageDraw.Draw(img)
    x = int(1075 * k)
    d.text((x, int(140 * k)), 'Evangelische', font=font(int(62 * k), True), fill=PURPLE)
    d.text((x, int(208 * k)), 'Termine', font=font(int(62 * k), True), fill=PURPLE)
    d.rectangle((x + 4 * k, 295 * k, x + 70 * k, 300 * k), fill=GOLD)
    d.text((x + 2 * k, 318 * k), 'Veranstaltungen einfach', font=font(int(24 * k), False), fill=PURPLE)
    d.text((x + 2 * k, 348 * k), 'auf Ihrer Website einbinden', font=font(int(24 * k), False), fill=PURPLE)
    return img


if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    make_icon()
    make_banner(772, 250).save(os.path.join(OUT, 'banner-772x250.png'), optimize=True)
    make_banner(1544, 500).save(os.path.join(OUT, 'banner-1544x500.png'), optimize=True)
