"""Fetch the two exam sites from one GitHub revision into a new directory."""
import hashlib
import json
from pathlib import Path, PurePosixPath
import re
import subprocess
import sys
from tempfile import TemporaryDirectory
from urllib.parse import quote

API = "https://api.github.com/repos/AntonioSniper/DE"
RAW = "https://raw.githubusercontent.com/AntonioSniper/DE"
SITES = ("первая часть/", "часть вторая/приложение/")


def fetch(url):
    return subprocess.check_output([
        "curl", "--fail", "--silent", "--show-error", "--location",
        "--retry", "2", "--connect-timeout", "10", "--max-time", "120", url,
    ])


def download(target):
    if target.exists():
        raise ValueError(f"Папка {target} уже существует. Укажите новое имя.")
    commit = json.loads(fetch(f"{API}/commits/main"))
    revision = commit["sha"]
    if not re.fullmatch(r"[a-f0-9]{40}", revision):
        raise ValueError("Не удалось определить версию main.")
    tree = json.loads(fetch(commit["commit"]["tree"]["url"] + "?recursive=1"))
    if tree.get("truncated"):
        raise ValueError("GitHub вернул неполный список файлов.")
    files = [e for e in tree["tree"] if e["type"] == "blob" and
             (e["path"].startswith(SITES) or e["path"] == "install-redos.sh")]
    required = {"install-redos.sh", "первая часть/korochki_db.sql",
                "часть вторая/приложение/database/college_events.sql"}
    if not required.issubset(e["path"] for e in files):
        raise ValueError("В выбранной версии отсутствуют файлы установки.")

    with TemporaryDirectory(dir=target.parent) as temporary:
        staged = Path(temporary) / "sites"
        for i, entry in enumerate(files, 1):
            name = entry["path"]
            parts = PurePosixPath(name)
            if parts.is_absolute() or ".." in parts.parts or entry["mode"] not in ("100644", "100755"):
                raise ValueError(f"Неожиданный путь: {name}")
            print(f"[{i}/{len(files)}] {name}", flush=True)
            data = fetch(f"{RAW}/{revision}/{quote(name)}")
            header = f"blob {len(data)}\0".encode("ascii")
            if hashlib.sha1(header + data).hexdigest() != entry["sha"]:
                raise ValueError(f"Файл не совпал с версией на GitHub: {name}")
            path = staged.joinpath(*parts.parts)
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(data)
        staged.rename(target)
    print(f"Файлы версии {revision[:7]} сохранены в {target}.")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit("Укажите имя новой папки для двух сайтов.")
    try:
        download(Path(sys.argv[1]).expanduser().absolute())
    except (OSError, ValueError, KeyError, subprocess.CalledProcessError) as error:
        sys.exit(f"Получение файлов прервано: {error}")
