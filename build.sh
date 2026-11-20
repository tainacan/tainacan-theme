#!/bin/bash
source build-config.cfg

echo "Updating theme files"
rm -rf $destination
mkdir $destination

## Install npm dependencies (sass and clean-css-cli)
npm install

## Compile SASS
sh compile-sass.sh

cp -R src/* $destination/

## Clean up source files not needed in final theme
echo "Cleaning up source files..."

# Remove SCSS source files (only compiled CSS should remain)
rm -rf $destination/assets/scss/

# Remove source maps (not needed in production)
find $destination -name "*.css.map" -delete
find $destination -name "*.js.map" -delete

# Remove development-only files
rm -rf $destination/vendor/
rm -rf $destination/node_modules/
rm -rf $destination/.git/
rm -rf $destination/.gitignore
rm -rf $destination/README.md
rm -rf $destination/CHANGELOG.md
rm -rf $destination/package.json
rm -rf $destination/package-lock.json
rm -rf $destination/build.sh
rm -rf $destination/build-watch.sh
rm -rf $destination/compile-sass.sh
rm -rf $destination/build-config.cfg
rm -rf $destination/build-config-sample.cfg
rm -rf $destination/theme-unit-test/

# Remove any other development artifacts
find $destination -name ".DS_Store" -delete 2>/dev/null || true
find $destination -name "Thumbs.db" -delete 2>/dev/null || true
find $destination -name "*.log" -delete 2>/dev/null || true

echo "Cleanup completed!"

echo "Compilation finished!"
