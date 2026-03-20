#!/bin/bash


# Checks if the sass command is available from inside node_modules. We use this instead of global sass to avoid conflicts.
if ! npx --no sass --version >/dev/null 2>&1; then
  echo >&2 "Sass não está instalado neste projeto. Rode 'npm install'."
  exit 1
fi

# Define o caminho.
echo "Compiling Sass..."

# Sass deprecation silencing for @import (will be migrated to @use/@forward in the future)
# Silence deprecation warnings from Bootstrap 4's legacy Sass syntax
# These are from Bootstrap internals and cannot be fixed without upgrading to Bootstrap 5
SASS_SILENCE="--silence-deprecation=import --silence-deprecation=global-builtin --silence-deprecation=color-functions --silence-deprecation=if-function --silence-deprecation=abs-percent"

#Style do Tema
cd src/assets/scss
# Compile without compression to preserve WordPress theme header comments
npx sass $SASS_SILENCE style.scss:../../style.css
echo "Tainacan's style compiled (with header preserved).";

# Create minified version using lightningcss (replaces clean-css-cli for better security)
if npx --no lightningcss --version >/dev/null 2>&1; then
    npx lightningcss --minify --bundle ../../style.css -o ../../style.min.css 2>/dev/null
    echo "Tainacan's style minified (lightningcss).";
elif command -v cleancss >/dev/null 2>&1 || npx --no cleancss --version >/dev/null 2>&1; then
    # Fallback to clean-css if lightningcss is not available
    npx cleancss --output ../../style.min.css ../../style.css 2>&1 | grep -v "WARNING: Ignoring local source map" || true
    echo "Tainacan's style minified (clean-css fallback).";
else
    echo "Warning: No CSS minifier available. Skipping minified version.";
fi

npx sass -s compressed $SASS_SILENCE editor-style.scss:../../editor-style.css
echo "Tainacan's Gutenberg Editor style compiled";

npx sass -s compressed $SASS_SILENCE editor-style-legacy.scss:../../editor-style-legacy.css
echo "Tainacan's Gutenberg Editor legacy style compiled";

npx sass -s compressed $SASS_SILENCE bootstrap_custom.scss:../vendor/bootstrap/scss/bootstrap.min.css
echo "Bootstrap style compiled";

rm -rf .sass-cache

cd ../../../
rm -rf style.css.map
rm -rf editor-style.css.map
rm -rf assets/vendor/bootstrap/scss/bootstrap.min.css.map
echo "CSS map files removed";

echo "Sass compilation Completed!"
exit 0
