# dtc website

De WordPress-site van dtc /always on. Deze repo bevat de complete installatie:
WordPress-core, het Divi-thema, de plugins en — het belangrijkste — de eigen
code in `wp-content/mu-plugins/`.

## Wat waar staat

| Map | Wat het is |
| --- | --- |
| `wp-content/mu-plugins/` | **Al het eigen werk.** 26 must-use plugins die de opmaak, het menu, de video's, de scroll-effecten, de SEO-laag, de inlogpagina en de eigen adminschermen regelen. Ze laden altijd, in alfabetische volgorde, zonder dat iemand ze aan of uit kan zetten. |
| `wp-content/themes/Divi/` | Het thema. We passen Divi zelf niet aan; alle afwijkingen zitten in de mu-plugins. |
| `deploy/` | Alles om de site als Docker-image op Railway te draaien. |
| `tools/` | `dtcpush.py` en hulpbestanden om pagina-inhoud tussen lokaal en Railway te synchroniseren. |

### Bewust niet in de repo

- `wp-content/uploads/` (528 MB) — de media staan op een Railway-volume.
- `wp-content/ai1wm-backups/` (1,6 GB) — back-ups van de migratieplugin.
- `wp-config.php` — bevat het databasewachtwoord en de WordPress-sleutels.
  Gebruik `wp-config.example.php` als startpunt.
- caches (`et-cache`, `wp-fastest-cache`) — die bouwt WordPress zelf opnieuw op.

## Lokaal draaien

```bash
cp wp-config.example.php wp-config.php     # database invullen
php -S localhost:8080                      # of ./start-dtc.command
```

De site draait op http://localhost:8080. Zonder database-dump zie je een lege
installatie; vraag een export aan iemand met toegang.

## Waar de inhoud staat

De teksten, pagina's en instellingen staan in de **database**, niet in deze repo.
Divi bewaart een pagina als shortcodes in `post_content`. Wie inhoud aanpast doet
dat dus in WordPress zelf, niet in git.

Lokaal en online synchroniseren gaat met `tools/dtcpush.py`:

```bash
python3 tools/dtcpush.py                 # vergelijk lokaal met Railway
python3 tools/dtcpush.py push 284222     # zet één pagina online
```

`dtcpush.py` slaat pagina's over die online gewijzigd zijn, zodat werk in de
online Divi-builder niet overschreven wordt. Pull die eerst, pas je wijziging
opnieuw toe, en push daarna.

## Deployen naar Railway

```bash
rsync -a --delete wp-content/mu-plugins/ ~/Desktop/dtc-railway/app/wp-content/mu-plugins/
cd ~/Desktop/dtc-railway && railway up --service dtc-web --ci
```

`deploy/wp-config.example.php` laat zien welke omgevingsvariabelen de
Railway-service verwacht. De echte `wp-config.php` staat niet in de repo.

## Werkafspraken

1. **Eerst lokaal, dan Railway.** Laat een wijziging eerst op localhost zien.
2. **Opmaak hoort in een mu-plugin**, niet in Divi's "Custom CSS"-veld en niet in
   het thema. Zo blijft alles op één plek en overleeft het een thema-update.
3. **Leeg de Divi-cache** (`wp-content/et-cache/<pagina-id>`) na een wijziging aan
   pagina-inhoud, anders blijf je de oude CSS zien.
4. **Nooit sleutels of wachtwoorden committen.**
