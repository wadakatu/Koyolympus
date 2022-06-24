#!/bin/sh
set -ex

export TAG=$1;
export SUCCESS=200

cd /var/www/koyolympus/koyolympus
git checkout . && git fetch && git checkout $TAG
composer install
npm install && npm run prod
systemctl reload nginx && STATUS=`curl -LI https://koyolympus.gallery -o /dev/null -w '%{http_code}\n' -s`

if [ $STATUS -ne $SUCCESS ]; then
  exit 1;
fi




