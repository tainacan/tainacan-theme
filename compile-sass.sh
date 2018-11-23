#!/bin/bash
 
 
#Teste commit 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "REQUIRE: SASS installed to compile the SCSS archives to CSS.";
  exit 1;
}
 
# Define o caminho.
echo "Compiling Sass..."

#Bootstrap
#cd src/assets/vendor/bootstrap/scss
#sass bootstrap.scss:bootstrap.min.css --style compressed

#Style do Tema
#cd ../../../../assets/scss
cd src/assets/scss
sass -E 'UTF-8' style.scss:../../style.css --style compressed
echo "Style of Tainacan Compiled";
sass bootstrap_custom.scss:../vendor/bootstrap/scss/bootstrap.min.css --style compressed
echo "Bootstrap Compiled";
rm -rf .sass-cache
cd ../vendor/slick/scss
sass slick.scss:slick.min.css --style compressed
sass slick-theme.scss:slick-theme.min.css --style compressed
echo "Slick for slider carousel Compiled";
rm -rf slick-theme.min.css.map slick.min.css.map .sass-cache/
cd ../../../scss
rm -rf ../../style.css.map

echo "Sass compilation Completed!"
exit 0
