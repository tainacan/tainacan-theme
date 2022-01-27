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

##Bootstrap
rm -rf $destination/assets/vendor/ekko-lightbox

##Slick
mkdir -p $destination/assets/vendor/slick/css
cp $destination/assets/vendor/slick/slick.min.css $destination/assets/vendor/slick/css/slick.min.css
cp $destination/assets/vendor/slick/slick-theme.min.css $destination/assets/vendor/slick/css/slick-theme.min.css
mv $destination/assets/vendor/slick/ajax-loader.gif $destination/assets/vendor/slick/css/ajax-loader.gif
mv $destination/assets/vendor/slick/fonts/ $destination/assets/vendor/slick/css/fonts/
cp $destination/assets/vendor/slick/slick.min.js $destination/assets/vendor/slick/js/slick.min.js

echo "Compilation finished!"
