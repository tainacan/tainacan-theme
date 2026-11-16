# Lições do tnc-check

Cada arquivo desta pasta documenta uma regra do verificador `tnc-check`: sintoma, causa (com arquivo:linha do núcleo do Tainacan 1.4.1, relativo a `src/`, ou do WordPress quando indicado), o que a regra detecta e o que ela deixa passar, como corrigir, quando suprimir e de onde a regra veio. O texto é lido por pessoas e por agentes de IA com `tnc-check --explain <ID>`.

Na coluna Severidade, "varia" indica que alguns achados da regra têm severidade diferente da padrão; o detalhe está na lição. As regras TNC-S05, W13, W14 e W15, a parte de `wp_unslash()` da S02 e a de `error_log()` da B04 só rodam em projeto WordPress (cabeçalho de plugin ou tema, ou `text_domain` no `.tnc-check.json`); fora disso o verificador analisa qualquer PHP, inclusive ele mesmo. A severidade de qualquer regra de arquivo pode ser trocada ou desligada no `.tnc-check.json` do projeto (`"rules": { "TNC-P02": "off" }`, com `"off"`, `"warn"` ou `"block"`; o TNC-B05 só aceita `"off"`). As conferências de release (TNC-R) rodam só com `tnc-check --release` e não aceitam supressão em linha nem baseline.

## base

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-B01](TNC-B01.md) | Erro de sintaxe PHP (php -l) | bloqueio | base |
| [TNC-B02](TNC-B02.md) | Tag de abertura curta (<?) | bloqueio | base |
| [TNC-B03](TNC-B03.md) | Codificação corrompida (BOM, UTF-8 inválido, mojibake) | bloqueio (varia) | base |
| [TNC-B04](TNC-B04.md) | Sobra de depuração | aviso (varia) | base |
| [TNC-B05](TNC-B05.md) | Supressão sem motivo | aviso | base |
| [TNC-B06](TNC-B06.md) | Arquivo PHP sem proteção contra acesso direto | aviso | base |
| [TNC-B07](TNC-B07.md) | Supressão do PHPCS sem justificativa técnica | aviso | base |
| [TNC-B08](TNC-B08.md) | get_template_part() para arquivo que não existe | bloqueio | base |

## seguranca

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-S01](TNC-S01.md) | SQL com variável sem $wpdb->prepare() | bloqueio (varia) | seguranca |
| [TNC-S02](TNC-S02.md) | Entrada HTTP usada sem sanitização | aviso | seguranca |
| [TNC-S03](TNC-S03.md) | Handler AJAX/admin-post sem nonce ou sem capability | bloqueio (varia) | seguranca |
| [TNC-S04](TNC-S04.md) | Rota REST sem permissão adequada | bloqueio (varia) | seguranca |
| [TNC-S05](TNC-S05.md) | Saída HTML sem escape no ponto de saída | aviso | seguranca |
| [TNC-S06](TNC-S06.md) | Construção perigosa (eval, unserialize, extract, include dinâmico) | bloqueio (varia) | seguranca |
| [TNC-S08](TNC-S08.md) | Credencial gravada no código | bloqueio (varia) | seguranca |
| [TNC-W09](TNC-W09.md) | JavaScript inseguro (eval, innerHTML variável, v-html) | aviso (varia) | seguranca |

## desempenho

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-P01](TNC-P01.md) | Consulta sem limite de resultados | aviso | desempenho |
| [TNC-P02](TNC-P02.md) | Consulta ou requisição HTTP dentro de laço (N+1) | aviso | desempenho |
| [TNC-P03](TNC-P03.md) | meta_query com LIKE/REGEXP (varredura completa de wp_postmeta) | aviso | desempenho |
| [TNC-P04](TNC-P04.md) | SQL destrutivo em tabela do WordPress | bloqueio (varia) | desempenho |
| [TNC-P08](TNC-P08.md) | Evento de cron agendado sem verificar se já existe | aviso | desempenho |

