# Padrões para passar no Plugin Check do WordPress.org (código PAD)

Decisões de desenvolvimento adotadas pela equipe depois de levar plugins reais de centenas de achados do Plugin Check a zero. Valem para todo plugin WordPress; as seções Tainacan estão em `ganchos-e-extensao.md` e `ui-admin-e-drawer.md`. O `tnc-check` automatiza o que dá (coluna **Detecção**); o resto é critério de revisão (`papeis/wordpress-org.md`).

Ferramenta oficial: plugin **Plugin Check** (`wp plugin install plugin-check --activate`; `wp plugin check <slug> --exclude-directories=.agents,.claude,.codex,.cursor,node_modules,vendor,tests`) ou a ação `wordpress/plugin-check-action` no CI. Padrão PHPCS equivalente: `templates/phpcs.xml.dist`.

## PAD-00 · Hierarquia: refatorar > API canônica > suprimir

Ao bater num aviso de lint ou de segurança, nesta ordem:

1. **Refatorar** para que o aviso desapareça: quase sempre existe uma API do WordPress (ou repositório do Tainacan) que resolve sem `$wpdb` direto, sem `unserialize`, sem `json_decode` cego, sem heredoc.
2. **Substituir pela função canônica**: `wp_remote_get` por `file_get_contents(URL)`, `wp_json_encode` por `json_encode`, `wp_delete_file` por `unlink`, `WP_Query` por SQL em tabela do WordPress, `wp_rand` por `rand` (TNC-W13).
3. **Suprimir é o último recurso**, com justificativa técnica que responda "por que a alternativa não serve aqui?" em uma frase (TNC-B07). "Is safe", "false positive", "performance" sem medição não valem.

Motivo: suprimir é admitir que não houve esforço de eliminar o risco. Um revisor externo já criticou exatamente isso.

## PAD-01 · Persistência: APIs de alto nível primeiro, `$wpdb` por exceção

Ordem: `wp_insert_post`, `get_post_meta`, `update_post_meta`, `get_posts`, `WP_Query`, `wp_insert_term` → repositórios do Tainacan → `$wpdb` só para tabela própria do plugin, agregações (COUNT/GROUP BY/JOIN) ou lotes.

**Pattern A (tabela própria):** interpolação só de nome de tabela controlado (`$wpdb->prefix`), input via `prepare()`, supressão consolidada acima da linha:

```php
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Plugin's own table; $wpdb->prefix is trusted; user input via %d placeholders.
$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
```

**Pattern B (ORDER BY, IN variável, nome vindo de input):** validar contra allowlist **antes** de interpolar; IN com `array_fill( 0, count( $ids ), '%d' )`, nunca `implode( ',', $ids )`.

SQL de várias linhas: `phpcs:disable` antes e `phpcs:enable` depois (o `phpcs:ignore` só cobre a primeira linha). Detecção: TNC-S01, W11, P04, B07.

## PAD-02 · Datas

Banco, cálculo, comparação: `gmdate( 'Y-m-d', strtotime( '+X days' ) )`. Exibição: `wp_date( get_option( 'date_format' ), $ts )` ou `date_i18n()`. Nunca `date()` (TNC-W13).

## PAD-03 · Entrada

Toda leitura de `$_POST`/`$_GET`/`$_REQUEST`/`$_SERVER`/`$_COOKIE`:

```php
$value = isset( $_POST['x'] ) ? sanitize_text_field( wp_unslash( $_POST['x'] ) ) : '';
```

`wp_unslash()` antes de `sanitize_*()`, sempre (TNC-S02). Nonce em todo handler que muta estado (TNC-S03):
- AJAX com despachante central: `check_ajax_referer()` no topo; nos métodos privados, `// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified at handler entrypoint via check_ajax_referer().`
- Formulário admin: `wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'name' )` + `wp_nonce_field( 'name' )`.
- REST: cookie auth + `X-WP-Nonce`; leituras extras levam `-- REST endpoint; nonce handled by wp_rest cookie auth.`
- Filtro `$_GET` só leitura em listagem admin: `-- Read-only admin filter; no state mutation.`

REST: `args` com `sanitize_callback`/`validate_callback` no registro da rota, não dentro do handler. `permission_callback` nunca `__return_true`: `current_user_can()` com a cap do recurso ou `$entity->can_read()`/`can_edit()`; leitura pública confere `publish` (TNC-S04).

## PAD-04 · Saída: escape no ponto de saída (regra absoluta)

O Plugin Check olha só a linha do `echo`. Escape acontece dentro do `echo`/`printf` final, não antes (TNC-S05).

