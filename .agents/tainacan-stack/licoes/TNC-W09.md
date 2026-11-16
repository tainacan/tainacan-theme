# TNC-W09 · JavaScript inseguro (eval, innerHTML variável, v-html)

**Severidade:** aviso; `eval(` e `new Function(` são bloqueio · **Categoria:** seguranca · **Arquivos:** .js, .jsx, .ts, .tsx, .vue

## Sintoma
Um título, descrição ou comentário cadastrado no acervo executa script na página de outra pessoa (XSS armazenado), ou um parâmetro da URL injeta HTML na tela. Em outros casos, a página fica em branco porque `document.write` rodou depois do carregamento.

## Causa
`innerHTML`, `outerHTML`, `insertAdjacentHTML` e `v-html` interpretam o valor como HTML. Se o valor vem do usuário ou da API sem sanitização no servidor, qualquer marcação vira parte da página. `eval` e `new Function` executam texto como código. No Tainacan, o HTML que o núcleo já entrega sanitizado passa por `wp_kses` no servidor, como `Item::get_metadata_as_html()` (`classes/entities/class-tainacan-item.php:776`); campos de texto puro, como nomes e títulos vindos da REST, não têm essa garantia.

## O que a regra detecta
- `eval(` (fora de `.eval(`) e `new Function(`: bloqueio.
- `document.write(` e `document.writeln(`: aviso.
- Atribuição `.innerHTML =`, `.innerHTML +=`, `.outerHTML =` com valor que não é string literal sem interpolação: aviso.
- `.insertAdjacentHTML( 'posicao', valor )` com valor que não é string literal: aviso.
- Em arquivos `.vue`: `v-html="expressao"` cuja expressão não indica HTML confiável: aviso.
- Não dispara para `innerHTML = ''`, `innerHTML = '<p>fixo</p>'` e template literal sem `${}`.
- Não dispara para `v-html` cuja expressão contém (sem diferenciar maiúsculas) `sanitiz`, `safe`, `purif`, `kses`, `_as_html`, `AsHtml`, `$i18n`, `i18n.`, `Hook`, `hooks[`, `Form`, `_form`, `.icon`, `renderMetadata` ou `render...Label`. É uma lista por nome: `v-html="webhookPayload"` passa porque contém `hook`.
- Não analisa `v-html` fora de `.vue` (por exemplo em template de string), nem `dangerouslySetInnerHTML`. Linhas com mais de 1000 caracteres (minificadas) e comentários são ignorados.

## Como corrigir
1. Para texto, use `textContent` (ou `{{ }}` em Vue).
2. Para estrutura, monte com `createElement`/`append`.
3. Para HTML que precisa vir pronto, sanitize no servidor (`wp_kses`) e deixe isso claro no nome (`descricaoSanitizada`, `..._as_html`).

```js
// errado
el.innerHTML = dados.titulo;

// certo
el.textContent = dados.titulo;
```

```vue
<!-- errado -->
<div v-html="item.descricao"></div>
<!-- certo: HTML sanitizado no servidor -->
<div v-html="item.descricao_as_html"></div>
```

## Quando suprimir
HTML que vem de fonte confiável que a regra não reconhece pelo nome, como um campo que o próprio plugin sanitiza com `wp_kses` no PHP: prefira renomear a variável; se não der, `<!-- tnc-check-ignore TNC-W09: HTML gerado pelo PHP do plugin e passado por wp_kses -->` (em JS, `// tnc-check-ignore TNC-W09: <motivo do caso>`). Não suprima `eval`.

## Origem
Plugins de visualização semântica e de narrativas adotaram interface por DOM puro, sem `innerHTML`, para não expor dados do acervo a XSS, e um plugin de processos teve XSS armazenado ao injetar conteúdo do usuário cru no React. A regra também segue a boa prática geral de segurança no front-end.