## tainacan

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-T01](TNC-T01.md) | Gancho de admin do Tainacan imprime em vez de devolver string | bloqueio | tainacan |
| [TNC-T02](TNC-T02.md) | <script> no HTML de gancho de admin do Tainacan (não executa) | bloqueio | tainacan |
| [TNC-T03](TNC-T03.md) | Gancho de admin registrado fora de tainacan-register-admin-hooks | aviso | tainacan |
| [TNC-T04](TNC-T04.md) | Opção de tipo de metadado lida sem o prefixo _option_ | aviso | tainacan |
| [TNC-T05](TNC-T05.md) | Repositório do Tainacan usado sem singleton ou sem validate() | bloqueio (varia) | tainacan |
| [TNC-T06](TNC-T06.md) | Gancho da busca facetada registrado com um nome só | bloqueio | tainacan |
| [TNC-T07](TNC-T07.md) | Parâmetro de busca que a REST do Tainacan descarta | aviso | tainacan |
| [TNC-T08](TNC-T08.md) | Página de admin do Tainacan sem load_page() | aviso | tainacan |
| [TNC-T09](TNC-T09.md) | Gancho ou consulta do Tainacan com uso que falha em silêncio | aviso (varia) | tainacan |

## wordpress

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-W03](TNC-W03.md) | Tradução sem domínio de texto ou com domínio errado | aviso | wordpress |
| [TNC-W10](TNC-W10.md) | Armadilha de WordPress que afeta o site inteiro | aviso (varia) | wordpress |
| [TNC-W11](TNC-W11.md) | $wpdb->prepare() com marcadores e argumentos em número diferente | aviso (varia) | wordpress |
| [TNC-W12](TNC-W12.md) | wp_localize_script() com booleano ou número (chegam como string) | aviso | wordpress |
| [TNC-W13](TNC-W13.md) | Função PHP com equivalente do WordPress | aviso | wordpress |
| [TNC-W14](TNC-W14.md) | Asset carregado de servidor externo (CDN) | aviso | wordpress |
| [TNC-W15](TNC-W15.md) | Marcador em string traduzível sem numeração ou sem comentário translators | aviso | wordpress |

## convivencia

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-C01](TNC-C01.md) | Remoção de ganchos ou assets de terceiros | aviso | convivencia |
| [TNC-C02](TNC-C02.md) | Seletor CSS global vazando para tema e outros plugins | aviso | convivencia |

## release

| ID | Título | Severidade | Categoria |
|---|---|---|---|
| [TNC-R00](TNC-R00.md) | Pasta sem cabeçalho Plugin Name: na raiz | bloqueio | release |
| [TNC-R01](TNC-R01.md) | Versões divergentes entre cabeçalho, constantes, readme e manifestos | bloqueio | release |
| [TNC-R02](TNC-R02.md) | readme.txt ausente ou incompleto | aviso | release |
| [TNC-R03](TNC-R03.md) | Changelog ausente ou sem a versão atual | aviso | release |
| [TNC-R04](TNC-R04.md) | Text Domain diferente do slug da pasta | aviso | release |
| [TNC-R05](TNC-R05.md) | Uso do Tainacan sem Requires Plugins: tainacan | aviso | release |
| [TNC-R06](TNC-R06.md) | Cron agendado sem remoção | aviso | release |
| [TNC-R07](TNC-R07.md) | JavaScript usa wp.i18n sem wp_set_script_translations() | aviso | release |
| [TNC-R08](TNC-R08.md) | phpcs.xml.dist ausente ou sem regras de segurança | aviso | release |

Quando o TNC-R00 aparece, as demais conferências de release não rodam.

## Como uma lição nova vira regra

Quando um problema foi entendido, pode se repetir e dá para reconhecer no código, ele vira regra: escreva a classe em `src/Rules/<Nome>.php` estendendo `Rule`, com `ID` (próximo número livre da categoria: B base, S segurança, P desempenho, T Tainacan, W WordPress, C convivência), `TITLE`, `SEVERITY`, `CATEGORY`, `EXTENSIONS` e um docblock que explique a causa citando o núcleo com arquivo:linha; crie as fixtures em `tests/fixtures/<ID>/`, com os casos que devem acusar anotados com `@expect <ID>` (ou `:block`/`:warn`) na linha exata e casos parecidos que não devem acusar; registre a classe em `src/Registry.php`; rode `php tests/run.php` até 0 falha; meça o ruído e procure o mesmo problema em outros plugins com `tnc-check --workspace=<pasta com os plugins> --rules=<ID> --no-lint`, transformando cada falso positivo em fixture que não acusa; por fim, escreva `licoes/<ID>.md` neste formato, acrescente a linha a este índice e atualize `CHANGELOG.md` e `VERSION`. O passo a passo completo está na skill `tnc-registrar-licao`. Problemas que não dá para detectar no código vão para `referencias/`, e os que só valem para um projeto, para o `AGENTS.md` dele.
