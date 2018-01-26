#!/bin/bash
 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "SASS does not seem to be available.";
  exit 1;
}
 
# Define o caminho.
echo "Compiling Sass..."

#Bootstrap
cd src/assets/vendor/bootstrap/scss
sass bootstrap.scss:bootstrap.min.css --style compressed

#Style do Tema
cd ../../../../assets/scss
sass -E 'UTF-8' style.scss:../../style.css
cd ../..
rm -rf style.css.map

echo "Sass compilation Completed!"
exit 0
