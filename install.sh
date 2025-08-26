#!/bin/bash
echo " ..::Tainacan Theme::.."
echo "Copying files ..."
echo "Copying Bootstrap Framework ..."

# Create directories if they don't exist
mkdir -p src/assets/vendor/bootstrap/scss
mkdir -p src/assets/vendor/bootstrap/js

# Copy Bootstrap SCSS files from node_modules
cp -r node_modules/bootstrap/scss/* src/assets/vendor/bootstrap/scss/

# Copy Bootstrap JavaScript files
cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js src/assets/vendor/bootstrap/js/bootstrap.min.js
cp -r node_modules/bootstrap/js/src/* src/assets/vendor/bootstrap/js/src/

echo "Finished copying files!"
echo "Bootstrap files copied from node_modules to src/assets/vendor/bootstrap/"