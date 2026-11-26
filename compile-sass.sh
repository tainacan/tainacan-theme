#!/bin/bash
# Compiles the theme Sass (src/assets/scss) into src/style.css, src/style.min.css and
# src/editor-style.css. The compiled CSS is versioned, so the theme works without a
# build step. Needs `npm install` once (sass and clean-css-cli).
set -e

# Uses the sass inside node_modules, not a global one, to avoid version conflicts.
if ! npx --no sass --version >/dev/null 2>&1; then
	echo >&2 "Sass is not installed in this project. Run 'npm install'."
	exit 1
fi

cd "$(dirname "$0")/src/assets/scss"

echo "Compiling Sass..."
# Not compressed: the WordPress theme header comment must survive.
npx sass --no-source-map style.scss:../../style.css
npx cleancss --output ../../style.min.css ../../style.css
npx sass --no-source-map -s compressed editor-style.scss:../../editor-style.css

echo "Sass compilation completed."
