## Stack de IA do Tainacan

Este repositório usa a Stack de IA do Tainacan, instalada em `.agents/tainacan-stack/` (versão em `.agents/tainacan-stack/VERSION`). As regras abaixo valem para qualquer agente (Claude, Codex, Copilot, Cursor, Gemini, opencode) e para quem trabalha sem agente. O que é específico deste projeto fica na seção do projeto, fora deste bloco; em conflito, a seção do projeto vale para o projeto e este bloco vale para o resto.

### Como trabalhar

1. **Pensar antes de agir.** Declare as premissas. Se o pedido admite mais de uma leitura, apresente as leituras e pergunte; não escolha em silêncio. Leia o arquivo inteiro antes de editar.
2. **O mínimo que resolve.** Nada além do pedido, nenhuma abstração para uso único, nenhuma opção que ninguém pediu.
3. **Mudança cirúrgica.** Toque só no que o pedido exige e siga o estilo do código ao redor. Problema fora do escopo vira observação na resposta, não edição.
4. **Critério verificável.** Antes da primeira edição, escreva o plano como checklist `- [ ] passo → verificação: <comando ou observação>`. Na resposta final, repita o checklist marcando `[x]` só o que foi verificado, com o comando e o resultado; o que depende de teste manual fica `[ ]` com o motivo. "Parece funcionar" não conta.
5. **Subagente simples, modelo mais barato.** Busca, localização de arquivos, leitura em lote e conferência mecânica rodam num modelo menor (no Claude Code, `model: haiku` no subagente ou no parâmetro `model` da chamada; nas outras ferramentas, o modelo leve equivalente). Revisão de segurança e de correção, migração de dados e decisões de arquitetura ficam no modelo principal.
6. **Tarefa nova, contexto novo.** Ao trocar de tarefa, comece uma sessão limpa (`/clear` no Claude Code, conversa nova nas outras ferramentas) e retome pelo arquivo de contexto do repositório, não pelo histórico da tarefa anterior.

### Roteamento

Escolha a skill pela demanda (todas em `.agents/skills/`; no Claude Code aparecem como `tainacan-stack:<nome>`):

| Demanda | Skill |
|---|---|
| Implementar ou corrigir código em plugin ou no núcleo | `tnc-guardrails` |
| Criar plugin novo | `tnc-novo-plugin` |
| Tipo de metadado ou filtro, gancho de admin, busca facetada, página do item, importador, exportador, exposer, processo em segundo plano, bloco | `tnc-estender-tainacan` |
| Tela no admin do Tainacan, campo no formulário de item/coleção/metadado, drawer ou assistente ao lado do item | `tnc-ui-admin` |
| Tema do Tainacan: template, modo de visualização, recurso que liga e desliga, fork do tema oficial | `tnc-tema` |
| Passar no Plugin Check, publicar no WordPress.org, limpar achados do PHPCS | `tnc-wordpress-org` |
| Rota REST própria ou consumo da REST do Tainacan | `tnc-rest-api` |
| Lentidão, acervo grande, consultas, índice de busca | `tnc-desempenho` |
| Bug sem causa conhecida, comportamento estranho | `tnc-diagnostico` |
| Revisão de diff, PR, branch ou módulo | `tnc-revisao-tecnica` |
| Plugins que se afetam, CSS ou JS vazando, ganchos disputados | `tnc-convivencia` |
| Importação ou migração de acervo | `tnc-migracao-acervo` |
| Publicar versão | `tnc-release` |
| Contribuir com o núcleo (repositório tainacan/tainacan) | `tnc-contribuir-nucleo` |
| Problema novo que vale lembrar | `tnc-registrar-licao` |
| Rodar as verificações antes de entregar | `tnc-validacao` |

A skill principal conduz; `tnc-validacao` roda sempre que houver código alterado.

### Verificações obrigatórias

- Antes de declarar pronto: `php .agents/tainacan-stack/bin/tnc-check --changed`. Bloqueio impede a entrega; aviso se corrige ou se justifica na própria linha com `// tnc-check-ignore TNC-XNN: motivo` (sem motivo a supressão não vale).
- `php -l` em todo PHP alterado (o `tnc-check` já roda) e os testes do projeto quando existirem.
- Nunca use `--no-verify`, `git push --force` em branch compartilhada nem desligue o gancho para passar. Commits em Conventional Commits (`fix(area): ...`), em inglês. Nunca `push` em branch de repositório upstream sem confirmação explícita.
- Explicação de qualquer achado: `php .agents/tainacan-stack/bin/tnc-check --explain TNC-XNN`.

### Regras do WordPress que valem sempre

