# Pedido para agentes de código

Claude Code, OpenAI Codex, Gemini CLI, Cursor, GitHub Copilot e outros agentes que
trabalham numa pasta com terminal.

**Como usar**

1. Descompacte o kit numa pasta vazia e abra o agente nela. Ele lê `AGENTS.md`
   sozinho (Claude Code lê `CLAUDE.md`, Gemini CLI lê `GEMINI.md`, e os dois
   apontam para `AGENTS.md`).
2. Se o agente tiver acesso ao servidor do site (SSH com WP-CLI, ou um ambiente
   local com cópia do site), diga como chamar o WP-CLI.
3. Cole o pedido abaixo e descreva o site.

```text
Crie o tema filho de um site feito com o tema WordPress Tainacan Interface e o plugin Tainacan, seguindo o AGENTS.md desta pasta (Kit para IA do tema): leia os arquivos na ordem que ele indica, siga a ordem das decisões e as regras, e use site-context.json como a verdade sobre o site.

Se houver WP-CLI no site (eu digo como chamar):
- comece com `wp tainacan-interface kit context` (contexto atualizado) e crie o filho com `wp tainacan-interface kit child <pasta> --name="<nome>"`, em vez de copiar à mão;
- depois de cada mudança: `php -l` nos arquivos PHP, `wp tainacan-interface kit check <pasta>` e, para cada página montada, `wp tainacan-interface kit blocks <id>`;
- não ative o tema nem mude opções num site em produção sem me perguntar; num site de teste, pode;
- se houver Node com Puppeteer, valide as páginas no editor com tools/check-editor.js.

Sem WP-CLI: monte o filho em ./<pasta> a partir de starter-child/, trocando todos os marcadores {{...}} e o prefixo starter_child_ das funções, rode `php -l` se houver PHP, confira theme.json com um parser de JSON e gere <pasta>.zip.

Ao terminar, mostre: a árvore de arquivos, as páginas (markup de blocos ou padrões usados), as opções a ajustar (chave e valor), o que criar no painel, os passos de instalação, a saída das checagens e o que não foi possível verificar.
```
