# TNC-W14 · Asset carregado de servidor externo (CDN)

**Severidade:** aviso (sempre) · **Categoria:** wordpress · **Arquivos:** .php, .js, .vue, .css, .scss (só em projeto WordPress)

## Sintoma
O WordPress.org recusa o plugin: "Offloading images, js, css, and other scripts to your servers or any remote service is disallowed" (`PluginCheck.CodeAnalysis.Offloading`). O gráfico do painel some quando o CDN muda a versão ou fica fora do ar. O site de um museu público envia o IP de cada visitante ao Google Fonts, e o encarregado de dados pergunta com que base legal.

## Causa
Código de terceiro carregado em tempo de execução coloca o site na dependência de um servidor que o administrador não controla e que pode mudar o conteúdo sem aviso (um CDN comprometido executa JavaScript no admin com a sessão do usuário). Fontes e estilos externos também entregam o IP e o `Referer` do visitante a terceiros. O WordPress.org exige que tudo venha do pacote do plugin.

## O que a regra detecta
- `wp_enqueue_script()`, `wp_enqueue_style()`, `wp_register_script()`, `wp_register_style()` e as variantes `_module` com URL literal começando por `http://`, `https://` ou `//`.
- `<script src="https://...">` e `<link rel="stylesheet" href="https://...">` em HTML inline ou em string PHP.
- JavaScript: `import ... from 'https://...'`, `import('https://...')`, `elemento.src = 'https://...'`, `<script src="//...">` em template.
- CSS/SCSS: `@import` de URL externa e `url(https://...)` apontando para fonte (`.woff`, `.woff2`, `.ttf`, `.otf`), `.css` ou `.js`.

Não acusa: URL montada com `plugins_url()`, `plugin_dir_url()`, constante do plugin ou caminho relativo; links de navegação (`<a href>`), `canonical`, `@context` de JSON-LD, imagens em `url()` de CSS.

## Como corrigir
1. Baixe a biblioteca para `assets/vendor/<lib>/` (ou instale pelo npm e inclua no bundle).
2. Enfileire do pacote, com a versão pinada no comentário:

```php
// Chart.js 4.4.1 (MIT), de https://github.com/chartjs/Chart.js/releases
wp_enqueue_script( 'meu-plugin-chart', plugins_url( 'assets/vendor/chart/chart.umd.js', MEU_PLUGIN_FILE ), [], '4.4.1', true );
```

3. Fontes: baixe os `.woff2` e escreva o `@font-face` apontando para o plugin; ou use a pilha de fontes do sistema.
4. Serviços que só existem remotamente (reCAPTCHA, mapas com tiles) são exceção documentada: deixe a dependência explícita no `readme.txt` (seção "External services") e suprima na linha.

## Quando suprimir
`// tnc-check-ignore TNC-W14: reCAPTCHA só existe como serviço do Google; dependência declarada no readme.txt`. Tiles de mapa carregados pelo Leaflet por configuração também são serviço, não biblioteca.

## Origem
Plugin de comprovante com reCAPTCHA e plugin de indicadores com Chart.js pelo jsDelivr, ambos recusados na primeira submissão ao WordPress.org. Em outro, a troca do CDN para `assets/vendor/` eliminou a diferença entre o ambiente de testes (com internet) e a intranet do museu (sem).
