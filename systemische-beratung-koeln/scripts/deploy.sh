#!/usr/bin/env bash
# Deploy nach ALL-INKL per SFTP (bevorzugt) oder FTPS. Niemals unverschlüsseltes FTP.
# Zugangsdaten stehen in .env (nicht im Repository, nicht im Webroot).
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Fehler: .env fehlt (Vorlage: .env.example)"; exit 1; }
set -a; . ./.env; set +a
: "${DEPLOY_HOST:?}" "${DEPLOY_USER:?}" "${DEPLOY_PASSWORD:?}" "${DEPLOY_REMOTE_DIR:?}"
PROTO="${DEPLOY_PROTOCOL:-sftp}"
command -v lftp >/dev/null || { echo "Fehler: lftp ist nicht installiert"; exit 1; }

echo "== 1/5 Installation (npm ci)"; npm ci
echo "== 2/5 Textprüfung";            npm run lint:text
echo "== 3/5 Build + Typprüfung";     npm run check && npm run build
echo "== 4/5 Prüfung dist/";          npm run verify
echo "   Linkcheck intern (extern: separat mit linkinator gegen die Live-Seite)"

case "$PROTO" in
  sftp) URL="sftp://$DEPLOY_HOST"; SETTINGS="set sftp:auto-confirm no" ;;
  ftps) URL="ftp://$DEPLOY_HOST";  SETTINGS="set ftp:ssl-force true; set ftp:ssl-protect-data true; set ssl:verify-certificate yes" ;;
  *) echo "Fehler: DEPLOY_PROTOCOL muss sftp oder ftps sein"; exit 1 ;;
esac

echo "== 5/5 Upload ($PROTO) mit --delete (entfernt Altlasten auf dem Server)"
read -r -p "Webroot $DEPLOY_REMOTE_DIR auf $DEPLOY_HOST wird gespiegelt und bereinigt. Wirklich? [ja/N] " a
[ "$a" = "ja" ] || { echo "Abgebrochen."; exit 1; }
lftp -u "$DEPLOY_USER,$DEPLOY_PASSWORD" -e "$SETTINGS; mirror --reverse --delete --verbose dist/ $DEPLOY_REMOTE_DIR; bye" "$URL"
echo "Fertig. Weiter mit den Sicherheitstests in DEPLOY.md."
