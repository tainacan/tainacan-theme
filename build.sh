#!/bin/bash
command -v sass >/dev/null 2>&1 || {
  echo >&2 "REQUIRED: Sass installed to compile SCSS archives to CSS.";
  exit 1;
}

source build-config.cfg

echo "Updating theme files"
rm -rf $destination
mkdir $destination

##Removendo o arquivo sass
##rm -rf $destination/scss

## Install composer dependencies
composer install

## Compile SASS
sh compile-sass.sh

cp -R src/* $destination/

##Bootstrap
mkdir $destination/assets/vendor/bootstrap/css
cp $destination/assets/vendor/bootstrap/scss/bootstrap.min.css $destination/assets/vendor/bootstrap/css/bootstrap.min.css
rm -rf $destination/vendor

echo "Compilation finished!"
