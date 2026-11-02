#!/bin/sh
# Démarrage du conteneur : port imposé par l'hébergeur + certificat de la base MySQL
set -e

# Render (et la plupart des hébergeurs) fournit le port d'écoute dans $PORT
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Certificat d'autorité de la base MySQL (connexion chiffrée), fourni en variable d'environnement
if [ -n "$DB_CA_CERT" ]; then
    printf '%s\n' "$DB_CA_CERT" > /tmp/db-ca.pem
    export DB_SSL_CA=/tmp/db-ca.pem
fi

# Les variables d'environnement doivent être visibles par PHP sous Apache
exec "$@"
