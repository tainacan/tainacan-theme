---
name: tnc-ui-admin
description: Cria ou refatora a interface de um plugin dentro do admin do Tainacan sem instância Vue própria: tela de configurações pela Pages API, campos nos formulários de item, coleção e metadado com o trio de persistência, painel lateral (drawer) com sugestões ou validações, escrita de metadado pela REST do núcleo com recarga do formulário Vue, tokens CSS do tema. Use para "tela de admin", "campo no formulário do item/coleção/metadado", "assistente ao lado do item", "drawer", "painel de sugestões", "integrar com a SPA do Tainacan", ou ao refatorar plugin que observa o DOM ou manipula inputs do Tainacan.
---

# tnc-ui-admin

Referência completa, com código e checklist: `.agents/tainacan-stack/referencias/ui-admin-e-drawer.md` (UIA-01 a UIA-09). Assinaturas dos ganchos: `referencias/ganchos-e-extensao.md`. Confira sempre no núcleo da versão que o projeto suporta.

## Decidir a camada

| Pedido | Camada | Seção |
|---|---|---|
| Tela de configurações ou painel próprio | `\Tainacan\Pages` + `Singleton_Instance`, submenu sob `tainacan_other_links_slug`, `load_page` | UIA-02 |
| Campo extra no formulário de item, coleção, metadado, taxonomia, termo, filtro | `tainacan_register_admin_hook()` + trio (gravar com `can_edit()` / expor em `tainacan-api-response-<entidade>-meta` / `register_post_meta` com `show_in_rest`) | UIA-03 |
| Ação ao lado do item (sugerir, validar, preencher) | drawer anexado ao `<body>`, reage a `tainacan_navigation_path_updated` e `tainacan_item_edition_item_loaded` | UIA-04, UIA-05 |
| Preencher metadado do item a partir do plugin | `PUT tainacan/v2/item/{id}/metadata/{mid}` + `TainacanReloadItemMetadataForm` (Tainacan >= 1.1.0, com gate) | UIA-04 |
| Dados do PHP para o JS | `wp_add_inline_script` com `wp_json_encode` (TNC-W12), nonce `wp_rest`, `wp_set_script_translations` | UIA-02, PAD-05 |

## Regras que não se negociam

- **Nenhuma instância Vue própria** (`new Vue`, `createApp`) no admin do Tainacan; componente extra só por `tainacan-register-vuejs-component` (`referencias/front-end-e-vue.md`). O resto é jQuery + `@wordpress/api-fetch` + `@wordpress/hooks`.
- **Nunca localizar e mutar input Buefy** (`.val()`, `dispatchEvent('input')` em campo do Tainacan): grave pela REST e peça recarga.
- **Nunca observar `document.body`** com `MutationObserver` para descobrir a rota: use os ganchos `wp.hooks` do núcleo. Se observar DOM, escopo estreito, debounce, teardown.
- Callback de gancho de admin **devolve** string, sem `<script>` (TNC-T01, T02); JS em arquivo enfileirado que escuta `tainacan-<entidade>-hook-reload`.
- Checkbox grava array (`['1']`); `save_data` confere `can_edit()`.
- Drawer, não modal: sobreposição não bloqueante, indicador para reabrir, delegação de eventos no `document`, re-cache de referências, `escapeHtml` em todo conteúdo dinâmico (TNC-W09), enqueue condicional por tela.
- CSS sob a classe raiz do plugin, com bloco `:root` de tokens `--tainacan-*` com fallback (TNC-C02).
- REST própria em `<slug>/v1`, `args` validados, `permission_callback` real (TNC-S04), cache por transient prefixado.

## Fluxo

1. **Diagnóstico** (plugin existente): preencha o checklist de conformidade da referência e liste as lacunas por prioridade. Plugin novo: `tnc-novo-plugin` primeiro.
2. **Plano** com critérios verificáveis (`tnc-guardrails`): para cada camada, o comando ou a observação que prova (ex.: "abrir Itens > Editar, acionar, ver o campo recarregar sem F5").
3. **Implementar** camada por camada na ordem do playbook UIA-09, preservando o comportamento atual.
4. **Verificar** na SPA real, com o tema em uso e os outros plugins ativos: abrir item, acionar o painel, gravar, ver o Vue recarregar; console limpo; `tnc-validacao`.

## Relato

Camadas tocadas, o que foi substituído (observador de DOM → `addAction`; input → REST + evento), o checklist de conformidade marcado, o roteiro de teste manual na SPA.
