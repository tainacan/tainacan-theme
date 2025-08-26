# Tainacan Interface

Tema WordPress padrão do Tainacan, para ser usado com o plugin Tainacan

# Instalação no WordPress

- Copiar o arquivo `build-config-sample.cfg` para `build-config.cfg`
- Ajustar o `PATH` da variável `destination` para o caminho desejado correspondente
- Instalar as dependências Node.js
  - `npm install`
- Execute o build
  - `npm run build` ou `./build.sh`

## Dependências

Este tema agora usa npm para gerenciar dependências:
- **Sass**: Para compilação de SCSS (versão 1.58.3)
- **Bootstrap**: Framework CSS (versão 4.6.1)

## Scripts disponíveis

- `npm run build` - Executa o build completo
- `npm run build:watch` - Executa o build em modo watch
- `npm run compile-sass` - Compila apenas os arquivos Sass
- `npm run install-bootstrap` - Copia os arquivos do Bootstrap para a pasta src
- `npm run postinstall` - Executado automaticamente após `npm install`
