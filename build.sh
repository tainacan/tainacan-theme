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
mkdir $destination/vendor/bootstrap/css
cp $destination/vendor/bootstrap/scss/bootstrap.min.css $destination/vendor/bootstrap/css
rm -rf $destination/vendor/bootstrap/scss

echo "Finish!!"