| Contexto | Função |
|---|---|
| Texto | `esc_html` |
| Atributo | `esc_attr` |
| URL | `esc_url` |
| String em JS | `esc_js` ou `wp_json_encode` |
| HTML rico | `wp_kses_post` |
| Exceção | `esc_html( $e->getMessage() )` |

`printf( esc_html__( 'Olá, %s!', 'dominio' ), esc_html( $name ) )`: `%s` substitui mas não escapa; `%d` aceita `(int)`. Template incluído com `ob_start()`: escape tudo lá dentro, como entrada não confiável.

## PAD-05 · i18n

Domínio de texto único = slug do plugin, em toda string (TNC-W03, R04); nunca reusar `tainacan` em plugin de extensão. `load_plugin_textdomain()` só com `languages/` próprio. `/* translators: %s = ... */` específico antes de toda string com marcador; vários marcadores numerados `%1$s`, `%2$s` (TNC-W15). Sem concatenar strings traduzíveis. JS: `wp_set_script_translations( $handle, 'dominio' )` depois do enqueue + `wp.i18n.__()` (TNC-R07).

## PAD-06 · Depuração e funções proibidas

`error_log()` só dentro de `if ( defined( 'WP_DEBUG' ) && WP_DEBUG )` + `// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Guarded by WP_DEBUG; surfaces only in dev.` (TNC-B04). Proibidos sem refatorar: heredoc com HTML (template + `ob_start`), `fopen`/`fwrite` (`WP_Filesystem`), `unlink` (`wp_delete_file`), `file_get_contents(URL)` (`wp_remote_get`), `json_encode` (`wp_json_encode`), `rand` (`wp_rand`), `set_time_limit` sem justificativa (TNC-W13), `unserialize()` sem `['allowed_classes' => false]` (TNC-S06; prefira `json_decode` + validação de esquema com `is_array()` e chaves esperadas). Zero CDN em `wp_enqueue_*`: bibliotecas em `assets/vendor/` (TNC-W14).

## PAD-07 · Concorrência em operação multi-tabela

Empréstimo, pedido, saldo: `START TRANSACTION`/`COMMIT`/`ROLLBACK` com try/catch; `SELECT ... FOR UPDATE` na linha de estado; contadores com UPDATE atômico (`SET x = GREATEST(0, x + %d)`), nunca ler-modificar-gravar; sequência por chave com `GET_LOCK` nomeado (timeout 10 s). Trava de processo com `add_option()` (falha se existe) ou `GET_LOCK`, não transient lido-e-gravado. Detecção: manual (`papeis/correcao.md`).

## PAD-08 · Assets

Constante `MEUPLUGIN_VERSION` como `$ver` em todo `wp_enqueue_*`; bibliotecas de terceiros em `assets/vendor/<lib>/` com versão anotada; bundle de webpack/esbuild commitado em `assets/js/` (deploy sem build); enqueue condicional (hook de página no admin, `is_singular()` etc. no front). Detecção: TNC-W14; o resto manual.

## PAD-09 · Compatibilidade declarada

Cabeçalho: `Requires at least: 6.5`, `Requires PHP: 7.4`, `Tested up to:` acompanhando o Tainacan, `Requires Plugins: tainacan` em extensão (TNC-R05). `readme.txt` com `Stable tag` igual à versão (TNC-R01, R02). Changelog com a versão (TNC-R03).

## PAD-10 · Lint e revisão em duas camadas

`phpcs.xml.dist` estrito (`WordPress-Core`, `Docs`, `Extra`, `WordPress.Security`, `PluginCheck`, `PHPCompatibilityWP`; TNC-R08) e `vendor/bin/phpcs` antes de todo PR. Toda revisão usa **duas camadas juntas**: (1) este checklist + `tnc-check` + `php -l` + `phpcs`; (2) revisão de correção (lógica, casos-limite, regressão) com verificação de cada achado, no Claude Code pela skill `/code-review` (`medium` uma vez por entrega; `high` em segurança, migração de dados, transação ou release; `xhigh` só a pedido) e nas outras ferramentas pelo papel `papeis/correcao.md`. Em conflito, estes padrões prevalecem (ex.: `/code-review` sugerir supressão onde há API canônica).

## PAD-11 · Fluxo de versão

`php -l` em todo arquivo alterado antes do commit; Conventional Commits em inglês (`fix(area): ...`; gancho `commit-msg`); bump semver no cabeçalho, na constante, no `readme.txt` e no `package.json` (TNC-R01); git-flow (`develop` → `release/X.Y.Z` → `master` com tag anotada, merge de volta); ZIP de release pelo `git archive` sem `tests/`, `node_modules/`, `vendor` de dev, fontes Vue, `.agents/`. Nunca `push` em `main`/`master` de repositório upstream (tainacan/tainacan) sem confirmação explícita: trabalhar no fork e abrir PR.
