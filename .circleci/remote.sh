#!/bin/sh
set -ex

export tag = $1;

cd /var/www/koyolympus
ls -a
echo tag