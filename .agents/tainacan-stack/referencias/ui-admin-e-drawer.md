# UI no admin do Tainacan: telas, campos de formulário e drawer (código UIA)

Como um plugin de extensão se integra ao admin do Tainacan (SPA em Vue 3 + Buefy sobre Bulma) sem criar instância Vue própria nem mexer no DOM que o Vue controla. Padrão extraído de plugins de catalogação assistida, crowdsourcing de metadados e indicadores, e confirmado no núcleo 1.1.0 a 1.4.1. Complementa `ganchos-e-extensao.md` (assinaturas dos ganchos) e `front-end-e-vue.md` (CSS, z-index, eventos).

## Quando se aplica

O plugin adiciona tela própria no admin do Tainacan, campo nos formulários de item, coleção ou metadado, ou um painel de ação (assistente, sugestões, validação) ao lado do formulário do item. Plugin só de front público usa apenas as seções REST e tokens.

## UIA-01 · Arquitetura em camadas

```
<slug>.php              bootstrap magro: constantes, guarda de autoload, Plugin::get_instance()
includes/Plugin.php     singleton; liga os serviços no `init`, só se class_exists('\Tainacan\Repositories\Items')
includes/Admin/         página via \Tainacan\Pages + template settings-page.php
includes/Hooks/         campos nos formulários (item/coleção/metadado) + filtros do núcleo
includes/REST/API.php   namespace próprio <slug>/v1: args validados, caps, cache por transient
src/js, src/css → build/  compilado por @wordpress/scripts; bundles commitados
```

Bootstrap sem lógica; `Plugin` liga tudo no `init`, nunca no construtor; sem Tainacan, `admin_notice` e nada de fatal. Opções com `wp_parse_args()` sobre padrões. **Detecção:** manual.

## UIA-02 · Tela admin pela Pages API

```php
class Admin_Page extends \Tainacan\Pages {
    use \Tainacan\Traits\Singleton_Instance;
    protected function get_page_slug(): string { return 'meu_plugin'; }
    public function add_admin_menu() {
        $suffix = add_submenu_page( $this->tainacan_other_links_slug, __( 'Meu Plugin', 'meu-plugin' ),
            '<span class="icon">…svg…</span><span class="menu-text">' . esc_html__( 'Meu Plugin', 'meu-plugin' ) . '</span>',
            'manage_options', $this->get_page_slug(), [ $this, 'render_page' ], 10 );
        add_action( 'load-' . $suffix, [ $this, 'load_page' ] );   // TNC-T08
    }
    public function admin_enqueue_css() { /* wp_enqueue_style lendo build/*.asset.php */ }
    public function admin_enqueue_js()  { /* wp_enqueue_script + wp_add_inline_script com wp_json_encode (TNC-W12) */ }
    public function render_page_content() { $options = get_option( 'meu_plugin_options', [] ); include __DIR__ . '/settings-page.php'; }
}
```

Layout do template: `.wrap.tainacan-page-container-content`, `.tainacan-fixed-subheader` com título, grade `1fr / 360px` (conteúdo + barra lateral), cartões colapsáveis, `info-box` na lateral, barra de ações fixa com Salvar. O template escapa tudo (TNC-S05). **Detecção:** TNC-T08; layout manual.

## UIA-03 · Campo de formulário: o trio de persistência

```php
add_action( 'tainacan-register-admin-hooks', function () {
    tainacan_register_admin_hook( 'collection', [ $this, 'render_form' ], 'end-right' );   // devolve string (TNC-T01), sem <script> (TNC-T02)
} );
add_action( 'tainacan-insert-tainacan-collection', [ $this, 'save_data' ] );               // 1) gravar, com $entity->can_edit()
add_filter( 'tainacan-api-response-collection-meta', [ $this, 'add_meta_to_response' ], 10, 2 ); // 2) expor para pré-preencher na edição
add_action( 'init', [ $this, 'register_meta' ] );                                           // 3) register_post_meta( ..., [ 'show_in_rest' => true ] )
```

Posições usadas: item `begin-left`, coleção `end-right`, metadado `end-left`. `save_data` lê `tainacan_get_api_postdata()`, confere `can_edit()`, `''` → `delete_post_meta`, valor → `update_post_meta`. **Checkbox grava array** (`['1']`), o contrato dos ganchos de formulário; dado legado escalar se normaliza em `rest_post_dispatch`. Marcação com as classes do Buefy/Bulma do próprio Tainacan (`.field`, `.label`, `.control`, `.input`, `.b-checkbox.checkbox`) para herdar o visual. O JS do campo é arquivo enfileirado que escuta `tainacan-<entidade>-hook-reload` no `document`. **Detecção:** TNC-T01, T02, T03; o trio é manual.

## UIA-04 · Front: jQuery + `@wordpress/api-fetch` + `@wordpress/hooks`, nunca Vue próprio

**Reagir a rota e item** (não observar o `<body>`):

```js
import { addAction } from '@wordpress/hooks';
addAction( 'tainacan_navigation_path_updated', 'meu_plugin', ( payload ) => {
    const path = payload?.childEntity?.defaultLink || payload?.currentRoute?.path || '';
    const m = path.match( /collections\/(\d+)\/items\/(\d+)\/edit/ );   // entrou/saiu da edição do item
} );
addAction( 'tainacan_item_edition_item_loaded', 'meu_plugin', ( collection, item ) => { /* ids prontos */ } );
```

**Escrever metadado** pela REST do núcleo e pedir ao Vue que recarregue; nunca localizar o `<input>` Buefy e simular digitação:

