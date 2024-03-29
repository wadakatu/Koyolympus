#!/bin/sh
set -eu

export TAG=$1;
export SUCCESS=200
export STATUS=400

cd /var/www/koyolympus/koyolympus && php artisan down
git checkout . && git fetch && git checkout $TAG
composer install --no-dev
npm install && npm run prod
sudo systemctl reload nginx && php artisan up && STATUS=`curl -LI https://koyolympus.gallery -o /dev/null -w '%{http_code}\n' -s`

if [ $STATUS -ne $SUCCESS ]; then
  exit 1;
fi