#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Synchroniseren tussen de lokale site en de Railway-kopie, zonder elkaars werk te overschrijven.

    python3 dtcpush.py status            wat is er waar veranderd
    python3 dtcpush.py push [id ...]     lokale wijzigingen online zetten (slaat conflicten over)
    python3 dtcpush.py pull [id ...]     online wijzigingen terughalen naar lokaal
    python3 dtcpush.py ijk               de huidige toestand als ijkpunt vastleggen

Opties: --forceer (bij push: ook over online wijzigingen heen schrijven)
"""
import base64, json, os, subprocess, sys, tempfile, shutil

LOKAAL   = "/Users/jesse/Desktop/dtc-local"
RAILWAY  = "/Users/jesse/Desktop/dtc-wpcli"
TOOLS    = "/Users/jesse/Desktop/dtc-tools"
IJKPUNT  = os.path.join(TOOLS, "ijkpunt.json")
DOMEIN   = {"lokaal": "http://localhost:8080",
            "online": "https://dtc-web-production.up.railway.app"}

def wp(map_, *argumenten):
    r = subprocess.run(["wp"] + list(argumenten), cwd=map_, capture_output=True, text=True)
    return r.stdout, r.stderr, r.returncode

def base64_uit(uitvoer):
    """de laatste niet-lege regel is onze base64 (wp-cli print soms waarschuwingen)"""
    regels = [r.strip() for r in uitvoer.splitlines() if r.strip()]
    if not regels:
        raise SystemExit("geen uitvoer van wp-cli")
    return regels[-1]

def inventaris(kant):
    map_ = LOKAAL if kant == "lokaal" else RAILWAY
    uit, fout, code = wp(map_, "eval-file", os.path.join(TOOLS, "inventaris.php"), DOMEIN[kant])
    if code != 0:
        raise SystemExit("inventaris %s mislukt:\n%s" % (kant, fout[-800:]))
    data = json.loads(base64.b64decode(base64_uit(uit)))
    return {x["id"]: x for x in data["items"]}

def lees_ijkpunt():
    if not os.path.exists(IJKPUNT):
        return {}
    with open(IJKPUNT) as f:
        return {int(k): v for k, v in json.load(f).items()}

def schrijf_ijkpunt(ijk):
    with open(IJKPUNT, "w") as f:
        json.dump({str(k): v for k, v in ijk.items()}, f, indent=1, ensure_ascii=False)

def vergelijk():
    lok, onl, ijk = inventaris("lokaal"), inventaris("online"), lees_ijkpunt()
    rijen = []
    for pid in sorted(set(lok) | set(onl)):
        l, o = lok.get(pid), onl.get(pid)
        basis = ijk.get(pid, {}).get("md5")
        if l and not o:
            staat = "alleen lokaal"
        elif o and not l:
            staat = "alleen online"
        elif l["md5"] == o["md5"]:
            staat = "gelijk"
        elif basis is None:
            staat = "verschillend (geen ijkpunt)"
        elif l["md5"] != basis and o["md5"] != basis:
            staat = "CONFLICT"
        elif l["md5"] != basis:
            staat = "lokaal gewijzigd"
        else:
            staat = "online gewijzigd"
        rijen.append((pid, staat, l, o))
    return rijen, lok, onl, ijk

def naam(l, o):
    x = l or o
    return (x.get("slug") or x.get("titel") or "")[:26]

def toon_status(rijen):
    interessant = [r for r in rijen if r[1] != "gelijk"]
    print("%d pagina's vergeleken, %d met verschil\n" % (len(rijen), len(interessant)))
    if not interessant:
        print("  lokaal en online zijn gelijk")
        return
    for pid, staat, l, o in interessant:
        extra = ""
        if o and o.get("via_plugin"):
            extra = "  <- online bewerkt via de plugin door %s" % (o.get("door") or "onbekend")
        print("  %-9s %-28s %-26s %s%s" % (pid, staat, naam(l, o), (l or o)["type"], extra))
    if any(r[1] == "CONFLICT" for r in interessant):
        print("\n  CONFLICT = zowel lokaal als online gewijzigd. Bekijk dat eerst met de hand;")
        print("  push slaat die pagina's over (tenzij je --forceer meegeeft).")

def overzetten(van, naar, ids):
    """kopieert de inhoud van de ene kant naar de andere"""
    map_van  = LOKAAL if van == "lokaal" else RAILWAY
    map_naar = LOKAAL if naar == "lokaal" else RAILWAY
    tijdelijk = tempfile.mkdtemp(prefix="dtcpush-")
    try:
        for pid in ids:
            uit, fout, code = wp(map_van, "eval-file", os.path.join(TOOLS, "lees.php"), str(pid), DOMEIN[van])
            if code != 0:
                raise SystemExit("lezen van %s mislukt:\n%s" % (pid, fout[-500:]))
            b64 = base64_uit(uit)
            if b64 == "GEENPOST":
                print("  %s bestaat niet aan de kant '%s'" % (pid, van)); continue
            inhoud = base64.b64decode(b64)
            if b"DTC_SITE" not in inhoud and b"[" not in inhoud:
                raise SystemExit("inhoud van %s ziet er niet goed uit" % pid)
            with open(os.path.join(tijdelijk, "%d.b64" % pid), "wb") as f:
                f.write(b64.encode())
        uit, fout, code = wp(map_naar, "eval-file", os.path.join(TOOLS, "schrijf.php"), tijdelijk, DOMEIN[naar])
        print("\n".join(r for r in uit.splitlines() if "Deprecated" not in r and r.strip()))
        if code != 0:
            raise SystemExit("schrijven mislukt:\n%s" % fout[-800:])
        # de wijziging is overgenomen, dus de markering aan de bronkant mag weg
        wp(map_van, "eval-file", os.path.join(TOOLS, "ontmarkeer.php"), *[str(i) for i in ids])
    finally:
        shutil.rmtree(tijdelijk, ignore_errors=True)

def hoofd():
    opdracht = sys.argv[1] if len(sys.argv) > 1 else "status"
    forceer = "--forceer" in sys.argv
    gevraagd = [int(a) for a in sys.argv[2:] if a.isdigit()]

    rijen, lok, onl, ijk = vergelijk()

    if opdracht == "status":
        toon_status(rijen)
        return

    if opdracht == "ijk":
        nieuw = {pid: {"md5": l["md5"]} for pid, staat, l, o in rijen if l and o and l["md5"] == o["md5"]}
        anders = [pid for pid, staat, l, o in rijen if staat != "gelijk"]
        schrijf_ijkpunt(nieuw)
        print("ijkpunt vastgelegd voor %d pagina's" % len(nieuw))
        if anders:
            print("niet vastgelegd (nog verschillend): %s" % ", ".join(str(a) for a in anders))
        return

    if opdracht not in ("push", "pull"):
        print(__doc__); return

    if opdracht == "push":
        kandidaten = [r for r in rijen if r[1] in ("lokaal gewijzigd", "verschillend (geen ijkpunt)")]
        conflicten = [r for r in rijen if r[1] in ("CONFLICT", "online gewijzigd")]
        if forceer:
            kandidaten += conflicten; conflicten = []
    else:
        kandidaten = [r for r in rijen if r[1] in ("online gewijzigd", "verschillend (geen ijkpunt)", "CONFLICT")]
        conflicten = []

    if gevraagd:
        kandidaten = [r for r in kandidaten if r[0] in gevraagd]

    if conflicten:
        print("OVERGESLAGEN (online aangepast, niet overschreven):")
        for pid, staat, l, o in conflicten:
            wie = (o.get("door") or "onbekend") if o else "onbekend"
            print("  %-9s %-26s %s%s" % (pid, naam(l, o), staat, "  door " + wie if o and o.get("via_plugin") else ""))
        print("  haal die eerst op met:  python3 dtcpush.py pull %s\n" % " ".join(str(c[0]) for c in conflicten))

    if not kandidaten:
        print("niets te %sen" % opdracht)
        return

    print("%s: %d pagina's\n" % (opdracht, len(kandidaten)))
    ids = [r[0] for r in kandidaten]
    overzetten("lokaal" if opdracht == "push" else "online",
               "online" if opdracht == "push" else "lokaal", ids)

    # ijkpunt bijwerken voor alles wat nu gelijk is
    rijen2, lok2, onl2, _ = vergelijk()
    nieuw = dict(ijk)
    for pid, staat, l, o in rijen2:
        if l and o and l["md5"] == o["md5"]:
            nieuw[pid] = {"md5": l["md5"]}
    schrijf_ijkpunt(nieuw)
    nog = [r for r in rijen2 if r[1] != "gelijk"]
    print("\nna afloop: %d van de %d pagina's gelijk" % (len(rijen2) - len(nog), len(rijen2)))
    for pid, staat, l, o in nog:
        print("   nog verschillend: %s %s (%s)" % (pid, naam(l, o), staat))

if __name__ == "__main__":
    hoofd()
