# TNC-C02 · Seletor CSS global vazando para tema e outros plugins

**Severidade:** aviso (sempre) · **Categoria:** convivencia · **Arquivos:** .css, .scss

## Sintoma
Depois de ativar o plugin, partes do site que nada têm a ver com ele mudam de aparência: links sem sublinhado no tema, tabelas do admin do Tainacan com outro espaçamento, títulos menores em todas as páginas, rolagem da página travada.

## Causa
A folha de estilo do plugin é carregada na mesma página que o tema, o admin do Tainacan e outros plugins. Um seletor de elemento solto (`a`, `img`, `h3`, `table`), o universal `*` ou `html`/`body` sem classe valem para tudo o que está na página, não só para o componente do plugin.

## O que a regra detecta
- Regra cujo seletor é `html` ou `body` sozinho (sem descendente): "altera a página inteira".
- Regra cujo primeiro componente do seletor é um elemento HTML comum (por exemplo `a`, `div`, `img`, `h1` a `h6`, `table`, `input`, `button`, `span`, `p`, `ul`, `li`) ou `*`, com ou sem pseudo-classe (`a:hover`). Também `body p` e `html h2`, em que o primeiro componente depois de `html`/`body` é um elemento.
- Analisa cada seletor da lista separada por vírgulas: `img, .meu-plugin img` é acusado por causa do `img`.
- Vale para regras dentro de `@media` e `@supports`.
- Não dispara para seletor que começa por classe ou id (`.meu-plugin a`), `body.minha-classe ...`, `:root`, quadros de `@keyframes` (`from`, `to`, porcentagens), nem para regras aninhadas dentro de outra regra (SCSS).
- Não dispara para elemento com atributo ou pseudo-elemento colado (`input[type=text]`, `p::before`), nem para comentários.
- Não analisa arquivos de admin, editor ou impressão (`admin*.css`, `editor*.css`, `print*.css`, `impressao*.css`) quando o `.tnc-check.json` declara `"css_admin_isolated": true`.

## Como corrigir
Prefixe toda regra com a classe raiz do componente do plugin ou com uma classe própria no `body`.

```css
/* errado */
a { text-decoration: none; }
body { overflow: hidden; }

/* certo */
.meu-plugin a { text-decoration: none; }
body.meu-plugin-modal-aberto { overflow: hidden; }
```

## Quando suprimir
Folha que só é carregada numa página montada inteiramente pelo plugin, sem o tema (folha de impressão, tela isolada): declare `"css_admin_isolated": true` no `.tnc-check.json` quando o nome do arquivo seguir o padrão, ou suprima a regra pontual com `/* tnc-check-ignore TNC-C02: folha carregada só na página de impressão do plugin */`.

## Origem
Boa prática geral de CSS em plugins WordPress. Plugins da equipe tiveram problemas parecidos de vazamento e colisão de estilos entre o plugin, o tema e o admin do Tainacan (por exemplo, classe genérica reaproveitada que achatou componentes de outra tela); a regra cobre a forma mais direta desse vazamento, o seletor sem escopo.
