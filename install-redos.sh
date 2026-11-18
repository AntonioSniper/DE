#!/usr/bin/env bash
# Run with bash as the regular desktop user. Only the XAMPP commands need sudo.
set -eu
cd -- "$(dirname -- "$0")"
xampp=/opt/lampp

if [[ ! -x "$xampp/lampp" || ! -x "$xampp/bin/php" || ! -d "$xampp/htdocs" ]]; then
  echo 'Не найден XAMPP в /opt/lampp.' >&2
  exit 1
fi
if ! "$xampp/bin/php" -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("pdo_mysql") && extension_loaded("mbstring") ? 0 : 1);'; then
  echo 'Для сайтов нужны PHP 8.2 или новее, pdo_mysql и mbstring в XAMPP.' >&2
  exit 1
fi

for file in 'первая часть/korochki_db.sql' 'первая часть/media/slides/slide-1.webp' \
            'часть вторая/приложение/database/college_events.sql' 'часть вторая/приложение/login.php'; do
  if [[ ! -f "$file" ]]; then
    echo "Отсутствует файл проекта: $file" >&2
    exit 1
  fi
done

copy_site() {
  local source=$1 destination=$2 item
  sudo mkdir -p -- "$destination"
  for item in "$source"/* "$source/.htaccess"; do
    if [[ "$item" == "$source/config.php" && -f "$destination/config.php" ]]; then
      continue
    fi
    sudo cp -R -- "$item" "$destination/"
  done
}

sudo "$xampp/lampp" start
copy_site 'первая часть' "$xampp/htdocs/korochki"
copy_site 'часть вторая/приложение' "$xampp/htdocs/events"
printf '%s\n' 'Код обоих сайтов скопирован.' 'Проверьте базы в http://localhost/phpmyadmin/.' \
  'SQL для первой установки:' "$xampp/htdocs/korochki/korochki_db.sql" \
  "$xampp/htdocs/events/database/college_events.sql" \
  'Сайты: http://localhost/korochki/ и http://localhost/events/'
