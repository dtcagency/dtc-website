#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Voegt de teksten die online zijn aangepast samen met het werk dat lokaal is gedaan.
De online tekst wint (dat is het nieuwste redactiewerk), de lokale opmaak blijft staan.

    python3 samenvoegen.py            laat zien wat er zou veranderen
    python3 samenvoegen.py doen       past het toe op de lokale kopie
"""
import base64, difflib, json, os, subprocess, sys

LOKAAL  = "/Users/jesse/Desktop/dtc-local"
RAILWAY = "/Users/jesse/Desktop/dtc-wpcli"
TOOLS   = "/Users/jesse/Desktop/dtc-tools"
DOMEIN  = {"lokaal": "http://localhost:8080", "online": "https://dtc-web-production.up.railway.app"}

def wp(map_, *args):
    r = subprocess.run(["wp"] + list(args), cwd=map_, capture_output=True, text=True)
    return r.stdout, r.returncode

def laatste(uit):
    regels = [r.strip() for r in uit.splitlines() if r.strip()]
    return regels[-1] if regels else ""

def teksten(kant, pid):
    map_ = LOKAAL if kant == "lokaal" else RAILWAY
    uit, code = wp(map_, "eval-file", os.path.join(TOOLS, "teksten.php"), str(pid))
    b = laatste(uit)
    if b in ("GEENPOST", "GEENPLUGIN", ""):
        return None
    return json.loads(base64.b64decode(b))

def inhoud(kant, pid):
    """de inhoud zoals hij in de database staat; de posities uit teksten.php horen hierbij"""
    map_ = LOKAAL if kant == "lokaal" else RAILWAY
    uit, code = wp(map_, "eval-file", os.path.join(TOOLS, "lees_ruw.php"), str(pid))
    b = laatste(uit)
    return base64.b64decode(b)   # bytes: php telt posities in bytes, niet in tekens

def schoon(t):
    return " ".join(t.split())

def vergelijk(pid):
    onl = teksten("online", pid)
    lok = teksten("lokaal", pid)
    if onl is None or lok is None:
        return None, None, []
    a = [schoon(x["t"]) for x in onl]
    b = [schoon(x["t"]) for x in lok]
    sm = difflib.SequenceMatcher(None, a, b, autojunk=False)
    wijzig = []
    for tag, i1, i2, j1, j2 in sm.get_opcodes():
        if tag != "replace":
            continue
        # alleen een-op-een vervangingen zijn veilig
        if ( i2 - i1 ) != ( j2 - j1 ):
            for k in range(i1, i2):
                wijzig.append(("?", a[k], None, None))
            continue
        for k in range(i2 - i1):
            wijzig.append(("=", b[j1 + k], lok[j1 + k]["o"], onl[i1 + k]["t"]))
    return onl, lok, wijzig

def main():
    doen = len(sys.argv) > 1 and sys.argv[1] == "doen"
    ids = [int(x) for x in sys.argv[2:] if x.isdigit()]
    if not ids:
        print("geef de post-id's mee"); return

    for pid in ids:
        onl, lok, wijzig = vergelijk(pid)
        if onl is None:
            print("  %-8s kon de teksten niet uitlezen" % pid); continue
        zeker = [w for w in wijzig if w[0] == "="]
        onzeker = [w for w in wijzig if w[0] == "?"]
        print("  %-8s %d tekst(en) online anders%s" % (pid, len(zeker), ("  (%d niet automatisch te koppelen)" % len(onzeker)) if onzeker else ""))
        for _, oud, off, nieuw in zeker[:8]:
            print("      lokaal: %-44s" % oud[:44])
            print("      online: %-44s" % schoon(nieuw)[:44])
        for _, oud, _, _ in onzeker[:4]:
            print("      ? %s" % oud[:60])

        if doen and zeker:
            s = inhoud("lokaal", pid)
            gedaan = 0
            for _, oud, off, nieuw in sorted(zeker, key=lambda w: -w[2]):
                huidig = [x for x in lok if x["o"] == off][0]["t"].encode("utf-8")
                if s[off:off+len(huidig)] != huidig:
                    print("      LET OP: positie klopt niet meer, overgeslagen: %s" % huidig.decode("utf-8","replace")[:40]); continue
                s = s[:off] + nieuw.encode("utf-8") + s[off+len(huidig):]
                gedaan += 1
            print("      %d van %d teksten overgenomen" % (gedaan, len(zeker)))
            pad = "/tmp/dtc-merge-%d.b64" % pid
            open(pad, "w").write(base64.b64encode(s).decode())
            map_tijd = "/tmp/dtc-merge-map"
            os.makedirs(map_tijd, exist_ok=True)
            os.replace(pad, os.path.join(map_tijd, "%d.b64" % pid))
    if doen:
        print("\n  samengevoegde pagina's staan klaar in /tmp/dtc-merge-map")

main()