```js
await apiFetch( { url: `${cfg.tainacanApiUrl}item/${itemId}/metadata/${metadatumId}`, method: 'PUT', data: { values } } );
if ( cfg.features.supportsMetadataReloadEvent ) {   // Tainacan >= 1.1.0
    window.dispatchEvent( new CustomEvent( 'TainacanReloadItemMetadataForm', { detail: { itemId, metadatumId } } ) ); // sem metadatumId recarrega tudo
}
```

Preencher vários campos: N PUTs sem recarregar e **um** evento no fim. Nonce: `apiFetch.use( apiFetch.createNonceMiddleware( cfg.restNonce ) )` registrado uma vez. **Detecção:** manual (procure `new Vue`, `createApp`, `MutationObserver` em `document.body`, `.val(` em input do Tainacan).

## UIA-05 · Drawer, não modal

Painel `position: fixed; right: -520px` → `.open { right: 0 }` anexado ao `<body>`; sobreposição **não bloqueante** (o usuário continua editando); indicador flutuante para reabrir; abas; cartões valor + evidência; toasts. Erros e editor como seções inline ou `<details>`, não modais. Z-index acima do lightbox do Tainacan, abaixo da barra do WordPress.

Robustez obrigatória: delegação de eventos em `$( document ).on( 'click', '.classe', ... )` (sobrevive à reinjeção pela SPA); re-cache de referências quando a UI é recriada; `escapeHtml` (via `div.textContent`) em todo conteúdo dinâmico (TNC-W09); enqueue condicional (`$screen->base === 'admin_page_tainacan_admin'` ou `hook_suffix`); se observar DOM, escopo estreito + debounce + teardown. **Detecção:** TNC-W09; o resto manual.

## UIA-06 · Tokens CSS espelhando o tema

```css
:root {
    --meu-primary: var(--tainacan-blue5, #1f2f56);
    --meu-accent:  var(--tainacan-turquoise1, #b5e0e3);
    --meu-bg:      var(--tainacan-gray1, #e5f2f2);
    --meu-border:  var(--tainacan-input-border-color, #d0e8e8);
    --meu-success: var(--tainacan-success, #4caf50);
}
```

Reaproveitar `--tainacan-button-border-radius`, `--tainacan-input-border-radius`, `--wp-admin--admin-bar--height`. Todo seletor sob a classe raiz do plugin (TNC-C02).

## UIA-07 · REST próprio e persistência

Namespace `<slug>/v1`; `args` com `validate_callback`/`sanitize_callback` no registro; `permission_callback` com cap real ou `$entity->can_*()`, nunca `__return_true` (TNC-S04); cache por transient prefixado `<slug>_`; operações caras em fases (`extract` → `analyze`). Persistência na ordem APIs do WordPress → repositórios/entidades do Tainacan → `$wpdb` por exceção com `esc_like` + `prepare` + supressão justificada (PAD-01, TNC-B07).

## UIA-08 · Ferramental

`@wordpress/scripts` (webpack multi-entry), bundles em `build/` commitados, enqueue lendo `build/*.asset.php`; `phpcs.xml.dist` estrito (TNC-R08); `.distignore` sem `src/`, `node_modules`, `tests`; `declare( strict_types=1 )` em todo PHP; textdomain = slug.

## UIA-09 · Playbook para refatorar plugin existente

Convergência incremental, camada por camada, preservando o comportamento:

0. **Diagnóstico**: como registra tela (menu puro ou Pages), como injeta UI no item (heredoc, MutationObserver, Vue próprio), como grava metadado (input, admin-ajax, REST), onde há `$wpdb`, `json_decode` cego, `unserialize`, heredoc, CDN; estado do `phpcs.xml.dist`. Saída: lista de lacunas priorizada contra o checklist abaixo.
1. **Base segura**: `strict_types`, ruleset estrito, singleton com `init`, funções canônicas (TNC-W13).
2. **Tela admin**: Pages API + template com cartões e tokens.
3. **Campos**: `tainacan_register_admin_hook` + trio; `can_edit()`; `register_post_meta`.
4. **Front**: `addAction` no lugar de observar DOM; PUT + `TainacanReloadItemMetadataForm`; drawer; delegação, re-cache, `escapeHtml`.
5. **REST e cache**: namespace próprio, args, caps, transients.
6. **Verificação**: `tnc-validacao`, `phpcs`, `npm run build`; teste na SPA (abrir item, rodar ação, ver o Vue recarregar); bump de versão e changelog.

## Checklist de conformidade

- [ ] Bootstrap magro; `Plugin` singleton com wiring no `init` e guarda do Tainacan.
- [ ] `strict_types` em todo PHP.
- [ ] Tela via `\Tainacan\Pages` + `Singleton_Instance` + `load_page`.
- [ ] Campos via `tainacan_register_admin_hook` + trio (gravar / expor / `register_post_meta`); `can_edit()`; checkbox como array.
- [ ] Drawer no `<body>`, sobreposição não bloqueante, indicador, abas, cartões valor + evidência.
- [ ] `addAction` para rota e item; sem manipular input Buefy; PUT na REST do núcleo + evento de reload (com gate de versão).
- [ ] Delegação de eventos, re-cache, `escapeHtml`, enqueue condicional.
- [ ] Bloco `:root` de tokens.
- [ ] REST `<slug>/v1` com args, caps reais, cache por transient.
- [ ] `phpcs.xml.dist` estrito; `$wpdb` só com `esc_like` + `prepare` + supressão justificada.
