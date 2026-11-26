# Tainacan Interface
[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)

Tema WordPress padrão do Tainacan, para ser usado com o plugin Tainacan

# Instalação no WordPress

- Copiar o arquivo `build-config-sample.cfg` para `build-config.cfg`
- Ajustar o `PATH` da variável `destination` para o caminho desejado correspondente
- Instalar as dependências Node.js
  - `npm install`
- Execute o build
  - `npm run build` ou `./build.sh`

## Dependências

Este tema usa npm só para compilar os estilos:
- **Sass**: compilação de SCSS (versão 1.58.3)
- **clean-css-cli**: versão minificada do `style.css`

O tema não depende mais do Bootstrap: o pouco que ele usava está em `src/assets/css/foundation.css` (derivado do Bootstrap 4.6.1, licença MIT). O CSS compilado (`style.css`, `style.min.css`, `editor-style.css`) fica versionado, então o tema funciona sem passo de build.

## Scripts disponíveis

- `npm run build` - Executa o build completo
- `npm run build:watch` - Executa o build em modo watch
- `npm run compile-sass` - Compila apenas os arquivos Sass

O histórico de versões está em `CHANGELOG.md`.
