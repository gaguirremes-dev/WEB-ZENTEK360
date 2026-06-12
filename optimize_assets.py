"""Optimiza los assets de Zentek360 para web:
- Quita fondos negros de los robots (flood-fill desde bordes -> conserva negros internos)
- Recorta al contorno, redimensiona y exporta WebP ligero
- Comprime los flyers de servicios/publicidad
"""
import os
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT_BOT = os.path.join(ROOT, "images", "zbot")
OUT_PROD = os.path.join(ROOT, "images", "productos")
os.makedirs(OUT_BOT, exist_ok=True)
os.makedirs(OUT_PROD, exist_ok=True)


def trim_alpha(im, pad=10, athr=10):
    a = np.array(im.split()[-1])
    ys, xs = np.where(a > athr)
    if len(xs) == 0:
        return im
    x0, x1 = max(0, xs.min() - pad), min(im.width, xs.max() + pad)
    y0, y1 = max(0, ys.min() - pad), min(im.height, ys.max() + pad)
    return im.crop((x0, y0, x1 + 1, y1 + 1))


def remove_black_bg(im, thresh=58):
    """im RGBA (ya reducida). Flood-fill el fondo negro desde bordes."""
    rgb = im.convert("RGB")
    w, h = rgb.size
    seed = (255, 0, 255)
    pts = [(0, 0), (w - 1, 0), (0, h - 1), (w - 1, h - 1),
           (w // 2, 0), (w // 2, h - 1), (0, h // 2), (w - 1, h // 2),
           (w // 4, 0), (3 * w // 4, 0)]
    for p in pts:
        try:
            ImageDraw.floodfill(rgb, p, seed, thresh=thresh)
        except Exception:
            pass
    arr = np.array(rgb)
    bg = np.all(arr == np.array(seed), axis=-1)
    alpha = np.where(bg, 0, 255).astype("uint8")
    out = im.convert("RGBA")
    am = Image.fromarray(alpha).filter(ImageFilter.GaussianBlur(0.7))
    out.putalpha(am)
    return out


def process_bot(name, src, crop_bottom=0.0, target_h=1000, remove_bg=True):
    im = Image.open(src).convert("RGBA")
    if crop_bottom > 0:
        im = im.crop((0, 0, im.width, int(im.height * (1 - crop_bottom))))
    scale = target_h / im.height
    im = im.resize((max(1, int(im.width * scale)), target_h), Image.LANCZOS)
    if remove_bg:
        im = remove_black_bg(im)
    im = trim_alpha(im, pad=6)
    dst = os.path.join(OUT_BOT, name + ".webp")
    im.save(dst, "WEBP", quality=86, method=6)
    print(f"  zbot/{name}.webp  {im.size[0]}x{im.size[1]}  {os.path.getsize(dst)//1024}KB")


def process_prod(name, src, target_w=780, q=80):
    im = Image.open(src).convert("RGB")
    scale = target_w / im.width
    im = im.resize((target_w, int(im.height * scale)), Image.LANCZOS)
    dst = os.path.join(OUT_PROD, name + ".webp")
    im.save(dst, "WEBP", quality=q, method=6)
    print(f"  productos/{name}.webp  {im.size[0]}x{im.size[1]}  {os.path.getsize(dst)//1024}KB")


A = os.path.join(ROOT, "assets")
print("== Robots ==")
process_bot("thumbs", os.path.join(A, "AGENTE Z-BOT", "1.png"), crop_bottom=0.07)
process_bot("wink", os.path.join(A, "AGENTE Z-BOT", "2.png"), remove_bg=False)  # ya transparente
process_bot("phone", os.path.join(A, "AGENTE Z-BOT", "3.png"))
process_bot("sit", os.path.join(A, "AGENTE Z-BOT", "4.png"))
process_bot("point", os.path.join(A, "AGENTE Z-BOT", "5.png"))
# 6 = escena con pedestal; conservar fondo, solo comprimir
im6 = Image.open(os.path.join(A, "AGENTE Z-BOT", "6.png")).convert("RGB")
s = 1200 / im6.height
im6 = im6.resize((int(im6.width * s), 1200), Image.LANCZOS)
d6 = os.path.join(OUT_BOT, "scene.webp")
im6.save(d6, "WEBP", quality=84, method=6)
print(f"  zbot/scene.webp  {im6.size[0]}x{im6.size[1]}  {os.path.getsize(d6)//1024}KB")

print("== Productos ==")
process_prod("corporativo", os.path.join(A, "servicios", "CORPORATIVO.jpg"))
process_prod("empresarial", os.path.join(A, "servicios", "EMPRESARIAL.jpg"))
process_prod("emprendedores", os.path.join(A, "servicios", "EMPRENDEDORES.jpg"))
process_prod("mype", os.path.join(A, "servicios", "MYPE.jpg"))
process_prod("publicidad", os.path.join(A, "publicidad", "ZENTEK360v1.0.jpeg"))
print("Listo.")
