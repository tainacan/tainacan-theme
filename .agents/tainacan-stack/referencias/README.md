# Referências da Stack de IA do Tainacan

Lições curadas de cerca de 30 plugins reais que estendem o Tainacan e uma referência do núcleo com citações `arquivo:linha` (versões 1.0.3 e 1.4.1, caminhos relativos a `src/`). O público é a equipe do núcleo e quem desenvolve plugins, com ou sem agentes de IA.

**Regra de uso: consulte sob demanda e não carregue tudo.** Abra só o arquivo do assunto da tarefa e, dentro dele, vá pelo sumário ou pelo grep (`grep -n "### L-BUS" busca-e-indice.md`, `grep -rn "posts_pre_query" .`). Os arquivos divididos em partes têm um índice com o nome-base.

## Arquivos

| Arquivo | Código | Conteúdo |
|---|---|---|
| [`nucleo-tainacan.md`](nucleo-tainacan.md) (partes 1 a 3) | NUC | Referência do núcleo: arquitetura, entidades, repositórios, tipos de metadado, ganchos, REST, ferramental, armadilhas, modelos de código, com Δ entre 1.0.3 e 1.4.1, e lições do modelo de dados. |
| [`ganchos-e-extensao.md`](ganchos-e-extensao.md) (partes 1 e 2) | EXT | Como estender: ganchos de admin, `\Tainacan\Pages`, tipos de metadado e filtro, página do item, busca facetada, exposers, importadores, processos, REST, infraestrutura do plugin. |
| [`desempenho-e-escala.md`](desempenho-e-escala.md) | DES | Acervos com dezenas de milhares de itens: consultas, facetas, contagens, cache, assets, travas e filas, cron, lotes, capacidade do Elasticsearch/OpenSearch. |
| [`busca-e-indice.md`](busca-e-indice.md) | BUS | Busca roteada por índice externo, fallback SQL, disjuntor, contagens que não batem (origem × banco × índice), sinônimos, mapeamento, saúde do índice. |
| [`seguranca-wordpress.md`](seguranca-wordpress.md) | SEG | O que complementa as regras S/W: visibilidade e capabilities por coleção, cache por usuário, limite por IP e proxies confiáveis, segredos, SSRF, uploads, LGPD. |
| [`convivencia-entre-plugins.md`](convivencia-entre-plugins.md) | CON | Colisões plugin × plugin, plugin × tema e plugin × núcleo (sintoma, causa, regra de convivência), checklist para testar plugins juntos, `--conflicts` e `--inventory`. |
| [`front-end-e-vue.md`](front-end-e-vue.md) | FRO | Vue no admin e na busca facetada, formulário do item, `v-html`, chaves de lista, CSS que vaza, z-index, eventos, impressão, acessibilidade. |
| [`migracao-de-acervos.md`](migracao-de-acervos.md) (partes 1 e 2) | MIG | Integridade da origem ao Tainacan: nunca alterar valores, reconciliar contagens, CSV, multivalor, composto, anexos, identidade, enriquecimento, checklist de importação. |
| [`release-e-deploy.md`](release-e-deploy.md) | REL | Versão sincronizada, changelog, readme, ZIP, build, Plugin Check, renome de plugin, migrações no upgrade, OPcache, cache de borda, i18n do pacote. |
| [`padroes-wordpress-org.md`](padroes-wordpress-org.md) | PAD | Decisões para passar no Plugin Check do WordPress.org no primeiro envio: hierarquia refatorar > API canônica > suprimir, `$wpdb` por exceção, datas, entrada com `wp_unslash`, escape no ponto de saída, i18n, funções proibidas, concorrência, assets, revisão em duas camadas, fluxo de versão. |
| [`ui-admin-e-drawer.md`](ui-admin-e-drawer.md) | UIA | Integração com o admin do Tainacan: Pages API, trio de persistência dos campos de formulário, front com `@wordpress/hooks` e `api-fetch` sem Vue próprio, escrita de metadado pela REST + `TainacanReloadItemMetadataForm`, drawer lateral, tokens CSS, playbook para refatorar plugin existente. |
| [`ambiente-de-desenvolvimento.md`](ambiente-de-desenvolvimento.md) | AMB | Windows (PowerShell 5.1, CP850, CRLF, ZIP), XAMPP, WP-CLI, PHP no PATH, SSH e contêineres, testes e fluxo GitFlow do núcleo, práticas de trabalho com agentes. |

## Formato das lições

```
### L-<TEMA>-NN · <título curto>
**Sintoma:** ... **Causa:** ... **Correção:** ... **Detecção:** <ID do tnc-check> ou "manual: ..." **Vista em:** <tipos de projeto>
```

- As lições vistas em mais de um projeto vêm primeiro em cada arquivo e estão no "Comece por aqui".
- **Detecção** aponta a regra do `tnc-check` quando ela existe (`tnc-check --list-rules`). O detalhe da regra não se repete aqui.
- **Δ** marca diferença entre o Tainacan 1.0.3 e o 1.4.1. Em conflito entre uma lição e o código do núcleo, vale o núcleo.
