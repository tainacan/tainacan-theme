---
name: tnc-wordpress-org
description: Prepara um plugin Tainacan/WordPress para passar no Plugin Check do WordPress.org no primeiro envio e na revisão humana do diretório: roda o Plugin Check (wp plugin check) ou o PHPCS com o ruleset estrito, trata cada achado pela hierarquia refatorar > API canônica > suprimir com justificativa, e confere readme, cabeçalhos, licenças, serviços externos e pacote. Use para "passa no Plugin Check?", "preparar para o WordPress.org", "limpar os achados do phpcs", "zerar o Plugin Check", ou antes do primeiro envio ao diretório.
---

# tnc-wordpress-org

Referência das decisões: `.agents/tainacan-stack/referencias/padroes-wordpress-org.md` (PAD-00 a PAD-11). Papel de revisão: `.agents/tainacan-stack/papeis/wordpress-org.md` (subagente `tnc-revisor-wordpress-org` no Claude Code).

## 1. Rodar a ferramenta oficial

Em ordem de preferência, guardando a saída:

1. **Plugin Check num WordPress local** (o mesmo que o diretório usa):
   ```
   wp plugin install plugin-check --activate
   wp plugin check <slug> --exclude-directories=.agents,.claude,.claude-plugin,.codex,.cursor,node_modules,vendor,tests --format=table
   ```
   Alternativas: a tela Ferramentas > Plugin Check no admin; a ação `wordpress/plugin-check-action` no CI (modelo comentado em `.github/workflows/tnc-check.yml`).
2. **PHPCS com o ruleset estrito** (`phpcs.xml.dist` da stack; o instalador cria um se não houver): `composer install && vendor/bin/phpcs`. Cobre o mesmo que o Plugin Check para PHP, sem precisar do WordPress.
3. **Sem nenhum dos dois**: `php .agents/tainacan-stack/bin/tnc-check <pasta> --rules=TNC-S05,TNC-S02,TNC-W03,TNC-W13,TNC-W14,TNC-W15,TNC-B04,TNC-B06,TNC-B07 --format=md` e `tnc-check --release`. É triagem, não o veredito do diretório; diga isso no relato.

## 2. Tratar cada achado pela hierarquia (PAD-00)

Para cada código, nesta ordem, e só avance quando o passo anterior não serve:

| Achado | 1. Refatorar | 2. Função canônica | 3. Suprimir (último recurso) |
|---|---|---|---|
| `EscapeOutput` | escape na linha do `echo` (TNC-S05) | `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `esc_html__` | só saída não HTML (CSV, JSON com header), com a razão |
| `ValidatedSanitizedInput` | ler uma vez no topo do handler | `sanitize_*( wp_unslash( ... ) )`, `absint` | nunca |
| `NonceVerification` | nonce no formulário/AJAX | `check_ajax_referer`, `wp_verify_nonce` | `-- Nonce verified at handler entrypoint via check_ajax_referer()` em método privado; `-- Read-only admin filter; no state mutation.` |
| `DirectDatabaseQuery`, `PreparedSQL` | `WP_Query`, `get_posts`, repositório do Tainacan | `$wpdb->prepare()`, `array_fill` para IN | tabela própria ou agregação, com a razão (Pattern A/B) |
| `AlternativeFunctions`, `RestrictedFunctions.date` | | `wp_json_encode`, `wp_remote_get`, `WP_Filesystem`, `wp_delete_file`, `wp_rand`, `gmdate`/`wp_date` (TNC-W13) | fluxo grande ou binário, com a razão |
| `DevelopmentFunctions.error_log` | remover | `if ( WP_DEBUG )` em volta (TNC-B04) | `-- Guarded by WP_DEBUG; surfaces only in dev.` |
| `Offloading` (CDN) | copiar para `assets/vendor/` (TNC-W14) | `plugins_url()` | só serviço que não existe local (reCAPTCHA), declarado no readme |
| `I18n.*` | domínio único, `translators:`, `%1$s` (TNC-W03, W15) | `esc_html__`, `wp_set_script_translations` (TNC-R07) | nunca |
| `DiscouragedPHPFunctions` (`unserialize`, `base64`, `ini_set`) | `json_decode` + validação de esquema | `unserialize( $x, [ 'allowed_classes' => false ] )` | com a razão |

Toda supressão com o código do sniff e ` -- razão técnica` (TNC-B07). SQL de várias linhas: `phpcs:disable`/`enable`.

## 3. O que a ferramenta não vê e a revisão humana cobra

- `readme.txt` completo: `Stable tag` = versão, `Requires at least`, `Requires PHP`, `Tested up to`, `License`; seção **External services** para toda chamada a serviço de terceiros (o que é enviado, quando, link para termos e privacidade).
- Cabeçalho: `Requires Plugins: tainacan` em extensão; nome e slug sem marca registrada de terceiros.
- `uninstall.php` (ou `register_uninstall_hook`) que remove opções, tabelas, transients e eventos de cron.
- Bibliotecas de terceiros com licença compatível com GPL e versão anotada; nada minificado sem a fonte no pacote ou indicada no readme.
- Pacote sem arquivos de desenvolvimento: `.agents/`, `tests/`, `node_modules/`, mapas de fonte, fontes Vue não compiladas, `vendor/` de dev. `.distignore` e `.gitattributes export-ignore` cuidam disso (o instalador já marca as pastas da stack).
- Nada de atualização por servidor próprio nem de carregamento de código remoto.

## 4. Loop até zerar

1. Rode a ferramenta; agrupe por código e por arquivo.
2. Trate um arquivo por vez, começando pelos de segurança (escape, SQL, nonce), seguindo `tnc-guardrails` em cada mudança.
3. Rode de novo. Repita. Erro zero é obrigatório; aviso zero é a meta, e cada aviso que sobrar leva a razão na linha.
4. `tnc-validacao`, `tnc-check --release` e, se mudou versão, `tnc-release`.

## Relato

Ferramenta usada (e versão), contagem antes × depois por código, o que foi refatorado, o que ganhou função canônica e o que foi suprimido com a razão, pendências para a revisão humana (readme, serviços externos, licenças).
