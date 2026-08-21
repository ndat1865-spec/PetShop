from __future__ import annotations

from pathlib import Path
from PIL import Image, ImageChops, ImageStat


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "public/media/auth/cute-dog-eat.gif"
WEBP_OUT = ROOT / "public/media/auth/cute-dog-eat-hq.webp"
GIF_OUT = ROOT / "public/media/auth/cute-dog-eat-hq.gif"
SCALE = 4


def read_frames(path: Path):
    image = Image.open(path)
    frames: list[Image.Image] = []
    durations: list[int] = []
    disposals: list[int] = []
    loop = int(image.info.get("loop", 0))

    for index in range(image.n_frames):
        image.seek(index)
        durations.append(int(image.info.get("duration", 0)))
        disposals.append(int(getattr(image, "disposal_method", 2)))
        rgba = image.convert("RGBA")
        frames.append(
            rgba.resize(
                (rgba.width * SCALE, rgba.height * SCALE),
                Image.Resampling.LANCZOS,
            )
        )

    return frames, durations, disposals, loop


def save_webp(frames, durations, loop):
    temp = WEBP_OUT.with_suffix(".tmp.webp")
    frames[0].save(
        temp,
        format="WEBP",
        save_all=True,
        append_images=frames[1:],
        duration=durations,
        loop=loop,
        lossless=False,
        quality=92,
        method=4,
        minimize_size=False,
        exact=True,
    )
    temp.replace(WEBP_OUT)


def save_gif(frames, durations, disposals, loop):
    temp = GIF_OUT.with_suffix(".tmp.gif")
    frames[0].save(
        temp,
        format="GIF",
        save_all=True,
        append_images=frames[1:],
        duration=durations,
        loop=loop,
        disposal=disposals,
        transparency=255,
        optimize=False,
    )
    temp.replace(GIF_OUT)


def inspect(path: Path):
    image = Image.open(path)
    durations = []
    disposals = []
    for index in range(image.n_frames):
        image.seek(index)
        durations.append(int(image.info.get("duration", 0)))
        disposals.append(int(getattr(image, "disposal_method", 0)))
    return {
        "path": str(path.relative_to(ROOT)),
        "size": image.size,
        "frames": image.n_frames,
        "loop": int(image.info.get("loop", 0)),
        "durations": durations,
        "disposals": disposals,
        "bytes": path.stat().st_size,
    }


def webp_frame_durations(path: Path):
    data = path.read_bytes()
    durations = []
    offset = 12
    while offset + 8 <= len(data):
        chunk_type = data[offset : offset + 4]
        chunk_size = int.from_bytes(data[offset + 4 : offset + 8], "little")
        payload = data[offset + 8 : offset + 8 + chunk_size]
        if chunk_type == b"ANMF":
            durations.append(int.from_bytes(payload[12:15], "little"))
        offset += 8 + chunk_size + (chunk_size & 1)
    return durations


def main():
    source = inspect(SOURCE)
    frames, durations, disposals, loop = read_frames(SOURCE)
    save_webp(frames, durations, loop)
    save_gif(frames, durations, disposals, loop)

    webp = inspect(WEBP_OUT)
    gif = inspect(GIF_OUT)
    expected_size = (source["size"][0] * SCALE, source["size"][1] * SCALE)

    for result in (webp, gif):
        assert result["size"] == expected_size
        assert result["frames"] == source["frames"]
        assert result["loop"] == source["loop"]
    assert webp_frame_durations(WEBP_OUT) == source["durations"]
    assert gif["durations"] == source["durations"]
    assert gif["disposals"] == source["disposals"]

    webp_image = Image.open(WEBP_OUT)
    webp_image.seek(0)
    difference = ImageChops.difference(frames[0], webp_image.convert("RGBA"))
    mean_difference = ImageStat.Stat(difference).mean
    assert max(mean_difference[:3]) < 2.5, "WebP color drift is too high"
    assert WEBP_OUT.stat().st_size < GIF_OUT.stat().st_size
    print("webp_mean_rgba_difference=" + ",".join(f"{value:.4f}" for value in mean_difference))

    for result in (source, webp, gif):
        print(
            f'{result["path"]}|{result["size"][0]}x{result["size"][1]}|'
            f'{result["frames"]} frames|loop={result["loop"]}|'
            f'{result["bytes"]} bytes'
        )


if __name__ == "__main__":
    main()
