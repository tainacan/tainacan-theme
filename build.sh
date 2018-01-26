#!/bin/bash

source build-config.cfg

## Install composer dependencies
composer install

## Compile SASS
sh compile-sass.sh

echo "Updating files of theme"
rm -rf $destination
mkdir $destination
cp -R src/* $destination/

##Removendo o arquivo sass
rm -rf $destination/scss

##Bootstrap
mkdir $destination/assets/vendor/bootstrap/css
cp $destination/assets/vendor/bootstrap/scss/bootstrap.min.css $destination/assets/vendor/bootstrap/css/bootstrap.min.css
rm -rf $destination/assets/vendor/bootstrap/scss

echo "Finish!!"