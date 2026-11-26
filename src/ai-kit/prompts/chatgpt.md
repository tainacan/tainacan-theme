# Pedido para o ChatGPT

**Como usar**

1. No ChatGPT, crie um **Projeto** e envie o ZIP do kit (Aparência → Kit para IA
   → Baixar o kit) aos arquivos do Projeto. Outra opção é criar um **GPT** com o
   pedido abaixo nas instruções e o ZIP em "Conhecimento".
2. Cole o pedido abaixo nas instruções do Projeto ou na primeira mensagem.
3. Descreva o site. Peça o tema filho como ZIP para baixar: o ChatGPT cria o
   arquivo com a ferramenta de análise de dados (Python).

```text
Você vai criar o tema filho de um site feito com o tema WordPress Tainacan Interface e o plugin Tainacan. O "Kit para IA" do tema está nos arquivos deste Projeto, em ZIP. Se for o ZIP, descompacte-o com a ferramenta de Python antes de começar e leia os arquivos de verdade, não de memória.

Leia AGENTS.md primeiro e siga-o à risca: a ordem de leitura, a ordem das decisões (opções → tokens → padrões → ganchos → cópia de template), as regras e a checagem final. Use site-context.json como a verdade sobre o site: ids e tipos de post das coleções, taxonomias, páginas, opções já mudadas. Não invente coleções, metadados nem páginas. Parta de starter-child/ e troque todos os marcadores {{...}} e o prefixo starter_child_ das funções.

Quando eu descrever o site:
1. Faça no máximo 3 perguntas, só se faltar algo que muda o resultado.
2. Proponha em poucas linhas a identidade (preset, cores com contraste calculado, fontes) e a estrutura da página inicial, e então entregue.
3. Monte o tema filho numa pasta com Python, confira com Python (nenhum "{{" sobrando, theme.json válido, cabeçalho do style.css completo, padrões com Title e Slug) e gere o ZIP para eu baixar.
4. Entregue também: o markup de blocos de cada página, as opções a ajustar em Personalizar (chave e valor), o que criar no painel, os passos de instalação e a checagem de AGENTS.md respondida item por item.

Nunca escreva à mão o HTML dos blocos do Tainacan montados pelo editor (docs/04-blocos.md, grupo 5): use os equivalentes. Responda no idioma em que eu escrever.
```
