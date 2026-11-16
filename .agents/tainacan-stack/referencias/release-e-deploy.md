# Release e deploy de plugins Tainacan/WordPress

Pegadinhas de versão, empacotamento, renome, migração no upgrade, deploy, cache de borda e i18n do pacote, vistas em plugins que estendem o Tainacan. Cada lição traz sintoma, causa, correção e como detectar.

## Sumário

- [Versão e cache-buster](#versão-e-cache-buster) (L-REL-01 a 02)
- [Pacote de distribuição e build](#pacote-de-distribuição-e-build) (L-REL-03 a 11)
- [Renome de plugin e identificadores congelados](#renome-de-plugin-e-identificadores-congelados) (L-REL-12)
- [Instalação, upgrade e desinstalação](#instalação-upgrade-e-desinstalação) (L-REL-13 a 17)
- [Deploy no servidor](#deploy-no-servidor) (L-REL-18 a 26)
- [Cache de borda e links permanentes](#cache-de-borda-e-links-permanentes) (L-REL-27 a 28)
- [i18n do pacote](#i18n-do-pacote) (L-REL-29 a 37)
- [Mudanças atrás de interruptor](#mudanças-atrás-de-interruptor) (L-REL-38 a 40)
- [Documentação](#documentação) (L-REL-41 a 42)

## Comece por aqui

1. L-REL-01: versão igual no cabeçalho, na constante, no `Stable tag` e no `package.json` (`tnc-check --release`, TNC-R01).
2. L-REL-02: a versão é o cache-buster dos assets; mudou CSS/JS, sobe a versão.
3. L-REL-03: Plugin Check roda sobre o ZIP, não sobre o repositório.
4. L-REL-04: `vendor/` e `build/` precisam estar no pacote, sem dependências de dev.
5. L-REL-13: deploy por cópia não roda o gancho de ativação; instalação idempotente no boot, por versão.
6. L-REL-14: migração nunca em requisição anônima, sob trava, versão gravada depois.
7. L-REL-18: OPcache serve código velho após deploy; confirme a versão em execução antes de diagnosticar.
8. L-REL-19: deploy "por cima" ou atômico, com backup verificado; nunca `rm -rf` com o plugin ativo.
9. L-REL-12: renome de plugin: desativar o antigo antes de ativar o novo, identificadores de persistência congelados.
10. L-REL-27: páginas de item 404 depois de criar coleção ou atualizar: regras de reescrita velhas.

Referência do núcleo usada aqui: `pesquisa-core-tainacan.md`, seções D.2 (build), D.5 (versões mínimas), D.6 (CI) e D.8 (i18n). Citações relativas a `src/` do repositório do Tainacan; `repo:` indica arquivo fora do plugin.

---

## Versão e cache-buster

### L-REL-01 · Versão sincronizada em todos os lugares
**Sintoma:** depois de atualizar, o navegador não renova JS/CSS; ou cabeçalho, constante, `Stable tag` e `package.json` mostram versões diferentes. Um PR para `main` ganhou bump *minor* automático que ninguém pediu.

**Causa:** a versão mora em vários lugares (cabeçalho `Version:`, `define('*_VERSION')`, `Stable tag` do `readme.txt`, `package.json`, às vezes `composer.json`) e é atualizada à mão. Num caso a constante ficou em 0.1.7 com o cabeçalho em 0.1.8, e o `?ver=` não mudou. Um workflow de CI alterava a versão ao abrir PR.

**Correção:** um script único propaga a versão (por exemplo, a partir do `package.json`) para todos os lugares, no mesmo commit. Changelog a cada versão (seção `== Changelog ==` do `readme.txt` ou `CHANGELOG.md`). Conferir a versão resultante antes de mesclar PR quando há automação de bump.

**Detecção:** TNC-R01 (versões divergentes), TNC-R03 (changelog sem a versão atual), TNC-R02 (campos do `readme.txt`).

**Vista em:** plugin de processos de gestão de acervo, painel público de indicadores, plugin de impressão de itens.

### L-REL-02 · A versão do plugin é o cache-buster dos assets
**Sintoma:** correção de CSS "não surte efeito"; deploy confirmado, arquivo novo no servidor, mas a tela se comporta como duas versões atrás.

**Causa:** o `ver` de `wp_enqueue_style`/`wp_enqueue_script` é a constante de versão. Sem bump, o navegador e os caches intermediários servem o arquivo antigo. Com webpack é pior: o mapa de nomes dos chunks vive no bundle principal; se ele vem do cache, o chunk novo nunca é pedido.

**Correção:** todo deploy que muda asset sobe a versão (ver L-REL-01). Alternativa robusta: `VERSION . '.' . filemtime( $arquivo )` como versão do asset, que muda sozinha quando o arquivo muda.

**Detecção:** manual: diff que altera `assets/**/*.css|js` (ou `build/`) sem alterar o cabeçalho `Version:`/constante; `wp_enqueue_*` com versão literal fixa ou `null`.

**Vista em:** plugin de biblioteca, plugin de dados ligados, agregador arquivístico.

---

## Pacote de distribuição e build

### L-REL-03 · Plugin Check sobre o ZIP e exigências do diretório do WordPress.org
**Sintoma:** Plugin Check com mais de mil achados, muitos em arquivos que nunca são publicados (`tools/`, `docs/`, testes); ou lint local limpo e Plugin Check reprovando; ou pendências descobertas só na hora de enviar ao diretório.

**Causa:** a ferramenta rodou sobre o repositório cru, não sobre o pacote. O standard `PluginCheck` não é instalável pelo Composer, então o phpcs local não o inclui. Exigências do diretório desconhecidas.

**Correção:**
- Montar em `wp-content/plugins/<slug>` só o conteúdo do ZIP (sem `node_modules`, `docs`, `tests`, `tools`, fontes de `src/views`, `*.map`, `package*.json`, `.github`, arquivos de contexto de agente) e rodar `wp plugin check` ali. É a verificação autoritativa; rode à parte do phpcs.
- Pastas de desenvolvimento em `export-ignore` no `.gitattributes` (ou no `.distignore`, ver L-REL-05). CI com phpcs em PHP 7.4 e 8.x mais Plugin Check.
- Exigências frequentes: sem CDN (empacotar ícones e bibliotecas); sem cabeçalho `Update URI:`; sem heredoc; `readme.txt` com seção "External services" quando há chamada externa; `gmdate()`/`wp_date()` no lugar de `date()`; `error_log` só sob `WP_DEBUG`; `esc_html__()` em mensagens de exceção; comentários `/* translators: */`; escape no ponto de saída; `Tested up to` atualizado; `Plugin URI` que não aponte para repositório privado; `Contributors` reais; ícone, banner e capturas.
- Licenças: cabeçalho GPL-2.0 com dependência Apache-2.0 ou LGPL-3 embarcada é incompatível; GPL-3.0-or-later resolve. Dependência AGPL não entra no pacote do diretório: o módulo se declara indisponível.
- Regenerar o autoloader do Composer sem dependências de dev antes de empacotar (L-REL-04).
- Refatoração de escape para agradar o Plugin Check: conferir que o HTML renderizado ficou byte-idêntico (MD5 antes/depois).
- O slug no WordPress.org é imutável depois da primeira publicação. Mudar o nome de exibição é livre; pasta, slug, text domain, prefixos, namespace REST e tabelas ficam (ver L-REL-12).

**Detecção:** TNC-R02 (campos do `readme.txt`), TNC-R04 (text domain diferente do slug); o resto é manual: URL de CDN em `wp_enqueue_*`, `Update URI:`, `<<<` em PHP, `date(`, `error_log(` fora de `WP_DEBUG`, ausência de `.gitattributes`/`.distignore` com as pastas de dev.

**Vista em:** plugin de biblioteca, indexador de busca, kit de memorial virtual, colaboração do público em metadados, plugin de busca segmentada, plugin de impressão de itens, plugin de processos.

### L-REL-04 · `vendor/` e `build/` ausentes ou com dependências de dev
**Sintoma:** plugin ativo mas inerte (aviso de autoload do Composer) ou telas sem script e estilo, em silêncio. Em outro caso, fatal no `autoload_files.php` do ZIP de produção.

**Causa:** `build/` (saída de `npm run build`) e `vendor/` não estão no repositório; "Download ZIP" ou clone não os trazem e um deploy genérico por Git publica o plugin sem interface. `npm ci` falha com lock dessincronizado. Quando `vendor/` é versionado depois de um `composer install` com dev, `vendor/composer/autoload_*.php` aponta para pacotes de dev que o pacote não leva.

**Correção:**
- Release por ZIP que inclui `build/` e `vendor/` gerados com `composer install --no-dev --optimize-autoloader` e `npm install && npm run build`; usar o script oficial de empacotamento do projeto.
- Deploy por Git só com passo de build (opção `--build` quando `src/` muda).
- Se `vendor/` é versionado (produção sem Composer), rodar `composer install --no-dev -o` antes de commitar mudanças em `vendor/`; depois voltar com `composer install` e não commitar esse diff.
- Bibliotecas JS/CSS ficam locais em `assets/vendor/` (sem CDN em runtime: firewall institucional e regra do diretório).
- No núcleo: o pacote é `src/` ("Everything outside this folder is not part of the distribution package", `repo:docs/setup-local.md:9-14`), o `vendor` fica em `src/vendor` (`repo:composer.json`), e o `build.sh` roda `composer install` com `--no-dev` em `--prod`. Em `--prod` ele também apaga os fontes `.vue/.js/.scss` de `views/`, `class-tainacan-bulk-edit.php`, os exposers oai-pmh, json-ld, txt e xml e os `.js.map` (`repo:build.sh:1-130`). Plugin que depende desses arquivos do núcleo pode não achá-los num pacote de produção.

**Detecção:** manual: `.gitignore` com `build/` e PHP enfileirando `build/*.js` sem `file_exists`; `vendor/composer/autoload_files.php`/`autoload_static.php` citando pacotes de `require-dev` (`phpunit`, `phpcs`).

**Vista em:** plugin de processos, plugin de IA de terceiros para Tainacan, plugin de narrativas, gestor de periódicos, kit de memorial virtual.

### L-REL-05 · Exclusão sem âncora remove pastas que deviam ir no pacote
**Sintoma:** ícones de menu e biblioteca de PDF sumiram do pacote; biblioteca de ícones seria excluída do repositório e o plugin sairia sem ícones.

**Causa:** padrão `vendor` (ou `vendor/`) no `.distignore`, no `--exclude` ou no `.gitignore` casa qualquer diretório com esse nome em qualquer nível, inclusive `assets/vendor/`.

**Correção:** ancorar na raiz: `/vendor`, `/build`, `/node_modules`. Ao adicionar biblioteca de terceiros, conferir com `git check-ignore -v <arquivo>` e listar o conteúdo do ZIP gerado.

**Detecção:** manual: linhas de `.distignore`/`.gitignore` sem `/` inicial para nomes comuns (`vendor`, `build`, `icons`) em projeto que tem `assets/vendor/`.

**Vista em:** plugin de biblioteca, vários projetos num servidor de testes compartilhado.

### L-REL-06 · Sintaxe de PHP 8 em plugin que declara PHP 7.4
**Sintoma:** toda folha com QR dava parse error em PHP 7.4; o mesmo `match` passou por três revisões sem ser notado.

**Causa:** `match`, `?->`, `enum`, argumentos nomeados e outras construções de PHP 8 em plugin com `Requires PHP: 7.4`. O lint local rodava em PHP 8.

**Correção:** `switch` no lugar de `match` etc. `php7.4 -l` em todos os arquivos antes de publicar; CI em 7.4 e 8.x; phpcs com o `phpcs.xml.dist` do plugin. Declarar no cabeçalho e no `readme.txt` o mínimo real (`Requires PHP`, `Requires at least`) e, se usa classes do Tainacan, `Requires Plugins: tainacan` (WordPress 6.5+). O Tainacan 1.4.1 exige PHP 7.4+ e WordPress 6.5+ (`tainacan.php:9-11`; `readme.txt:4-6`). **Δ** A 1.0.3 declarava PHP 7.0+ e WordPress 6.0+. A matriz de CI do núcleo ainda testa WordPress `5.9`, abaixo do mínimo declarado (`repo:.github/workflows/test.yml`).

**Detecção:** TNC-B01 rodando o `tnc-check` com o binário PHP 7.4 (a regra usa o mesmo PHP que executa o verificador); TNC-R05 (falta `Requires Plugins: tainacan`); TNC-R02 (falta `Requires PHP`).

**Vista em:** plugin de dados ligados, plugin de impressão de itens.

### L-REL-07 · Classe referenciada que não chegou ao pacote ou ao bootstrap
**Sintoma:** site fora do ar por cerca de 1 min com `Class ... not found`; em outro caso, etiqueta com QR saindo em branco (PDF de 1.296 bytes em vez de ~10 KB), sem erro nenhum.

**Causa:** o bootstrap local referenciava uma classe cujo arquivo nunca tinha ido para produção, e a produção avançara por outras mãos (cerca de 10 commits à frente). No caso silencioso, o arquivo da classe não estava no `require_once` do bootstrap e o código defensivo `if ( class_exists( ... ) )` simplesmente pulava.

**Correção:** `git pull` antes de alterar; listar `src/**/*.php` local e no servidor e comparar (`comm -23`); conferir a versão no servidor, não no arquivo local. Incluir o arquivo no bootstrap/autoload. Testes conferem tamanho e conteúdo da saída, não só o status.

**Detecção:** manual: classes usadas (`new`, `use`, `::`, `class_exists('X')`) cujo arquivo não é alcançado por `require` nem autoload, ou não está na lista do pacote.

**Vista em:** plugin de biblioteca, plataforma de mapeamento comunitário, plugin de eventos e certificados em PDF.

### L-REL-08 · Chunks órfãos do webpack só se provam com build limpo
**Sintoma:** arquivos `assets/js/NNN.js` suspeitos de órfãos por várias versões.

**Causa:** procurar `.e(N)` no bundle principal não cobre o bundle do front-end, e o webpack só reescreve o chunk que mudou, então a data de modificação não prova nada.

**Correção:** apagar todos os chunks numéricos e o cache do webpack e rodar build limpo; o que reaparece está vivo. É seguro quando `assets/` é versionado (`git checkout -- assets/` desfaz).

**Detecção:** manual: build limpo e `git status` em `assets/`.

**Vista em:** plugin de biblioteca.

### L-REL-09 · `?>` dentro de comentário de linha fecha o PHP
**Sintoma:** um `<?xml ... ?>` escrito num comentário `//` fez o resto da linha sair como texto na página.

**Causa:** comentários `//` e `#` terminam no fim da linha **ou** em `?>`. O `?>` do exemplo encerrou o modo PHP. (A lição original atribuía o efeito ao `<?`; o gatilho é o `?>`.)

**Correção:** não escrever `<?`/`?>` literais em comentários; usar `<\?xml` ou descrever em palavras.

**Detecção:** manual: `grep -nE '(//|#).*\?>' **/*.php`. TNC-B01 não acusa (o arquivo é sintaticamente válido).

**Vista em:** plugin de biblioteca.

### L-REL-10 · `ini_get('safe_mode')` é código morto
**Sintoma:** verificação inútil e aviso do Plugin Check.

**Causa:** `safe_mode` foi removido no PHP 5.4.

**Correção:** remover a verificação.

**Detecção:** manual: `grep -n "ini_get( *'safe_mode'"`.

**Vista em:** plugin de narrativas.

### L-REL-11 · Workflow de CI recusado no push
**Sintoma:** push com `.github/workflows/*.yml` recusado.

**Causa:** token sem escopo `workflow`.

**Correção:** manter o arquivo fora de `.github/workflows` (ex.: `docs/ci/`) até `gh auth refresh -s workflow`.

**Detecção:** manual: mensagem de recusa do push.

**Vista em:** plugin de narrativas.

---

## Renome de plugin e identificadores congelados

### L-REL-12 · Renome de plugin: desativar o antigo, migrar sob trava, congelar identificadores
**Sintoma:** dois plugins do mesmo código ativos e ganchos duplicados; temas que sobrescreviam classes antigas quebram; risco de mídias órfãs, blocos quebrados e configurações "perdidas" ao absorver plugins menores num maior.

**Causa:** renome de slug e prefixos. Opções, metas, tabelas, crons, capabilities, namespace REST, nome de bloco e pasta de uploads são identificadores de persistência: mudá-los exige migração.

**Correção:**
- Preferir não renomear: manter o slug e sobrescrever o código, com migração automática no primeiro carregamento (sob flag) e contagem de dados antes e depois.
- Se o renome é inevitável: desativar o antigo antes de ativar o novo; migração idempotente sob trava e com `db_version`; renomear opções sem sobrescrever as que já existem; `UPDATE` de `meta_key` só pela chave exata (nunca em valor serializado); apagar transients e crons antigos; aliases para shortcodes antigos; ensaiar numa cópia do banco.
- Ao absorver plugins: manter todos os identificadores antigos; mudar só namespace PHP e textdomain; testar o pacote novo com o antigo ainda ativo (sem colisão de autoloader).
- Escolher um slug que não comece com o de outro projeto: no WordPress.org ele não muda depois (L-REL-03).

**Detecção:** TNC-R04 (text domain diferente do slug após o renome); manual: diff de release que altera literais de `get_option(`, namespace de `register_rest_route(`, nome em `register_block_type(` ou nome de tabela sem rotina de migração.

**Vista em:** plugin de dados ligados, plugin de busca segmentada, plugin de impressão de itens, plugin de processos, kit de memorial virtual.

---

## Instalação, upgrade e desinstalação

### L-REL-13 · Deploy por cópia não roda o gancho de ativação
**Sintoma:** tabelas e opções novas ausentes em instalações atualizadas copiando arquivos; em multisite, sites novos sem instalação.

**Causa:** a lógica de instalação está só em `register_activation_hook`, que dispara apenas na ativação pela interface ou CLI.

**Correção:** opção `*_db_version` (autoload) e `maybe_upgrade()` no boot, idempotente, comparando versões. Em multisite: `activate( $network_wide )` percorre os sites e `wp_initialize_site` instala nos novos; `uninstall.php` também percorre a rede. Restrições de quando rodar: L-REL-14.

**Detecção:** manual: `dbDelta`/`add_option`/`CREATE TABLE` só dentro do callback de `register_activation_hook`, sem checagem de versão equivalente em `plugins_loaded`/`init`.

**Vista em:** indexador de busca, página pública do item por tipo de mídia.

### L-REL-14 · Migração de schema e dados: fora de requisição anônima, sob trava, versão gravada depois
**Sintoma:** migração rodando duas vezes em pedidos concorrentes; migração meio aplicada marcada como concluída; risco em upgrades com `dbDelta`.

**Causa:** rotina de upgrade disparada em qualquer requisição; checagem "já rodou?" sem lock; versão gravada antes do fim; `dbDelta`/`create_tables` inteiro usado em upgrade.

**Correção:**
- Migrações só em admin, cron ou WP-CLI (`is_admin()`, `wp_doing_cron()`, `defined('WP_CLI')`), sob trava (`GET_LOCK` do MySQL ou `INSERT IGNORE` de uma flag). O mesmo padrão vale para ler-modificar-gravar e reservas atômicas.
- A versão do schema é gravada **depois** de concluir.
- Em upgrade, `ALTER TABLE ... ADD COLUMN` pontual em `upgrade_to_X()`; `dbDelta` fica para a criação inicial. Se uma rota pública depende de tabela nova, criar só a tabela sob demanda, com limite de frequência (ex.: no máximo a cada 10 min).
- Migração de dados idempotente, com diário e reversão; registros antigos vão para a lixeira, não são apagados.
- No núcleo, as migrações do Tainacan rodam em `init` (`tainacan.php:37-41`) e criam tabelas próprias (ex.: `tnc_bg_process` em `migrations.php:32`; **Δ** `tainacan_logs` só na 1.4.1, `migrations.php:553-560`).

**Detecção:** manual: `update_option('*_version'...)` antes de `dbDelta`/`$wpdb->query` na mesma função; rotina de upgrade em `init`/`plugins_loaded` sem `is_admin()`/`wp_doing_cron()`/`WP_CLI`; `dbDelta(` dentro de função `upgrade_*`; `if ( ! get_option( 'x_done' ) ) { ...; update_option( 'x_done', 1 ); }` sem lock.

**Vista em:** plugin de biblioteca, plugin de processos.

### L-REL-15 · `uninstall.php` autossuficiente, e o botão "Excluir" roda ele
**Sintoma:** desinstalação quebrada; ou, ao limpar cópias duplicadas do plugin (`-1`, `-old`), excluir pelo admin apagaria as tabelas da instalação em uso.

**Causa:** `uninstall.php` roda com o plugin descarregado, então constantes do arquivo principal (ex.: `MEU_PLUGIN_DIR`) não existem. A exclusão pelo admin executa `uninstall.php`; remover a pasta pelo gerenciador de arquivos não executa.

**Correção:** `uninstall.php` usa `__DIR__` e não depende do arquivo principal; apaga transients por prefixo exato; em multisite percorre a rede (L-REL-13). Cópias duplicadas se removem por arquivo, nunca pelo botão "Excluir". Eventos de cron são removidos no `register_deactivation_hook`.

**Detecção:** TNC-R06 (cron agendado sem remoção); manual: `uninstall.php` citando constantes definidas no arquivo principal.

**Vista em:** plugin de biblioteca, indexador de busca.

### L-REL-16 · Migração de configuração só em memória é intencional
**Sintoma:** `wp option get` mostra `schema_version=1` depois de atualizar.

**Causa:** a migração da configuração é aditiva e acontece em memória ao ler; só é gravada quando alguém salva.

**Correção:** documentar o comportamento; não tratar como defeito. Diagnosticar pela configuração efetiva, não pela opção crua.

**Detecção:** manual: ler o código de leitura da configuração antes de abrir chamado.

**Vista em:** plugin de redirecionamento (WordPress).

### L-REL-17 · Endereços de parceiros gravados no banco quebram após migrar
**Sintoma:** 401 entre instalações federadas depois de migrar ou renomear um site.

**Causa:** os endereços dos parceiros ficam numa tabela própria de cada instalação, e a troca de domínio não os atualiza.

**Correção:** incluir essas tabelas no roteiro de migração de domínio e atualizar em todos os bancos envolvidos.

**Detecção:** manual: inventário de tabelas próprias com URLs absolutas.

**Vista em:** servidor de testes compartilhado (vários projetos).

---

## Deploy no servidor

### L-REL-18 · OPcache servindo código velho após deploy
**Sintoma:** "bugs" que eram código antigo: correção enviada e comportamento antigo por minutos; uploads na pasta errada, deduplicação falhando, erros já corrigidos voltando.

**Causa:** workers do PHP-FPM servindo bytecode antigo; o OPcache não revalidou timestamps de forma confiável entre chamadas próximas.

**Correção:** depois de cada deploy, `touch` no arquivo principal ou recarregar o PHP-FPM; conferir md5 local × remoto. Ecoar a versão do plugin nas respostas e no cabeçalho do log (o JS avisa se a versão vier ausente) e confirmar a versão em execução antes de diagnosticar.

**Detecção:** manual: comparar a versão devolvida pela aplicação com a do arquivo publicado.

**Vista em:** indexador de busca, importador de pacotes de preservação (DIP).

### L-REL-19 · Deploy "por cima" ou atômico, com backup verificado
**Sintoma:** instante sem plugin ou plugin quebrado durante o deploy; necessidade de rollback rápido; dumps avulsos sumindo do servidor.

**Causa:** `rm -rf` da pasta antes de copiar a nova; nenhuma rotina de backup antes do deploy.

**Correção:**
1. Snapshot do banco e dos arquivos antes, com verificação (`gzip -t`, `tar -tzf`); registrar o caminho do backup.
2. Pacote por `git archive` (ou o ZIP de release); enviar.
3. Extrair por cima, ou extrair num diretório de stage no mesmo sistema de arquivos e trocar com `mv -f` (atômico).
4. Ajustar dono e permissões; `touch` no arquivo principal (L-REL-18).
5. `php -l` com o PHP do servidor (L-REL-20); conferir HTTP 200 do site e de um asset, ausência de 500 e a versão em execução.
6. Se quebrou, desativar automaticamente; rollback é extrair o tar anterior.

**Detecção:** manual: script de deploy com `rm -rf .../plugins/<slug>` antes da cópia.

**Vista em:** plataforma de mapeamento comunitário, plugin de dados ligados, plugin de redirecionamento, plugin de busca segmentada.

### L-REL-20 · `php -l` com o PHP que roda o site
**Sintoma:** lint local verde e servidor com outra versão de PHP; em outro caso, o script de deploy viu "93 arquivos com erro" e restaurou o backup sem necessidade.

**Causa:** PHP local diferente do de produção. Em ambiente com contêineres, o `php` só existe dentro do contêiner, não no host onde o script roda.

**Correção:** validar a sintaxe dentro do contêiner (`docker exec`, `kubectl exec` ou equivalente), numa área de stage fora do ar, antes de publicar; o script aborta sem tocar a produção se a validação falhar. WP-CLI também com o PHP do site (L-AMB-11 em `ambiente-de-desenvolvimento.md`).

**Detecção:** TNC-B01 quando o `tnc-check` roda com o mesmo binário PHP da produção; manual: script de deploy chamando `php -l` sem prefixo de execução no contêiner.

**Vista em:** plugin de eventos e certificados em PDF, servidor de testes compartilhado.

### L-REL-21 · Saída vazia de deploy nunca é sucesso
**Sintoma:** script remoto executado com 0 bytes e exit 0.

**Causa:** um `sed ... > arquivo` falhou e criou arquivo vazio; o fallback `ls || cat` não rodou porque o arquivo existia.

**Correção:** `set -e`; conferir `wc -c` (e, se possível, um hash) do script antes de transferir; o script remoto imprime um marcador de fim que o chamador exige.

**Detecção:** manual: `cmd > arquivo` sem checagem de tamanho antes do envio.

**Vista em:** plugin de pesquisas (questionários).

### L-REL-22 · Réplicas atrás de balanceador: janela de propagação
**Sintoma:** leituras alternando versão nova e antiga por até ~150 s; diagnóstico falso de "split" permanente.

**Causa:** várias réplicas atrás do balanceador ou do cache de borda atualizam em momentos diferentes.

**Correção:** conferir o hash do arquivo dentro do contêiner primeiro; amostrar 8 a 10 vezes pela borda; só concluir falha se o contêiner estiver errado.

**Detecção:** manual.

**Vista em:** plugin de pesquisas (questionários).

### L-REL-23 · Ferramenta de deploy por Git e API do provedor de hospedagem enganam
**Sintoma:** "último deploy" de meses atrás; conclusão errada de que um arquivo não existe ou de que o deploy não persistiu.

**Causa:** o branch cadastrado na ferramenta de deploy por Git tinha sido apagado do remoto; atualizar o cadastro do repositório sobrescreve com vazio os campos omitidos. A listagem de diretório pela API do provedor vinha defasada (omitia subpastas), e o endpoint de deploy falhava com 500 na maioria das vezes, mas às vezes funcionava.

**Correção:** conferir o branch cadastrado; ao atualizar o cadastro, reenviar todos os valores atuais. Listar o caminho alvo diretamente, não o diretório pai. Para operações que a API não oferece (SQL, backup), usar a REST do WordPress com Application Password ou um plugin próprio de snapshot.

**Detecção:** manual.

**Vista em:** ferramenta de deploy por Git, painel do provedor de hospedagem.

### L-REL-24 · Permissão `0640` torna o arquivo ilegível ao servidor web
**Sintoma:** validação devolve 404 para um documento que existe.

**Causa:** arquivo gerado por CLI como root com `0640`; o usuário do PHP-FPM não consegue ler.

**Correção:** `0644`. A proteção real vem do diretório fora da raiz web (ou `.htaccess`), nome aleatório e entrega pelo PHP.

**Detecção:** manual: `chmod(..., 0640)`/`0600` em arquivos que o PHP-FPM precisa ler; `ls -l` após gerar por CLI.

**Vista em:** plugin de eventos e certificados em PDF.

### L-REL-25 · `WP_DEBUG_LOG` esquecido ligado em servidor de testes
**Sintoma:** `debug.log` de 149 MB em armazenamento de rede.

**Causa:** `WP_DEBUG`/`WP_DEBUG_LOG` deixados ligados depois de uma investigação.

**Correção:** desligar ao terminar; logs de `catch` silenciosos só sob `WP_DEBUG`. Falhas que precisam ser vistas em produção vão para contadores persistentes (L-AMB-34 em `ambiente-de-desenvolvimento.md`).

**Detecção:** manual: `define('WP_DEBUG_LOG', true)` no `wp-config.php` de ambiente compartilhado; tamanho de `wp-content/debug.log`.

**Vista em:** indexador de busca.

### L-REL-26 · Sem rollback depois de entregue
**Sintoma:** risco de quebrar links e invalidar códigos já enviados por e-mail.

**Causa:** rollback do banco apaga documentos que já são referenciados fora do sistema.

**Correção:** depois que algo saiu (e-mail, link público, código de validação), corrigir por reemissão que preserva código e data; rollback só antes da entrega.

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

---

## Cache de borda e links permanentes

### L-REL-27 · Páginas de item e coleção 404 com a REST funcionando
**Sintoma:** depois de atualizar, migrar o site ou criar coleção, as páginas públicas de item dão 404 enquanto a REST serve os registros.

**Causa:** regras de reescrita desatualizadas. Cada coleção registra o seu CPT `tnc_col_{id}_item` com `rewrite.slug` igual ao slug da coleção (`classes/entities/class-tainacan-collection.php:119-169`, argumentos em `:131-162`), então coleção nova exige regras novas. No Nginx não há `.htaccess`: as regras vivem só na opção `rewrite_rules`, que fica velha.

**Correção:** reenviar Configurações → Links permanentes sem alterar nada, ou `wp rewrite flush --hard`. Não chamar `flush_rewrite_rules()` na ativação do seu plugin: ver a lição correspondente em `convivencia-entre-plugins.md`; flush por assinatura de versão em `ganchos-e-extensao.md`.

**Detecção:** manual: comparar `GET /wp-json/tainacan/v2/items/<id>` (200) com a URL pública do item (404).

**Vista em:** plugin de biblioteca, servidor de testes compartilhado (vários projetos).

### L-REL-28 · Cache de página (Varnish) serve HTML velho a anônimo
**Sintoma:** visitante anônimo vê HTML antigo depois de deploy, coleta, edição ou envio.

**Causa:** cache de página na frente do WordPress; o logado não vê (cookie fura o cache) e o anônimo vê.

**Correção:** `PURGE` com o `Host` do site ao fim de publicação, fusão, sincronização, restauração e no script de deploy. Endpoint configurável por filtro. Na primeira resposta diferente de 200, gravar uma opção que desliga o purge e não insistir.

**Detecção:** manual: `curl -sI` anônimo e conferir cabeçalhos de idade/cache (`Age`, `X-Cache`).

**Vista em:** agregador arquivístico.

---

## i18n do pacote

### L-REL-29 · `_x()` quando o inglês não distingue gênero ou contexto
**Sintoma:** "completed" já traduzido no masculino não servia para "etapa concluída"; tradução pt_BR forçada a uma forma só (`Desatualizada`/`Desatualizadas`).

**Causa:** o mesmo msgid em inglês é usado em contextos com gênero ou número diferentes em português.

**Correção:** `_x( 'completed', 'stage state', 'meu-plugin' )` para separar contexto, ou msgid distinto ("Next" vira "Next steps"). Idioma-fonte do código em inglês (exigido pelo GlotPress/diretório); conteúdo e dados linguísticos podem seguir em português.

**Detecção:** TNC-W03 cobre domínio ausente ou errado; o resto é manual: o mesmo literal curto (1 ou 2 palavras) em `__()` em telas diferentes.

**Vista em:** kit de memorial virtual, plugin de processos, plugin de eventos e certificados em PDF.

### L-REL-30 · Regerar o POT com outra ferramenta produz diff gigante
**Sintoma:** `wp i18n make-pot` local reescreveu o catálogo inteiro.

**Causa:** o catálogo existente foi gerado por outra ferramenta, com outra ordem e outros comentários.

**Correção:** acrescentar só as entradas novas ao fim do `.pot`/`.po` e compilar `.mo`/JSON com o script do projeto; nunca editar `.mo` à mão.

**Detecção:** manual: tamanho do diff do `.pot`.

**Vista em:** plugin de processos.

### L-REL-31 · Duração em texto livre: concordância e formato
**Sintoma:** "um hora e trinta minutos"; "1.30" lido como 1h30; conjunção errada em período ("de 3 e 5").

**Causa:** "hora" é feminino; número puro é decimal; dois dias seguidos pedem "e", intervalo pede "a".

**Correção:** concordância de gênero nos números por extenso (uma, duas, duzentas horas); "1:30" para relógio; recusar texto sem unidade reconhecida em vez de adivinhar.

**Detecção:** manual: testes com valores de borda.

**Vista em:** plugin de eventos e certificados em PDF, plugin de processos.

### L-REL-32 · `load_plugin_textdomain()` ainda é necessário para `.mo` embarcado
**Sintoma:** tradução embarcada em `languages/` nunca encontrada fora do diretório oficial.

**Causa:** `WP_Textdomain_Registry::get_paths_for_domain()` só olha `WP_LANG_DIR/plugins` e os caminhos registrados por `load_plugin_textdomain()` (medido no WordPress 7.1).

**Correção:** manter a chamada (em `init`), com supressão justificada do aviso do Plugin Check. Desde o WordPress 6.7, textos traduzíveis carregados antes de `init` geram aviso: o núcleo do Tainacan carrega os rótulos de papéis e capabilities em `init` por isso (`classes/class-tainacan-roles.php:68-73`). O núcleo não tem `languages/` em `src/` (D.8).

**Detecção:** manual: plugin com `languages/*.mo` e sem `load_plugin_textdomain(`.

**Vista em:** kit de memorial virtual.

### L-REL-33 · Textdomain do `block.json` diferente do plugin
**Sintoma:** rótulos dos blocos nunca traduzíveis.

**Causa:** `block.json` declarava o textdomain de plugins antigos; `wp_set_script_translations()` usava o novo.

**Correção:** alinhar `"textdomain"` do `block.json` com o `Text Domain` do cabeçalho e com o domínio passado a `wp_set_script_translations()`. Com chunks de webpack, as traduções de cada chunk precisam de carregador próprio; o núcleo usa `tainacan_load_script_translation_file_for_chunk` (`classes/tainacan-utils.php:324`) e `wp_set_script_translations(..., 'tainacan')` (`views/class-tainacan-pages.php:490`).

**Detecção:** manual: `"textdomain"` de cada `block.json` comparado com o cabeçalho. TNC-W03 confere só as chamadas PHP.

**Vista em:** kit de memorial virtual.

### L-REL-34 · `/* translators: */` dentro de atributo JSX é descartado
**Sintoma:** `make-pot` reclama de falta de comentário de tradutor.

**Causa:** o Babel descarta comentário dentro de atributo JSX.

**Correção:** mover o `sprintf( __( ... ) )` para uma `const` antes do JSX, com o comentário na linha imediatamente acima do `__(`.

**Detecção:** manual: `/* translators:` dentro de `{...}` de atributo JSX.

**Vista em:** plugin de processos.

### L-REL-35 · Renomear variável por substituição textual quebra `%1$s`
**Sintoma:** fatal na página de lote depois de uma refatoração.

**Causa:** a troca `$s` → `$tip_s` (exigida por um sniff de variável global em templates) também trocou `%1$s` por `%1$tip_s` dentro das strings traduzíveis.

**Correção:** restaurar os placeholders; refatorar renomes com ferramenta que entende tokens, ou excluir literais da substituição.

**Detecção:** manual: regex `%\d+\$[a-z_]{2,}` dentro de `__()`, `esc_html__()` e `sprintf()`.

**Vista em:** plugin de impressão de itens.

### L-REL-36 · Texto assinado tem de ser montado no idioma do site
**Sintoma:** quem assina e quem confere poderiam gerar textos diferentes, e hashes diferentes, por causa do idioma de cada usuário.

**Causa:** rótulos traduzidos pelo locale do usuário entram no documento assinado.

**Correção:** montar o documento sempre entre `switch_to_locale( get_locale() )` e `restore_previous_locale()`.

**Detecção:** manual: função que calcula hash/assinatura sobre texto com `__()` sem `switch_to_locale` antes.

**Vista em:** plugin de processos.

### L-REL-37 · Classe de domínio pura não chama `__()`
**Sintoma:** impossível testar a lógica sem WordPress.

**Causa:** funções de tradução dentro do núcleo puro.

**Correção:** quem chama injeta o rótulo já traduzido; a classe pura trabalha com chaves.

**Detecção:** manual: `__(`/`_e(` em classes marcadas como puras ou testáveis.

**Vista em:** plugin de pesquisas (questionários).

---

## Mudanças atrás de interruptor

### L-REL-38 · Leiaute novo atrás de interruptor, sem tocar o tema
**Sintoma:** risco de mudança visual sem como desligar; edição perdida quando o tema se atualiza sozinho.

**Causa:** editar tema ou template que se atualiza; mudança sem desligamento.

**Correção:** opção mais constante de emergência; desligado, nada é registrado nem enfileirado. `theme_mod_*` forçado por filtro só na requisição (nunca gravado com `set_theme_mod`); CSS escopado numa classe do `body`; chamadas à API do tema dentro de `try`; tag git de retorno antes de ligar.

**Detecção:** manual: `set_theme_mod(` em plugin.

**Vista em:** plugin de dados ligados.

### L-REL-39 · Funcionalidade "removida" atrás de flag, em camadas redundantes
**Sintoma:** pedido para retirar uma etapa do fluxo com casos ainda em andamento.

**Causa:** apagar o código quebraria pedidos pendentes e exigiria novo deploy para voltar.

**Correção:** opção desligada por padrão como fonte única; fechar em template, handler (HTTP 410 antes de checar login) e serviço; parar de induzir a etapa nos e-mails; manter as ações de encerramento para os casos abertos.

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário.

### L-REL-40 · Template ativo no banco, não no arquivo
**Sintoma:** editar o template em arquivo não mudava nada em produção.

**Causa:** o template ativo é um post de CPT; o arquivo só serve de semente.

**Correção:** editar o registro no banco, ou agir sobre o HTML renderizado (ex.: ajuste por regex para caber em 1 página, até 4 tentativas, gravando no disco só no fim). Registrar no contexto do repositório toda alteração feita direto no banco.

**Detecção:** manual: conferir de onde o template é lido antes de editar.

**Vista em:** plataforma de mapeamento comunitário.

---

## Documentação

### L-REL-41 · Não documentar à mão o que o núcleo já gera
**Sintoma:** risco de documentar à mão ganchos, phpDoc e OpenAPI, que envelhecem.

**Causa:** o Tainacan já gera essas referências: `repo:generate_docs.sh` e `repo:docs/` com `openapi.json` e `phpdoc` (pesquisa do núcleo, D.1); o workflow `generate-docs.yml` regenera a documentação em push na `develop` (D.6).

**Correção:** documentação manual só do que não é gerado (conceitos, receitas, decisões).

**Detecção:** manual.

**Vista em:** gerador de documentação com IA.

### L-REL-42 · Links "quebrados" na wiki Docsify: falsos positivos de caminho
**Sintoma:** centenas de links acusados como quebrados.

**Causa:** o verificador não tratava `\`, `../`, alias `/dev/`, código em bloco, nem imagens que só existem em `pt-br/_assets` ou `es-mx/_assets`.

**Correção:** verificador que resolve esses casos; o que sobra é real.

**Detecção:** manual: links de imagem para `_assets` inexistente no idioma.

**Vista em:** gerador de documentação com IA.
