# 6. Validação

Três momentos: a IA confere antes de entregar; quem instala confere antes de
ativar; e todos olham o site depois.

## 1. A IA, antes de entregar

### Sem acesso ao site (assistente de conversa)

Releia cada arquivo e responda, na própria entrega, item por item:

| Item | Como conferir |
|---|---|
| Cabeçalho do `style.css` | `Theme Name`, `Template: <pasta do pai>`, `Text Domain: <slug>`, `Version` |
| Marcadores | Nenhum `{{` sobrando (exceto os que você deixou de propósito e listou) |
| PHP | Toda função com o prefixo do filho; `<?php` no início; parênteses e chaves fechados; nenhum `?>` no fim de `functions.php` |
| Saída | Todo texto com `esc_html_e`/`esc_html__`, todo endereço com `esc_url`, todo atributo com `esc_attr` |
| Text domain | O mesmo slug em todas as chamadas de tradução |
| CSS | Só tokens `var(--tt2-*)` ou cores da paleta; seletores restritos às classes do filho |
| `theme.json` | JSON válido, `"version": 2`, sem `settings.color.palette` |
| Padrões | `Title`, `Slug: <slug>/…`, `Categories`; só blocos dos grupos 1 a 4 de `04-blocos.md` |
| Query Loops | `postType` e `namespace` de coleções que existem (`site-context.json`); `queryId` únicos |
| Blocos do editor | Nenhum dos 10 blocos "montados pelo editor" escrito com HTML; se usados, avisados |
| Celular | Nada com largura fixa em px acima de 360; colunas empilham; nada `alignwide` dentro de grupos |
| Acessibilidade | Um `h1`; títulos em ordem; contraste AA das cores escolhidas (calcule e informe) |

### Com acesso ao site (agente com terminal)

```bash
php -l functions.php && for f in patterns/*.php; do php -l "$f"; done
wp tainacan-interface kit check <slug>          # 0 erros; leia cada aviso
wp tainacan-interface kit blocks <id-da-página> # para cada página montada
wp theme list --status=inactive                  # o filho aparece, sem "broken"
```

Se houver Node e Puppeteer (`npm install puppeteer`):

```bash
WP_USER=editor WP_PASS='…' node tools/check-editor.js https://site.exemplo 123 456 [--save]
```

O script entra no site, abre cada página no editor e lista os blocos inválidos ou
desconhecidos, os avisos e se a página precisa ser salva. Com `--save`, salva a
página (útil depois que o tema refez blocos do Tainacan). Use um site de teste ou
um usuário com papel de Editor; a senha vai só por variável de ambiente.

Antes de ativar num site em produção, peça confirmação.

## 2. Quem instala, antes de ativar

1. Aparência → Temas → Enviar tema.
2. **Aparência → Kit para IA → Conferir um tema filho**: escolha o filho. Erros
   precisam ser corrigidos antes de ativar; avisos pedem leitura.
3. **Pré-visualizar em Personalizar** (link na mesma tela): navegue pela página
   inicial, uma coleção, um item e a busca.
4. Ative. As opções do pai são copiadas para o filho; o aviso do painel permite
   desfazer.

## 3. Todos, depois

- Página inicial, uma lista de itens, um item, a busca: sem erro, sem bloco vazio.
- Em 1366 px e em 390 px (no navegador: Ferramentas do desenvolvedor → modo
  celular): nada cortado, nada vazando para o lado.
- Abrir cada página nova no editor: nenhum "Este bloco contém conteúdo
  inesperado". Se o tema avisar que refez blocos do Tainacan, salve a página.
- Trocar a cor principal em Personalizar e ver se o site muda junto (prova de que
  o CSS usa os tokens).
- Teclado: Tab percorre a busca, os cards e os botões com foco visível.

## Mensagens da conferência e o que fazer

| A conferência diz | Causa | Correção |
|---|---|---|
| A linha "Template:" precisa ser … | Nome de pasta do pai errado | Use `versions.parent_theme` do contexto |
| Carrega a folha de estilo do Tainacan Interface | `wp_enqueue_style` do pai no filho | Apague: o pai já carrega os dois, na ordem certa |
| Carrega arquivo de servidor de fora (CDN) | Fonte, script ou imagem de fora | Use as fontes do tema e a biblioteca de mídia |
| Lê a requisição diretamente | `$_GET`/`$_POST`/`$_REQUEST` no código | Use `tainacan_interface_query_arg( 'chave' )` e as outras funções de `inc/request.php` do pai |
| Cópias de templates do Tainacan Interface | Arquivo com o nome de um template do pai | Troque por gancho ou opção; se não houver, justifique no topo do arquivo |
| Dê ao tema filho um "Text Domain" próprio | Vazio, `tainacan-interface` ou `tainacan` | O slug do filho |
| `theme.json` não é JSON válido / precisa de "version" | Sintaxe ou versão | Corrija; use `"version": 2` |
| Precisa das linhas "Title:" e "Slug:" | Cabeçalho do padrão incompleto | Acrescente |
| Comece o Slug com `<slug>/` | Padrão com prefixo de outro tema | Troque o prefixo |
| O bloco … não existe neste site | Bloco de plugin ausente ou nome errado | Troque pelo bloco certo |
| … não tem o HTML montado pelo editor | Bloco do grupo 5 escrito à mão | Abra a página no editor e salve, ou troque pelo equivalente |
| A consulta lista …, que não existe neste site | Query Loop de coleção inexistente | Use o `post_type` do contexto |
| HTML fora de bloco | Texto ou tags soltos entre os blocos | Ponha dentro de um bloco (vira bloco Clássico no editor) |

A conferência só informa; não impede a ativação. Corrija todos os erros antes de
ativar.
