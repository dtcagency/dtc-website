#!/bin/bash
# DTC lokale WordPress starten
export PATH="/opt/homebrew/opt/php@8.2/bin:/opt/homebrew/opt/mariadb/bin:$PATH"

# Database starten (indien nog niet actief)
brew services start mariadb >/dev/null 2>&1

cd "$HOME/Desktop/dtc-local" || exit 1

# Oude server op poort 8080 stoppen
lsof -ti:8080 | xargs kill -9 >/dev/null 2>&1

echo "DTC lokaal draait op:  http://localhost:8080"
echo "Beheer (wp-admin):     http://localhost:8080/wp-admin"
echo "  Inloggen: Bridget / dtc   (of admin / admin)"
echo ""
echo "Laat dit venster open. Sluiten = site stopt. (Ctrl+C om te stoppen)"
wp server --host=localhost --port=8080
