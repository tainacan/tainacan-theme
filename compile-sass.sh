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
sass -E 'UTF-8' -t compressed style.scss:../../style.css
echo "Tainacan's style compiled.";

sass -E 'UTF-8' -t compressed editor-style.scss:../../editor-style.css
echo "Tainacan's Gutenberg Editor style compiled";

sass bootstrap_custom.scss:../vendor/bootstrap/scss/bootstrap.min.css --style compressed
echo "Bootstrap style compiled";

rm -rf .sass-cache
cd ../vendor/slick
sass slick.scss:slick.min.css --style compressed
sass slick-theme.scss:slick-theme.min.css --style compressed
echo "Slick for slider carousel compiled";

rm -rf slick-theme.min.css.map slick.min.css.map .sass-cache/
cd ../../../
rm -rf style.css.map
rm -rf editor-style.css.map
rm -rf assets/vendor/bootstrap/scss/bootstrap.min.css.map
echo "CSS map files removed";

echo "Sass compilation Completed!"
exit 0
