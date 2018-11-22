# tainacan-theme
Default Tainacan Theme, to be used with tainacan plugin


# Instalação no WordPress
- Copiar o arquivo `build-config-sample.cfg` para `build-config.cfg`
- Ajustar o `PATH` da variável `destination` para o caminho desejado correspondente
- Instalar o SASS
    - sudo apt install ruby-full rubygems autogen autoconf libtool make
    - sudo gem install sass
- Instalar o Composer
    - php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    - HASH="$(wget -q -O - https://composer.github.io/installer.sig)"
    - php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    - sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
- Execute o build
    - ./build.sh