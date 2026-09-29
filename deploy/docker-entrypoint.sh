#!/bin/sh
set -e

DOCROOT=/var/www/html
UPLOADS="$DOCROOT/wp-content/uploads"

# Seed the uploads volume on first boot (the volume mount shadows the image dir).
mkdir -p "$UPLOADS"
if [ -z "$(ls -A "$UPLOADS" 2>/dev/null)" ] && [ -d /usr/src/uploads-seed ]; then
	echo "[dtc] uploads volume is empty - seeding from image..."
	cp -a /usr/src/uploads-seed/. "$UPLOADS"/
	echo "[dtc] seeding done ($(du -sh "$UPLOADS" | cut -f1))"
fi

# Divi writes its compiled CSS here; keep it writable.
mkdir -p "$DOCROOT/wp-content/et-cache" "$DOCROOT/wp-content/upgrade"
chown -R www-data:www-data "$UPLOADS" "$DOCROOT/wp-content/et-cache" "$DOCROOT/wp-content/upgrade" 2>/dev/null || true

# mod_php needs the prefork MPM; an apache2 upgrade can silently re-enable mpm_event.
echo "[dtc] mpm modules enabled: $(ls /etc/apache2/mods-enabled/ | grep -c '^mpm') -> $(ls /etc/apache2/mods-enabled/ | grep '^mpm' | tr '\n' ' ')"
if [ "$(ls /etc/apache2/mods-enabled/ | grep -c '^mpm_.*\.load$')" -gt 1 ]; then
	echo "[dtc] more than one MPM enabled - forcing prefork"
	rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
	ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
	ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
	echo "[dtc] now enabled: $(ls /etc/apache2/mods-enabled/ | grep '^mpm' | tr '\n' ' ')"
fi

# Railway assigns the public port via $PORT.
PORT="${PORT:-80}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -ri "s!<VirtualHost \*:[0-9]+>!<VirtualHost *:${PORT}>!" /etc/apache2/sites-available/000-default.conf
echo "[dtc] apache listening on ${PORT}"

exec "$@"
