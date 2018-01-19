#!/bin/bash

source build-config.cfg

## Compile SASS
sh compile-sass.sh

## Install composer dependencies
## composer install

echo "Updating files in $destination"
rm -rf $destination
mkdir $destination
cp -R src/* $destination/
rm -rf $destination/scss
