#!/bin/bash
source build-config.cfg

echo "Updating theme files"
rm -rf $destination
mkdir $destination

##Removendo o arquivo sass
##rm -rf $destination/scss

## Install npm dependencies and copy Bootstrap files
npm install

## Compile SASS
sh compile-sass.sh

## Ensure Bootstrap CSS is compiled
if [ ! -f "src/assets/vendor/bootstrap/scss/bootstrap.min.css" ]; then
    echo "Warning: Bootstrap CSS not found. Recompiling..."
    cd src/assets/scss
    npx sass -s compressed bootstrap_custom.scss:../vendor/bootstrap/scss/bootstrap.min.css
    cd ../../..
fi

cp -R src/* $destination/

##Bootstrap
# Bootstrap files are now handled in the cleanup section below

## Clean up source files not needed in final theme
echo "Cleaning up source files..."

# Remove SCSS source files (only compiled CSS should remain)
rm -rf $destination/assets/scss/

# Clean up Bootstrap vendor files - keep only what's needed
if [ -d "$destination/assets/vendor/bootstrap" ]; then
    echo "Cleaning up Bootstrap vendor files..."
    
    # Keep only the essential JS and compiled CSS
    mkdir -p $destination/assets/vendor/bootstrap/js
    
    # Remove source files but preserve the compiled bootstrap.min.css
    find $destination/assets/vendor/bootstrap/scss -name "*.scss" -delete
    rm -rf $destination/assets/vendor/bootstrap/js/src/
    rm -rf $destination/assets/vendor/bootstrap/js/dist/
    
    # Remove other unnecessary Bootstrap files
    find $destination/assets/vendor/bootstrap -name "*.scss" -delete
    find $destination/assets/vendor/bootstrap -name "*.js" ! -name "bootstrap.min.js" -delete
    find $destination/assets/vendor/bootstrap -name "*.map" -delete
    find $destination/assets/vendor/bootstrap -name "*.md" -delete
    find $destination/assets/vendor/bootstrap -name "*.txt" -delete
    find $destination/assets/vendor/bootstrap -name "*.yml" -delete
    find $destination/assets/vendor/bootstrap -name "*.json" -delete
    find $destination/assets/vendor/bootstrap -name "*.xml" -delete
    find $destination/assets/vendor/bootstrap -name "*.html" -delete
    
    # Remove empty directories
    find $destination/assets/vendor/bootstrap -type d -empty -delete 2>/dev/null || true
fi

# Remove source maps (not needed in production)
find $destination -name "*.css.map" -delete
find $destination -name "*.js.map" -delete

# Remove development-only files
rm -rf $destination/vendor/
rm -rf $destination/node_modules/
rm -rf $destination/.git/
rm -rf $destination/.gitignore
rm -rf $destination/README.md
rm -rf $destination/package.json
rm -rf $destination/package-lock.json
rm -rf $destination/build.sh
rm -rf $destination/build-watch.sh
rm -rf $destination/compile-sass.sh
rm -rf $destination/install.sh
rm -rf $destination/build-config.cfg
rm -rf $destination/build-config-sample.cfg
rm -rf $destination/theme-unit-test/

# Remove any other development artifacts
find $destination -name ".DS_Store" -delete 2>/dev/null || true
find $destination -name "Thumbs.db" -delete 2>/dev/null || true
find $destination -name "*.log" -delete 2>/dev/null || true

echo "Cleanup completed!"

echo "Compilation finished!"
