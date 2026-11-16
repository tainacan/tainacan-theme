---
name: tnc-guardrails
description: Conduz qualquer implementação ou correção de código em plugin do Tainacan ou no núcleo, do entendimento do pedido até a entrega verificada. Use para "implemente", "corrija", "ajuste", "refatore", "adicione campo/tela/rota" em PHP, JS, Vue ou CSS de projeto WordPress/Tainacan. Exige plano com critérios verificáveis, aplica as regras do Tainacan e termina com tnc-validacao.
---

# tnc-guardrails

Fluxo padrão de mudança de código. Outras skills (estender, REST, desempenho, migração) entram como complemento quando a demanda toca o assunto delas.

## 1. Entender antes de editar

- Leia a seção do projeto no `AGENTS.md` (estado, ambiente, pegadinhas, "não regredir").
- Leia por inteiro cada arquivo que vai mudar. Procure quem chama o código alterado (`grep -rn "nome_da_funcao"`), inclusive em JS e templates.
- Se a mudança toca o Tainacan, abra o trecho do núcleo que você vai usar e confira a assinatura. Referência rápida: `.agents/tainacan-stack/referencias/nucleo-tainacan.md`. Não confie em memória de versão: compare 1.0.x e 1.4.x quando o projeto suportar as duas.
- Declare as premissas. Ambiguidade que muda o resultado: pare e pergunte.

**Pare e pergunte se:** o pedido admite duas leituras com resultados diferentes; a mudança exige alterar o núcleo do Tainacan ou do WordPress; a correção muda dado já gravado (migração); há conflito entre o pedido e o "não regredir" do projeto.

## 2. Plano

Antes da primeira edição, escreva:

```
**Escopo:** arquivos que mudam e por quê
**Riscos:** dados gravados, convivência com outros plugins, desempenho em acervo grande, segurança
**Checklist:**
- [ ] <passo> → verificação: <comando ou observação concreta>
- [ ] tnc-check sem bloqueio → verificação: php .agents/tainacan-stack/bin/tnc-check --changed
```

Traduza o pedido em critério testável:
- "Corrigir o bug": reproduza primeiro (comando, URL, passo a passo); depois corrija; depois repita a reprodução.
- "Adicionar validação": liste as entradas inválidas e prove cada uma.
- "Melhorar desempenho": meça antes e depois com o mesmo comando (ver `tnc-desempenho`).

## 3. Implementar

- Siga o estilo do arquivo (nomes, comentários, formato de array, aspas). PHP novo abre com `<?php` e, em plugin, tem `defined( 'ABSPATH' ) || exit;`.
- Prefixe tudo que é global com o prefixo do plugin: funções, classes (ou namespace), constantes, opções, transients, handles, ações AJAX, rotas REST, classes CSS.
- **Hierarquia diante de qualquer aviso de lint ou segurança: refatorar > usar a API canônica do WordPress/Tainacan > suprimir com justificativa técnica** (`referencias/padroes-wordpress-org.md`, PAD-00). Supressão sem a frase "por que a alternativa não serve" não vale (TNC-B07, B05).
- Persistência: `wp_insert_post`/`get_post_meta`/`WP_Query` → repositórios do Tainacan → `$wpdb` só para tabela própria ou agregação, com `prepare()`.
- Entrada: `sanitize_*( wp_unslash( ... ) )`/`absint` (TNC-S02). Saída: escape **na linha do echo** com `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `esc_html__`; `printf( esc_html__( ... ), esc_html( $x ) )` (TNC-S05). SQL: `$wpdb->prepare()`. Escrita: nonce + `current_user_can`.
- Datas: `gmdate()` para banco e cálculo, `wp_date()` para exibição; nunca `date()`. `wp_json_encode`, `wp_remote_get`, `wp_delete_file`, `wp_rand`, `WP_Filesystem` no lugar das funções nativas (TNC-W13). `error_log()` só sob `WP_DEBUG` (TNC-B04). Biblioteca de terceiro em `assets/vendor/`, nunca CDN (TNC-W14).
- Tainacan: as regras curtas do `AGENTS.md` valem sempre. Para estender o núcleo, use `tnc-estender-tainacan`.
- Texto visível ao usuário passa por `__()` com o domínio do plugin.
- Edite arquivo acentuado com a ferramenta de edição do agente; no Windows PowerShell 5.1, nunca `Set-Content`/`Out-File` em código.

## 4. Verificar e entregar

Rode `tnc-validacao`. Bloqueio do `tnc-check` impede a entrega. Aviso: corrija ou justifique na linha (`// tnc-check-ignore TNC-XNN: motivo`).

Resposta final:
1. O que mudou, em uma ou duas frases por arquivo.
2. O checklist com `[x]` + comando + resultado, ou `[ ]` + motivo.
3. Pendências e o que depende de teste manual (com o passo a passo para quem vai testar).
4. Problemas encontrados fora do escopo, só como observação.
5. Lição nova? Indique se vale `tnc-registrar-licao`.
