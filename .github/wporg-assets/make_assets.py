"""Draws the WordPress.org directory artwork (icon and banner) into .wordpress-org/.

Original artwork, drawn with Pillow from simple shapes (calendar page, ring binders, a plain
cross, date dots). No third-party logo or image is used. Run from the repository root:

    python .github/wporg-assets/make_assets.py

Needs Python 3 and Pillow (pip install pillow). The banner text uses Arial Bold / Arial if
found (Windows/Linux/macOS font paths), otherwise Pillow's built-in font.
"""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

OUT = os.path.join(os.path.dirname(__file__), '..', '..', '.wordpress-org')
PURPLE = (88, 41, 140)
PURPLE_DARK = (58, 24, 100)
PURPLE_LIGHT = (126, 74, 184)
PAPER = (255, 255, 255)
GOLD = (245, 183, 49)
GREY = (205, 198, 218)


def icon(size):
    """Square icon, drawn at 4x and scaled down for smooth edges."""
    s = size * 4
    img = Image.new('RGBA', (s, s), (0, 0, 0, 0))
    d = ImageDraw.Draw(img)
    u = s / 128.0
    d.rounded_rectangle((0, 0, s - 1, s - 1), radius=22 * u, fill=PURPLE)
    # soft lower-right shade for a little depth
    shade = Image.new('RGBA', (s, s), (0, 0, 0, 0))
    ImageDraw.Draw(shade).polygon([(s, s * 0.45), (s, s), (s * 0.45, s)], fill=PURPLE_DARK + (110,))
    mask = Image.new('L', (s, s), 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, s - 1, s - 1), radius=22 * u, fill=255)
    img.paste(shade, (0, 0), Image.composite(shade.split()[3], Image.new('L', (s, s), 0), mask))
    d = ImageDraw.Draw(img)
    # calendar page
    x0, y0, x1, y1 = 20 * u, 26 * u, 108 * u, 108 * u
    d.rounded_rectangle((x0, y0, x1, y1), radius=9 * u, fill=PAPER)
    d.rounded_rectangle((x0, y0, x1, 50 * u), radius=9 * u, fill=GOLD)
    d.rectangle((x0, 40 * u, x1, 50 * u), fill=GOLD)
    # binder rings
    for cx in (44, 84):
        d.rounded_rectangle(((cx - 5) * u, 17 * u, (cx + 5) * u, 38 * u), radius=5 * u, fill=PURPLE_DARK)
    # cross
    cx, cy = 64 * u, 79 * u
    t, h, w = 6.5 * u, 22 * u, 16 * u
    d.rounded_rectangle((cx - t, cy - h, cx + t, cy + h), radius=2 * u, fill=PURPLE)
    d.rounded_rectangle((cx - w, cy - 13 * u, cx + w, cy - 13 * u + 2 * t), radius=2 * u, fill=PURPLE)
    # date dots left and right of the cross
    for px in (31, 97):
        for py in (62, 80, 98):
            d.ellipse((px * u - 3.2 * u, py * u - 3.2 * u, px * u + 3.2 * u, py * u + 3.2 * u), fill=GREY)
    return img.resize((size, size), Image.LANCZOS)


def font(size, bold):
    names = ['arialbd.ttf', 'Arial Bold.ttf', 'LiberationSans-Bold.ttf', 'DejaVuSans-Bold.ttf'] if bold else \
            ['arial.ttf', 'Arial.ttf', 'LiberationSans-Regular.ttf', 'DejaVuSans.ttf']
    for n in names:
        try:
            return ImageFont.truetype(n, size)
        except OSError:
            continue
    return ImageFont.load_default()


def banner(w, h):
    """Banner, designed for 772x250 and scaled for 1544x500."""
    k = w / 772.0
    img = Image.new('RGB', (w, h), PURPLE)
    px = img.load()
    for x in range(w):
        t = x / (w - 1)
        c = tuple(int(PURPLE_DARK[i] + (PURPLE_LIGHT[i] - PURPLE_DARK[i]) * t) for i in range(3))
        for y in range(h):
            px[x, y] = c
    d = ImageDraw.Draw(img)
    # a few decorative date dots on the right
    for i in range(4):
        for j in range(3):
            cx, cy = (560 + i * 50) * k, (70 + j * 55) * k
            d.ellipse((cx - 9 * k, cy - 9 * k, cx + 9 * k, cy + 9 * k), fill=(150, 100, 205))
    big = icon(int(170 * k))
    img.paste(big, (int(40 * k), int(40 * k)), big)
    d.text((240 * k, 78 * k), 'Evangelische Termine', font=font(int(44 * k), True), fill=PAPER)
    d.text((242 * k, 140 * k), 'Veranstaltungen aus dem Kalender', font=font(int(22 * k), False), fill=(232, 222, 245))
    d.text((242 * k, 170 * k), 'als Shortcode und Widget einbinden', font=font(int(22 * k), False), fill=(232, 222, 245))
    return img


if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    for n in (128, 256):
        icon(n).save(os.path.join(OUT, 'icon-%dx%d.png' % (n, n)), optimize=True)
    banner(772, 250).save(os.path.join(OUT, 'banner-772x250.png'), optimize=True)
    banner(1544, 500).save(os.path.join(OUT, 'banner-1544x500.png'), optimize=True)
