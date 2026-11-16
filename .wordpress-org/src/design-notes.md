# Quiet Cartography

Design notes for the WordPress.org banner and icon.

**Idea.** A booking is a line drawn between a place and a day. The artwork treats that as a small map: a
shore, a destination, a date, and a dotted route that joins them. Nothing is photographed; everything is
drawn with one stroke weight, the way a careful hand marks a chart.

**Colour.** One gradient carries the whole surface, from the plugin's teal (`#0d9488`) through `#0284c7`
to deep blue (`#0369a1`): shallow water to open sea. White is the only ink. A single light teal
(`#99f6e4`) is reserved for what the eye should find last: the pin's centre, the chosen day, the route.

**Space.** Type sits left, the drawing right, and the middle is left empty on purpose. Faint contour
lines and concentric rings run behind both, low enough in contrast to be felt before they are seen.
All text and key shapes stay inside the central 1200 x 350 px of the 1544 x 500 canvas.

**Type.** Anek Bangla (the font the plugin ships, SIL OFL): the name in bold, the tagline in regular
weight, nothing else. No claims, no ratings, no third-party marks.

**Icon.** The same palm and pin, reduced until they still read at 64 px, on the same gradient in a
rounded square. No text.

## Files

| File | Size |
| --- | --- |
| `banner-1544x500.png` | 1544 x 500 |
| `banner-772x250.png` | 772 x 250 |
| `icon-256x256.png` | 256 x 256 |
| `icon-128x128.png` | 128 x 128 |
| `icon.svg` | vector |

Sources: `src/banner.html` (SVG inside an HTML page so the bundled font loads), `icon.svg`.
The PNGs are screenshots of those pages taken with headless Chrome at a device scale factor of 1.
These files go in the SVN `assets/` folder, not in `trunk/`.