- Diante de aviso de lint ou segurança: **refatorar > usar a API canônica > suprimir** com justificativa técnica (`phpcs:ignore <sniff> -- por que a alternativa não serve`). "Is safe" não é justificativa. (TNC-B07, B05)
- Escape **na linha do echo**, no contexto certo (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `esc_html__`); `printf` com formato e argumentos escapados. Escape feito antes não conta. (TNC-S05)
- Entrada: `sanitize_*( wp_unslash( $_POST[...] ) )`; nonce e capability em todo handler que muta. (TNC-S02, S03)
- `gmdate()`/`wp_date()`, `wp_json_encode()`, `wp_remote_get()`, `wp_delete_file()`, `wp_rand()`, `WP_Filesystem` no lugar das funções nativas; `error_log()` só sob `WP_DEBUG`; biblioteca em `assets/vendor/`, nunca CDN. (TNC-W13, B04, W14)
- i18n: domínio único do plugin, `translators:` e `%1$s` em toda string com marcador, `wp_set_script_translations()` para JS. (TNC-W03, W15, R07)
- Revisão sempre em **duas camadas**: checklist dos padrões + `tnc-check` + `phpcs` e, no Claude Code, `/code-review` (`medium` uma vez por entrega; `high` em segurança, migração, transação ou release; `xhigh` só a pedido) para bugs de correção. Em conflito, valem estes padrões. Detalhe: `referencias/padroes-wordpress-org.md`.

### Regras do Tainacan que valem sempre

- Repositórios são singletons: `\Tainacan\Repositories\Items::get_instance()` ou `tainacan_items()`; nunca `new`. Entidade publicada e todo `Item_Metadata_Entity` precisam de `validate()` antes do `insert()`, senão há exceção; registre `get_errors()`, nunca engula. (TNC-T05)
- Opções de tipo de metadado estão em `_option_<chave>` e em `metadata_type_options`; `get_post_meta( $id, 'taxonomy_id' )` devolve vazio. Taxonomia do metadado: `_option_taxonomy_id`, slug `tnc_tax_<id>`. Valor do item: `meta_key` = ID numérico do metadado. (TNC-T04)
- Callback de `tainacan_register_admin_hook()` devolve string (`ob_start` + `return ob_get_clean()`), é registrado dentro da ação `tainacan-register-admin-hooks` e não leva `<script>`: o HTML entra por `v-html`. (TNC-T01, T02, T03)
- Busca facetada, depois do item: registre `tainacan_faceted_search_item_after` e `tainacan_faceted_search_collection_item_after`. (TNC-T06)
- REST do Tainacan: `taxquery` descarta `include_children` e usa `metadatum` no lugar de `field`; `metaquery` usa `metadatumtype`, não `type`. Em listagens use `fetch_only` e `fetch_only_meta`. (TNC-T07)
- Itens são `tnc_col_<id>_item` com `exclude_from_search`: `'post_type' => 'any'` não os encontra. Filtro genérico `tainacan-item-metadata-get-value-as-html` não roda para tipos com HTML próprio; use as variantes `--type-<tipo>`. (TNC-T09)
- Acervos têm dezenas de milhares de itens: nada de consulta sem limite, consulta dentro de laço nem `meta_query` com `LIKE` sem medir. (TNC-P01, P02, P03)
- Nunca altere `meta_value` em massa sem filtrar por `meta_key`: PHP serializado guarda o tamanho das strings e corrompe. Para troca de texto use `wp search-replace --dry-run`. Faça backup antes de SQL destrutivo. (TNC-P04)
- Um plugin não desfaz o que outro registrou (`remove_all_filters`, desregistro de assets alheios, `$wp_filter`), não usa CSS global e prefixa funções, classes, opções, handles e rotas. (TNC-C01, C02, W10)
- Rotas REST sempre com `permission_callback`; escrita nunca aberta sem justificativa; handlers AJAX e admin-post com nonce e capability; SQL com `$wpdb->prepare()`. (TNC-S01 a S08)

### Dados, segurança e ambientes

- Achado de segurança só é confirmado com o caminho do dado demonstrado, da entrada ao uso, citando arquivo e linha de cada passo. Sem isso é hipótese e se reporta como tal.
- Nunca grave senha, token ou chave no código, no histórico do git ou em mensagem. Encontrou um? Avise antes de qualquer outra ação.
- Dados pessoais (LGPD) não vão para log, para a resposta de API pública nem para o prompt de serviço externo.
- Em migração de acervo, o valor da origem chega intacto: nenhuma normalização silenciosa de caixa, espaço ou duplicado. Reconcilie as contagens origem × banco × índice separadamente.
- Teste em ambiente de testes com dados reais antes de produção. Mudança em produção só com pedido explícito.

### Conhecimento e lições

- Ordem de consulta: a seção do projeto neste arquivo; `.agents/tainacan-stack/licoes/` (uma lição por regra); `.agents/tainacan-stack/referencias/` (por tema; consulte sob demanda, não carregue tudo); o código do núcleo.
- Problema novo, com causa entendida e que pode se repetir: use `tnc-registrar-licao`. Se dá para detectar no código, a lição vira regra do `tnc-check`, e a mesma verificação roda nos outros plugins: `tnc-check --workspace=<pasta> --rules=<ID>`.
- Lição que só vale para este projeto vai para a seção do projeto deste arquivo, na mesma entrega da correção.

### Escrita

Português brasileiro claro e direto, voz ativa, frases completas, sem travessão nem floreio. Código, comandos, identificadores e nomes de tecnologias ficam literais. Resposta final: o que mudou, o checklist verificado, o que ficou pendente e por quê.
