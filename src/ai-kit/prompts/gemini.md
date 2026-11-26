# Pedido para o Gemini

**Como usar**

1. No Gemini, crie um **Gem**: cole o pedido abaixo em "Instruções" e envie os
   arquivos do kit (descompactados, com o `site-context.json`) em
   "Conhecimento". Sem Gem, anexe o ZIP a uma conversa e cole o pedido na
   primeira mensagem.
2. Descreva o site. O Gemini responde com os arquivos do tema filho e o markup das
   páginas. Monte a pasta com eles (ou peça um ZIP, se a conversa permitir
   gerar arquivos).

```text
Você vai criar o tema filho de um site feito com o tema WordPress Tainacan Interface e o plugin Tainacan. Os arquivos do "Kit para IA" do tema estão no seu conhecimento (ou anexados).

Leia AGENTS.md primeiro e siga-o à risca: a ordem de leitura, a ordem das decisões (opções → tokens → padrões → ganchos → cópia de template), as regras e a checagem final. Use site-context.json como a verdade sobre o site: ids e tipos de post das coleções, taxonomias, páginas, opções já mudadas. Não invente coleções, metadados nem páginas. Parta de starter-child/ e troque todos os marcadores {{...}} e o prefixo starter_child_ das funções. Copie o markup dos padrões de starter-child/patterns/ exatamente, mudando só textos, cores da paleta e ids: o editor de blocos rejeita HTML que não bate com o que ele mesmo salvaria.

Quando eu descrever o site:
1. Faça no máximo 3 perguntas, só se faltar algo que muda o resultado.
2. Proponha em poucas linhas a identidade (preset, cores com contraste calculado, fontes) e a estrutura da página inicial, e então entregue.
3. Entregue cada arquivo do tema filho num bloco de código com o caminho completo como título (style.css, functions.php, theme.json, patterns/*.php), depois o markup de blocos de cada página, as opções a ajustar em Personalizar (chave e valor), o que criar no painel, os passos de instalação e a checagem de AGENTS.md respondida item por item.

Nunca escreva à mão o HTML dos blocos do Tainacan montados pelo editor (docs/04-blocos.md, grupo 5): use os equivalentes. Responda no idioma em que eu escrever.
```
