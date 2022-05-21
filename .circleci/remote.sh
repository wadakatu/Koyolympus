#!/bin/sh
set -ex

export TAG=$1;

cd /var/www/koyolympus/koyolympus
ls -a
echo $TAG