# Kit para IA do Tainacan Interface

> **English.** This folder ships with the theme. It lets Claude, ChatGPT, Gemini or
> any other AI change Tainacan Interface the right way: make a child theme, set the
> options, and build pages with blocks that keep Tainacan and the block editor
> working. AIs start at `AGENTS.md`. People start here, or at
> Appearance → Kit for AI in the WordPress admin, which downloads this folder with
> the context of the site.

## Para que serve

Pedir a uma IA "faça um site bonito para o nosso acervo" costuma dar errado de
três jeitos: ela edita o tema pai, e a próxima atualização apaga tudo; escreve
blocos do Tainacan à mão, e o editor mostra "Este bloco contém conteúdo inesperado";
ou cola CSS que briga com as opções de Personalizar. Este kit dá à IA o que ela
precisa para acertar de primeira:

- **as regras**: o que fazer, em que ordem, o que nunca fazer (`AGENTS.md`);
- **o funcionamento do tema** (`docs/`);
- **os dados exatos**: as 147 opções com valores aceitos, os ganchos, os tokens de
  CSS, o markup dos blocos do Tainacan (`reference/`);
- **um tema filho modelo** já validado no editor, com padrões de página inicial
  (`starter-child/`);
- **pedidos prontos** para cada IA (`prompts/`);
- **o contexto do site** (`site-context.json`, gerado na hora do download):
  coleções, metadados públicos, taxonomias, páginas, menus e opções já mudadas.
  Não leva usuários, e-mails nem itens.

## Como usar

1. No painel: **Aparência → Kit para IA → Baixar o kit (.zip)**.
2. Entregue o ZIP à IA:
   - **Claude** (claude.ai): crie um Projeto, envie o ZIP ao conhecimento do
     Projeto e cole o pedido de `prompts/claude.md`.
   - **ChatGPT**: crie um Projeto (ou um GPT), envie o ZIP e cole o pedido de
     `prompts/chatgpt.md`.
   - **Gemini**: crie um Gem com o pedido de `prompts/gemini.md` como instrução e
     os arquivos do kit como conhecimento.
   - **Agentes de código** (Claude Code, Codex, Gemini CLI, Cursor, Copilot):
     descompacte o kit numa pasta vazia e abra o agente nela. Ele lê
     `AGENTS.md`. Com WP-CLI no servidor, ele confere o próprio trabalho
     (`prompts/coding-agents.md`).
3. Descreva o site: público, tom, cores, coleções em destaque, páginas.
4. Instale o tema filho que a IA devolver (Aparência → Temas → Enviar tema),
   confira em **Aparência → Kit para IA → Conferir um tema filho**, pré-visualize
   e só então ative.

A mesma tela tem o **tema filho modelo** para baixar já com o nome do seu site,
e a lista dos blocos que uma IA pode escrever.

## Conteúdo

| Caminho | O que é |
|---|---|
| `AGENTS.md` | Instruções para qualquer IA (o ponto de partida) |
| `CLAUDE.md`, `GEMINI.md` | Apontam para `AGENTS.md` |
| `docs/01-como-o-tema-funciona.md` | O que o tema faz sozinho: desenho, opções, páginas do Tainacan |
| `docs/02-tema-filho.md` | O padrão do tema filho |
| `docs/03-opcoes-sem-codigo.md` | Mudar o site só com opções (Personalizar, WP-CLI, por coleção) |
| `docs/04-blocos.md` | Blocos que se escrevem à mão, os que não, Query Loop do Tainacan |
| `docs/05-receitas-de-design.md` | Páginas prontas e princípios de design, acessibilidade e desempenho |
| `docs/06-validacao.md` | Checagem antes de entregar e antes de ativar |
| `reference/options.json` | As opções do tema, com chave, padrão e valores aceitos |
| `reference/css-tokens.md` | As variáveis de CSS (`--tt2-*`) |
| `reference/hooks.md` | Ações e filtros do tema |
| `reference/tainacan-blocks.md` / `.json` | Os blocos do Tainacan: como o editor os salva |
| `starter-child/` | Tema filho modelo (com marcadores `{{…}}`) |
| `prompts/` | Pedidos prontos para Claude, ChatGPT, Gemini e agentes de código |
| `tools/check-editor.js` | Abre uma página no editor (navegador sem tela) e lista blocos inválidos |

## Linha de comando

```bash
wp tainacan-interface kit context                 # contexto do site (JSON)
wp tainacan-interface kit zip kit.zip              # o kit + contexto num ZIP
wp tainacan-interface kit child <pasta> --name="…" [--activate]
wp tainacan-interface kit check [<pasta>]          # confere um tema filho
wp tainacan-interface kit blocks <id|arquivo>      # confere markup de blocos
wp tainacan-interface kit reference [pasta]        # regera reference/options.json
```

## Privacidade

O `site-context.json` traz só o que já é público no site (coleções, metadados
públicos, taxonomias, páginas publicadas) e as opções do tema. Mesmo assim, leia-o
antes de enviá-lo a um serviço externo, e não acrescente senhas, chaves ou dados
de pessoas ao que entregar à IA.
