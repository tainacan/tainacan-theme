#!/bin/bash
 
 
# Checks if the sass command is available from inside node_modules. We use this instead of global sass to avoid conflicts.
if ! npx --no sass --version >/dev/null 2>&1; then
  echo >&2 "Sass não está instalado neste projeto. Rode 'npm install'."
  exit 1
fi
 
# Define o caminho.
echo "Compiling Sass..."

#Bootstrap
#cd src/assets/vendor/bootstrap/scss
#sass bootstrap.scss:bootstrap.min.css --style compressed

#Style do Tema
#cd ../../../../assets/scss
cd src/assets/scss
# Compile without compression to preserve WordPress theme header comments
npx sass style.scss:../../style.css
echo "Tainacan's style compiled (with header preserved).";

# Create minified version using clean-css (header can be removed in minified version)
if command -v cleancss >/dev/null 2>&1 || npx --no cleancss --version >/dev/null 2>&1; then
    # Create minified version (suppress source map warnings - harmless, file will still be created)
    npx cleancss --output ../../style.min.css ../../style.css 2>&1 | grep -v "WARNING: Ignoring local source map" || true
    echo "Tainacan's style minified.";
else
    echo "Warning: clean-css-cli not available. Skipping minified version.";
fi

npx sass -s compressed editor-style.scss:../../editor-style.css
echo "Tainacan's Gutenberg Editor style compiled";

npx sass -s compressed editor-style-legacy.scss:../../editor-style-legacy.css
echo "Tainacan's Gutenberg Editor legacy style compiled";

npx sass -s compressed bootstrap_custom.scss:../vendor/bootstrap/scss/bootstrap.min.css
echo "Bootstrap style compiled";

rm -rf .sass-cache

cd ../../../
rm -rf style.css.map
rm -rf editor-style.css.map
rm -rf assets/vendor/bootstrap/scss/bootstrap.min.css.map
echo "CSS map files removed";

echo "Sass compilation Completed!"
exit 0
