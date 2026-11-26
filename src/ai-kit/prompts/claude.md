# Pedido para o Claude (claude.ai)

**Como usar**

1. Em claude.ai, crie um **Projeto** (ex.: "Site do acervo").
2. Em "Conhecimento do projeto", envie o ZIP do kit (Aparência → Kit para IA →
   Baixar o kit) ou os arquivos dele. O `site-context.json` precisa ir junto.
3. Em "Instruções do projeto", ou na primeira mensagem de uma conversa do
   Projeto, cole o pedido abaixo.
4. Descreva o site. O Claude devolve o tema filho (em ZIP, se a criação de
   arquivos estiver ligada, ou arquivo por arquivo) e as páginas em markup de
   blocos.

```text
Você vai criar o tema filho de um site feito com o tema WordPress Tainacan Interface e o plugin Tainacan. Os arquivos do "Kit para IA" do tema estão no conhecimento deste Projeto.

Antes de tudo, leia AGENTS.md e siga-o à risca: a ordem de leitura, a ordem das decisões (opções → tokens → padrões → ganchos → cópia de template), as regras e a checagem final. Use site-context.json como a verdade sobre o site: ids e tipos de post das coleções, taxonomias, páginas, opções já mudadas. Não invente coleções, metadados nem páginas. Parta sempre de starter-child/.

Quando eu descrever o site:
1. Faça no máximo 3 perguntas, só se faltar algo que muda o resultado (público, tom, cores, coleções em destaque, páginas).
2. Proponha em poucas linhas a identidade (preset, cores com contraste calculado, fontes) e a estrutura da página inicial, e então entregue.
3. Entregue: (a) o tema filho completo, como ZIP para baixar se puder criar arquivos, ou cada arquivo num bloco de código com o caminho como título; (b) o markup de blocos de cada página; (c) as opções a ajustar em Personalizar (chave e valor); (d) o que criar no painel; (e) os passos de instalação; (f) a checagem de AGENTS.md respondida item por item, dizendo o que não pôde verificar.

Nunca escreva à mão o HTML dos blocos do Tainacan montados pelo editor (docs/04-blocos.md, grupo 5): use os equivalentes. Responda no idioma em que eu escrever.
```
