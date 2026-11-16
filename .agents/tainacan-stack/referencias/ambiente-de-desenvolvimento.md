# Ambiente de desenvolvimento, testes e trabalho com agentes

Pegadinhas do ambiente local (Windows, XAMPP, PHP, WP-CLI), de SSH e contêineres, do ferramental de testes do núcleo do Tainacan, de testes de interface e de verificação honesta, mais as práticas de trabalho que se repetiram em vários projetos com agentes de IA.

## Sumário

- [Windows: codificação, arquivos e linha de comando](#windows-codificação-arquivos-e-linha-de-comando) (L-AMB-01 a 10)
- [PHP, WP-CLI e ferramentas locais](#php-wp-cli-e-ferramentas-locais) (L-AMB-11 a 20)
- [SSH e comandos remotos](#ssh-e-comandos-remotos) (L-AMB-21 a 25)
- [Contêineres e serviços](#contêineres-e-serviços) (L-AMB-26 a 29)
- [Núcleo do Tainacan: testes, lint e fluxo de contribuição](#núcleo-do-tainacan-testes-lint-e-fluxo-de-contribuição) (referência + L-AMB-30)
- [Testes de interface](#testes-de-interface) (L-AMB-31 a 33)
- [Testes e verificação honesta](#testes-e-verificação-honesta) (L-AMB-34 a 41)
- [Medir e conferir antes de agir](#medir-e-conferir-antes-de-agir) (L-AMB-42 a 44)
- [Agentes de IA](#agentes-de-ia) (L-AMB-45 a 47)
- [Práticas de trabalho recorrentes](#práticas-de-trabalho-recorrentes)

## Comece por aqui

1. L-AMB-01: PowerShell 5.1 corrompe UTF-8 e grava BOM; não use `Get-Content | Set-Content` em código.
2. L-AMB-02: ZIP feito com `Compress-Archive` quebra no Linux.
3. L-AMB-03: scripts `.sh` vindos do Windows em CRLF.
4. L-AMB-04: heredoc do shell come as barras invertidas de namespace PHP.
5. L-AMB-11: WP-CLI e ferramentas que embrulham PHP precisam do PHP certo no PATH.
6. L-AMB-21: SSH em rajada é bloqueado; uma sessão longa com script.
7. L-AMB-31: medir no navegador (computed style), não olhar a captura cedo demais.
8. L-AMB-34: log só com `WP_DEBUG` esconde bugs por meses.
9. L-AMB-42: conferir a versão instalada antes de tratar relato como defeito.
10. L-AMB-38: teste "simulado" que chama o provedor real.

Referência do núcleo usada aqui: `pesquisa-core-tainacan.md`, seções D.1 (estrutura), D.3 (testes), D.4 (lint) e D.7 (branches e PRs). `repo:` indica arquivo do repositório do Tainacan fora de `src/`. Pegadinhas de deploy (OPcache, `php -l` no contêiner, deploy por cima) estão em `release-e-deploy.md`.

---

## Windows: codificação, arquivos e linha de comando

### L-AMB-01 · PowerShell 5.1 corrompe UTF-8 e grava BOM
**Sintoma:** "Opções" vira "OpÃ§Ãµes" no arquivo editado; `fgetcsv` lê a primeira coluna com o BOM grudado no nome.

**Causa:** `Get-Content -Raw | Set-Content` lê e grava em ANSI; `-Encoding UTF8` do PowerShell 5.1 grava BOM.

**Correção:** editar código com ferramenta de edição de arquivo, ou .NET: `[IO.File]::ReadAllText($p)` e `[IO.File]::WriteAllText($p, $t, (New-Object Text.UTF8Encoding $false))`. Reverter a corrupção via Latin-1 só acerta os bytes 0xA0 a 0xFF: reticências, travessões e aspas curvas viram U+FFFD e precisam ser restaurados à mão (ou do git).

**Detecção:** TNC-B03 (BOM, UTF-8 inválido, mojibake); manual: `Set-Content`/`Out-File` em scripts que editam código.

**Vista em:** vários projetos no Windows.

### L-AMB-02 · `Compress-Archive` grava `\` nos nomes internos do ZIP
**Sintoma:** no servidor, o WordPress extraiu arquivos "planos" com nomes do tipo `pastaarquivo` ou com `\` no nome; em outro caso, nomes truncados (`vendor/autoload.php` virou `vendor/load.php`).

**Causa:** `Compress-Archive` do PowerShell 5.1 usa `\` como separador de entrada. O truncamento veio de um script próprio que fazia `substr( $path, strlen( $src ) )` com `$src` em caminho curto 8.3 e `$path` em caminho longo.

**Correção:** gerar o ZIP com `git archive`, `tar`, Python `zipfile`, .NET `ZipFile` ou PHP `ZipArchive` iterando a partir de `realpath( $src )`, sempre com `/`. Manifesto de distribuição; depois de gerar, listar as entradas e conferir hashes.

**Detecção:** manual: `Compress-Archive` em script de build ou release; `substr($path, strlen($src))` sem `realpath`.

**Vista em:** plugin de biblioteca, kit de memorial virtual, plugin de redirecionamento, deploy por painel do provedor de hospedagem.

### L-AMB-03 · Fim de linha CRLF em scripts e fontes
**Sintoma:** `$'\r': command not found` no servidor; scripts de deploy falhando; lint de JS cheio de avisos no Windows; `grep $'\r'` "não acha" CRLF que existe.

**Causa:** cópia de trabalho Windows com CRLF. Alguns repositórios têm PHP em CRLF de propósito. O executor de shell de certos agentes remove `\r` do comando, então `$'\r'` vira padrão vazio.

**Correção:**
- `.gitattributes` com `*.sh text eol=lf`; antes de enviar, `sed -i 's/\r$//' script.sh` ou `tr -d '\r' < script.sh | ssh host 'bash -s'`.
- Ao editar, manter o fim de linha que o arquivo já usa; não misturar. Ignorar avisos só de fim de linha.
- Para contar CR sem depender de `$'\r'`: `tr -cd '\015' < arquivo | wc -c`.

**Detecção:** manual: `*.sh` contendo `\r\n`; arquivos com finais de linha mistos.

**Vista em:** plugin de impressão de itens, plugin de processos, plugin de eventos e certificados em PDF, gerador de documentação com IA, vários projetos num servidor de testes.

### L-AMB-04 · Heredoc e `sed` comem barras invertidas de namespace PHP
**Sintoma:** `instanceof TainacanEntitiesItem_Metadata_Entity` foi para produção e todo metadado composto passou a ler vazio por duas versões; PHP gerado que não compila (namespaces com barra única ou nenhuma).

**Causa:** gerar ou corrigir PHP por heredoc do shell (`cat <<EOF`, `node - <<'EOF'`) ou `sed` com `'\\Tainacan\\...'`: o `\\` colapsa. Backtick dentro de `node -e "..."` vira substituição de comando. A ferramenta de shell de alguns agentes também trunca comandos acima de ~8 KB.

**Correção:** escrever PHP com ferramenta de edição de arquivo, ou gravar o script de patch num arquivo e executá-lo. Depois do patch, `grep -nE 'instanceof\s+Tainacan[A-Z][a-z]+[A-Z]'` e `php -l`.

**Detecção:** TNC-B01 quando o resultado não compila; manual: o grep acima e literais `'Tainacan[A-Z]\w+_` sem barra (o código compila e falha em silêncio).

**Vista em:** plugin de biblioteca, página pública do item por tipo de mídia, busca semântica por embeddings, plugin de narrativas, kit de memorial virtual.

### L-AMB-05 · Saída de `proc_open`/`exec` em CP850 no Windows
**Sintoma:** `?` ou acentos quebrados nas mensagens que voltam pela REST.

**Causa:** o console do Windows usa CP850 (ou Windows-1252); JSON espera UTF-8.

**Correção:** converter antes de devolver: `mb_convert_encoding( $saida, 'UTF-8', 'CP850' )` (com verificação `mb_check_encoding` para não converter o que já é UTF-8).

**Detecção:** manual: resultado de `proc_open`/`exec`/`shell_exec` devolvido em resposta REST sem conversão.

**Vista em:** plugin de OCR e busca em texto de PDFs/imagens (duas fontes).

### L-AMB-06 · Caminhos longos no git do Windows
**Sintoma:** clone ou checkout que falha (às vezes em silêncio) em repositórios com árvores profundas (`node_modules`, `vendor`, testes).

**Causa:** limite de 260 caracteres de caminho no Windows.

**Correção:** `git -c core.longpaths=true clone ...` (ou `git config core.longpaths true` no repositório); conferir `git status` depois do clone.

**Detecção:** manual: conferir se o checkout trouxe todos os arquivos.

**Vista em:** manutenção da própria stack de IA.

### L-AMB-07 · Limite de 32 KB da linha de comando
**Sintoma:** JSON grande não cabe no comando.

**Causa:** limite da linha de comando do Windows (e truncamento ainda menor em algumas ferramentas de shell de agente).

**Correção:** passar dados por arquivo ou stdin; se precisar ir no comando, gzip + base64.

**Detecção:** manual.

**Vista em:** plugin de pesquisas (questionários).

### L-AMB-08 · Windows preserva a data de criação ao sobrescrever
**Sintoma:** o pacote novo "parecia não ter sido salvo".

**Causa:** o Explorer mostra a data de criação do primeiro arquivo com aquele nome.

**Correção:** conferir data de modificação e conteúdo (hash); entregar com nome datado (`nome-AAAA-MM-DD.zip`).

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

### L-AMB-09 · Caractere proibido em nome de arquivo no Windows
**Sintoma:** PDFs sumindo em silêncio do ZIP gerado.

**Causa:** o dígito verificador do código podia ser `*`, proibido em nome de arquivo no Windows. Mudar o alfabeto do dígito invalida códigos já emitidos (a validação recalcula o dígito antes de ir ao banco) e exige módulo primo maior que o alfabeto.

**Correção:** sanitizar o nome do arquivo separadamente do código; se mudar o alfabeto, manter compatibilidade com os códigos antigos e testar contra trocas de caractere.

**Detecção:** manual: nomes de arquivo derivados de códigos sem remover `\/:*?"<>|`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-AMB-10 · Comparar caminhos de arquivo decodificados
**Sintoma:** `praça.jpg` nunca casava entre PHP e JS.

**Causa:** o WordPress grava e imprime o nome cru; o parser de URL do navegador devolve percent-encoded.

**Correção:** `rawurldecode` no PHP e `decodeURIComponent` no JS antes de comparar. Indexar o arquivo cheio e cada tamanho de `wp_get_attachment_metadata()` (um slide de imagem só carrega a URL de um tamanho).

**Detecção:** manual: comparação de `new URL(...).pathname` ou `a.href` com string vinda do PHP sem `decodeURIComponent`.

**Vista em:** kit de memorial virtual.

---

## PHP, WP-CLI e ferramentas locais

### L-AMB-11 · O PHP certo no PATH e no WP-CLI
**Sintoma:** ferramenta que embrulha PHP (phpcs, Composer, empacotador) devolve saída zero e "sucesso"; quase empacotou o phpunit para produção. No servidor, `wp` puro roda com outra versão de PHP, polui logs e se comporta diferente.

**Causa:** `php` fora do PATH (a ferramenta falha em silêncio); no servidor, o `wp` usa o PHP padrão do sistema, não o do site.

**Correção:** exportar o PATH no mesmo comando (`PATH=/c/xampp/php:$PATH composer ...`); no Git Bash, `MSYS_NO_PATHCONV=1` para argumentos que parecem caminho (URLs, `/wp-json/...`). No servidor: `sudo -u <usuário-do-site> php8.x /usr/bin/wp --path=...`. Saída vazia nunca é sucesso.

**Detecção:** manual: `php -v` e `wp --info` antes de confiar no resultado.

**Vista em:** plugin de pesquisas (questionários), servidor de testes compartilhado.

### L-AMB-12 · PHP local sem `ZipArchive`
**Sintoma:** validação local de ZIP ou XLSX impossível; código que funciona em produção falha local.

**Causa:** extensão `zip` ausente no PHP local e presente em produção.

**Correção:** `class_exists( 'ZipArchive' )` antes de usar, com mensagem clara; quando o plugin não pode depender da extensão, zip em fluxo próprio. Validar o arquivo gerado com .NET ou `tar -tvf`.

**Detecção:** manual: `new ZipArchive` sem `class_exists('ZipArchive')`.

**Vista em:** plugin de impressão de itens, plugin de pesquisas (questionários).

### L-AMB-13 · Testar WordPress sem servidor web
**Sintoma:** Apache e MySQL locais parados ou instáveis durante o desenvolvimento.

**Causa:** ambiente local instável (ver L-AMB-16).

**Correção:** bootar o WordPress pela CLI (`define( 'WP_USE_THEMES', false )`, `$_SERVER['HTTP_HOST'] = '...'`, `require 'wp-load.php'`); subir o MySQL em modo standalone; Chrome headless mais `pdfinfo`/`pdftotext` para conferir impressão; testes isolados com stubs mínimos do WordPress quando não há banco. Ainda assim, listar explicitamente o que não foi verificado em navegador real.

**Detecção:** manual.

**Vista em:** kit de memorial virtual, plugin de eventos e certificados em PDF.

### L-AMB-14 · PATH do Apache e dependências de OCR no Windows
**Sintoma:** ferramentas "não encontradas" depois de instaladas; erro com a limpeza de ruído do OCR.

**Causa:** o Apache só enxerga mudanças de PATH depois de reiniciado pelo painel do XAMPP; o `--clean` do OCRmyPDF exige `unpaper`, que não tem build oficial para Windows.

**Correção:** reiniciar o Apache pelo painel; desligar a limpeza no Windows; caminhos dos binários configuráveis na tela do plugin.

**Detecção:** manual: tela de diagnóstico que mostra o caminho e a versão de cada binário.

**Vista em:** plugin de OCR e busca em texto de PDFs/imagens.

### L-AMB-15 · `extension_loaded('mbstring')` ignora o polyfill
**Sintoma:** módulo bloqueado num servidor onde o plugin sempre funcionara.

**Causa:** `symfony/polyfill-mbstring`, carregado pelo `autoload_files` do Composer, supre as funções `mb_*` sem a extensão.

**Correção:** testar a capacidade (`function_exists` das funções usadas), não a extensão; `ext-mbstring` como `suggest` no `composer.json`.

**Detecção:** manual: `extension_loaded( 'mbstring' )` em plugin com `symfony/polyfill-mbstring` no `vendor/`.

**Vista em:** kit de memorial virtual.

### L-AMB-16 · Banco local quebrado: lint e teste puro verdes não provam a aplicação
**Sintoma:** suítes verdes sem tocar o banco; aplicação nunca exercitada.

**Causa:** o InnoDB do MariaDB local (XAMPP) não sobe (logs que não correspondem ao tablespace); consertar afeta todos os projetos da máquina.

**Correção:** segunda instância com `datadir` próprio em outra porta; validar no servidor de testes; declarar no relatório o que só passou em teste puro.

**Detecção:** manual: `wp db check` ou uma consulta real antes de declarar "testado".

**Vista em:** ambiente local XAMPP (vários projetos).

### L-AMB-17 · Não depurar pelo banco local quando o plugin roda remoto
**Sintoma:** diagnósticos errados.

**Causa:** o banco local não tem o plugin ativo nem os dados do servidor.

**Correção:** depurar no servidor de testes, com os dados de lá.

**Detecção:** manual.

**Vista em:** colaboração do público em metadados.

### L-AMB-18 · SQLite em WAL: não copiar só o `.sqlite`
**Sintoma:** backup sem os dados recentes.

**Causa:** em modo WAL, os dados recentes ficam no arquivo `-wal`.

**Correção:** API de backup do SQLite (`sqlite3 banco.sqlite ".backup destino.sqlite"` ou `Connection.backup()` em Python).

**Detecção:** manual: `cp`/`copy` de `*.sqlite` em scripts de backup ou deploy.

**Vista em:** gerador de documentação com IA.

### L-AMB-19 · Identidade git ausente sem alterar a configuração
**Sintoma:** commit falha por falta de `user.name`/`user.email`.

**Causa:** máquina sem identidade configurada e regra que proíbe o agente de alterar `git config`.

**Correção:** `GIT_AUTHOR_NAME`, `GIT_AUTHOR_EMAIL`, `GIT_COMMITTER_NAME` e `GIT_COMMITTER_EMAIL` no próprio comando.

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

### L-AMB-20 · API do GitHub não aceita senha (responde 404, não 401)
**Sintoma:** "repositório não encontrado" ao autenticar com usuário e senha.

**Causa:** autenticação por senha descontinuada na API desde 2021; repositório privado sem autenticação válida responde 404.

**Correção:** Personal Access Token (escopo `repo`); no GitLab, token `read_repository`; Bitbucket, App Password; Gitea, token.

**Detecção:** manual: `Authorization: Basic` com senha de conta em integração com o GitHub.

**Vista em:** ferramenta de deploy por Git.

---

## SSH e comandos remotos

### L-AMB-21 · Rajada de conexões SSH é bloqueada (fail2ban)
**Sintoma:** "Connection reset/refused" no meio do deploy; IP de origem bloqueado por 1 h depois de poucas falhas.

**Causa:** dezenas de invocações curtas de `ssh` ou tentativas repetidas; o fail2ban bane após algumas falhas e firewalls de rede também reagem à rajada.

**Correção:** uma sessão longa executando um script; agrupar os comandos pós-deploy numa única conexão (ou `ControlMaster`). Quando começar a recusar, parar, esperar a porta reabrir e rodar uma vez só.

**Detecção:** manual.

**Vista em:** catalogação assistida por IA local, ambiente de produção em contêineres, servidor de testes compartilhado.

### L-AMB-22 · Aspas aninhadas em comando remoto executam conteúdo
**Sintoma:** `&gt;` virou o comando `gt`; um comando chegou sem aspas e executou binários como root.

**Causa:** aspas simples dentro de argumento entre aspas simples; o PowerShell escapando mal `|` e `$` ao montar o comando.

**Correção:** escrever o script em arquivo e mandar por stdin ou cópia; nunca aninhar aspas.

**Detecção:** manual.

**Vista em:** plugin de dados ligados, servidor de testes compartilhado.

### L-AMB-23 · Texto acentuado digitado em comando remoto se corrompe
**Sintoma:** título com acentos corrompidos depois de um comando por SSH.

**Causa:** texto acentuado digitado na linha de comando remota (cliente SSH inline no Windows).

**Correção:** texto acentuado só por arquivo (`wp eval-file`) ou copiado do próprio banco por `SELECT`.

**Detecção:** manual: conferir o valor gravado depois.

**Vista em:** plugin de eventos e certificados em PDF, plataforma de mapeamento comunitário.

### L-AMB-24 · `ssh host 'bash -s' < script` e comando que lê stdin
**Sintoma:** o resto do script não roda depois de `wp plugin check`.

**Causa:** o comando lê a stdin, que é o próprio script, e consome o que vinha depois.

**Correção:** `</dev/null` nos comandos que podem ler stdin, ou deixá-los por último.

**Detecção:** manual: scripts enviados por `bash -s` com comandos interativos sem `</dev/null`.

**Vista em:** plugin de processos.

### L-AMB-25 · `find -exec php -l | grep -v` retorna 1 quando está tudo certo
**Sintoma:** o lint "falha" sem nenhum erro.

**Causa:** `grep -v "No syntax errors"` sem linhas de saída retorna 1.

**Correção:** contar os arquivos com erro num laço explícito e testar a contagem.

**Detecção:** manual: `php -l ... | grep -v "No syntax errors"` usado como condição.

**Vista em:** ambiente de produção em contêineres.

---

## Contêineres e serviços

Ver também `release-e-deploy.md`: `php -l` com o PHP do contêiner (L-REL-20) e réplicas atrás de balanceador (L-REL-22).

### L-AMB-26 · `wp db query` falha por TLS dentro do contêiner
**Sintoma:** "SSL is required, but the server does not support it".

**Causa:** o cliente `mysql` do contêiner exige TLS e o servidor de banco não oferece.

**Correção:** `wp eval`/`wp eval-file` com `$wpdb`. Para levar o arquivo para dentro do contêiner sem comando de cópia, base64 para `/tmp` do contêiner. Alguns runtimes (`crictl exec`) não têm `-e`: usar `env VAR=1 comando`.

**Detecção:** manual.

**Vista em:** ambiente de produção em contêineres, plugin de pesquisas (questionários), plugin de processos.

### L-AMB-27 · `/tmp` do contêiner não é o `/tmp` do host
**Sintoma:** arquivo gravado pelo PHP invisível pelo SSH; `rm` pelo SSH não apagava nada.

**Causa:** o PHP roda no contêiner; o SSH entra no host (ou no nó de arquivos).

**Correção:** trocar arquivos com o PHP por um volume compartilhado, como `WP_CONTENT_DIR`.

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário.

### L-AMB-28 · Plugin do Elasticsearch instalado à mão some no reinício do contêiner
**Sintoma:** `icu_tokenizer` ausente depois de reiniciar; a criação de índice do Koha falhou.

**Causa:** `analysis-icu` foi instalado no contêiner em execução, não na imagem.

**Correção:** instalar na imagem (`FROM elasticsearch:<versão>` + `elasticsearch-plugin install --batch analysis-icu`); deixar a aplicação criar o índice (não à mão); usuário dedicado restrito ao prefixo dos índices da aplicação, nunca o superusuário.

**Detecção:** manual: `GET _cat/plugins` depois de reiniciar.

**Vista em:** catálogo de biblioteca (Koha) com Elasticsearch.

### L-AMB-29 · Elasticsearch não sobe depois de reboot em máquina pequena
**Sintoma:** Elasticsearch inativo após reiniciar o servidor.

**Causa:** o systemd desiste em 75 s quando todos os serviços sobem juntos.

**Correção:** `TimeoutStartSec=600` no override da unidade; conferir `systemctl is-active elasticsearch` depois de cada reboot.

**Detecção:** manual.

**Vista em:** servidor de testes compartilhado.

---

## Núcleo do Tainacan: testes, lint e fluxo de contribuição

Referência (fonte: `pesquisa-core-tainacan.md` D.1, D.3, D.4, D.7).

- **Estrutura:** `src/` é o plugin; o resto não entra no pacote (`repo:docs/setup-local.md:9-14`). Raiz com `tests/`, `docs/`, `build.sh`, `run-tests.sh`, `generate_docs.sh`, `verify_compat.sh`, `phpcs.xml.dist`, `phpunit.xml.dist`, `eslint.config.js`; Composer com `"vendor-dir": "src/vendor"` (`repo:composer.json`).
- **PHPUnit:** `phpunit.xml.dist` usa `tests/bootstrap.php` e roda `tests/test-*.php`; arquivos com prefixo `_` ou `__` (ex.: `_test-logs.php`) ficam de fora (`repo:phpunit.xml.dist`). O bootstrap lê `tests/bootstrap-config.php` (cópia de `bootstrap-config-sample.php`, com `tests_dir => '/tmp/wordpress/wordpress-tests-lib'`) ou `WP_TESTS_DIR`, carrega `src/tainacan.php` em `muplugins_loaded` e dispara a ativação em `plugins_loaded` (`repo:tests/bootstrap.php:1-40`).
- **Rodar:** `./tests/bin/install-wp-tests.sh wordpress_test root "" /tmp/wordpress 127.0.0.1:3306 latest`, depois `mv tests/bootstrap-config-sample.php tests/bootstrap-config.php`, depois `phpunit` (é o que `run-tests.sh` faz; passos de `repo:.github/workflows/test.yml`).
- **Sem testes JS/e2e** no núcleo: nenhum jest, cypress, playwright ou vitest.
- **Lint:** PHPCS com `WordPress-Core` e `WordPress-Docs` (`repo:phpcs.xml.dist`); ESLint flat com `eslint-plugin-vue` `flat/recommended` e `no-console` como aviso (`repo:eslint.config.js:1-50`), rodando também no build do webpack. Scripts `composer phpcs`/`composer phpcbf`; `npm run lint`.
- **Branches (GitFlow):** `develop` é o branch padrão e de integração; produção é **`master`** (o `CONTRIBUTING.md` diz `main`, que não existe no GitHub). `feature/*` para tudo que não é urgente; `release/*` e `hotfix/*` só para mantenedores. Nunca commitar direto em `develop` ou `master`.
- **PR:** sempre contra `develop`, depois de `git rebase upstream/develop`. Commits em Conventional Commits (`feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `ci`), assunto com menos de 72 caracteres. Pelo menos uma aprovação; PR parado por 30 dias pode ser fechado.
- **Modelo de PR** (`repo:.github/pull_request_template.md`): issue relacionada, tipo de mudança, ESLint/PHPCS, testes, capturas de tela e atualização do `deprecated.js` quando `block.json` ou `save.js` de um bloco mudam (sem isso, blocos já salvos em posts viram "conteúdo inválido").

### L-AMB-30 · PHPUnit no Windows a partir de `tests/`
**Sintoma:** os testes não encontram o bootstrap.

**Causa:** caminhos relativos do `phpunit.xml.dist` resolvidos a partir do diretório atual no Windows.

**Correção:** `cd tests && ../vendor/bin/phpunit -c phpunit.xml.dist` (ou passar caminho absoluto para `-c`). No núcleo, o `vendor` está em `src/vendor` (`repo:composer.json`), então o binário é `src/vendor/bin/phpunit`.

**Detecção:** manual.

**Vista em:** plugin de narrativas.

---

## Testes de interface

### L-AMB-31 · Medição por CDP: esperar o CSS e medir, não olhar
**Sintoma:** botão "sem estilo" na captura; correção funcionando parecia quebrada; teste de presença dava falso positivo; a SPA não roteava.

**Causa:** captura feita assim que o elemento aparece, antes de o CSS aplicar. `innerText` devolve também o texto de span oculto. O CSS inline contém o nome da classe, então procurar a classe no HTML sempre acha. A primeira navegação por hash nem sempre dispara o roteador.

**Correção:** esperar (cerca de 3 s ou até `document.fonts.ready`) e conferir `getComputedStyle().display`/altura e `getBoundingClientRect()` antes de concluir; casar o elemento (`<div class="x">`), não a string da classe; reatribuir `location.hash` e esperar o seletor; captura com `clip` e `captureBeyondViewport`; contraste conferido por script.

**Detecção:** manual.

**Vista em:** plugin de biblioteca, plugin de impressão de itens.

### L-AMB-32 · Capturas de celular exigem emulação de dispositivo
**Sintoma:** captura em janela estreita cortava texto e enganava a avaliação; `--window-size=390` parecia overflow.

**Causa:** janela pequena sem emulação de viewport e DPR; no Windows, o Chrome headless tem largura mínima de janela de cerca de 500 px.

**Correção:** `Emulation.setDeviceMetricsOverride` com `mobile: true` (ex.: 375 × 812, DPR 2), ou um contêiner de 375 px na página de teste.

**Detecção:** manual.

**Vista em:** agregador arquivístico, painel público de indicadores.

### L-AMB-33 · Portão de tela com verde falso
**Sintoma:** a ferramenta de captura imprime "console limpo" fotografando a tela de login ou o estado de carregamento.

**Causa:** o login falhou sem abortar; a espera era por `main`, que o tema emite antes de o app montar.

**Correção:** sair com código diferente de zero se o login falhar; esperar o indicador de carregamento sumir; sempre olhar a imagem.

**Detecção:** manual.

**Vista em:** plugin de pesquisas (questionários).

---

## Testes e verificação honesta

### L-AMB-34 · Log só com `WP_DEBUG` esconde bugs por meses
**Sintoma:** três de quatro bugs silenciosos sobreviveram meses: o trabalho acontecia, o relato mentia.

**Causa:** a única forma de registrar falha era uma função de log que só grava com `WP_DEBUG` ligado, e ele fica desligado em produção.

**Correção:** contadores persistentes de falha (por metadado, por chamada de ingestão) exibidos na interface, independentes de `WP_DEBUG`; trilha de ingestão com uma linha por chamada, inclusive token inválido e payload vazio.

**Detecção:** manual: `if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { error_log( ... ) }` como único registro de falha em caminho de importação ou ingestão.

**Vista em:** agregador de acervos de vários sites, painel central de métricas e coletor.

### L-AMB-35 · Teste de interface em ambiente compartilhado tocou dado real
**Sintoma:** o teste marcou um item de revisão real.

**Causa:** teste rodando sobre os dados reais do ambiente.

**Correção:** contas e tarefas descartáveis, criadas e removidas no `finally`; cópia isolada do banco; desfazer tudo o que tocar. Testar também como não administrador, com contas sem permissão de edição em acervo real.

**Detecção:** manual.

**Vista em:** gerador de documentação com IA.

### L-AMB-36 · Endereços fictícios não recebem e-mail
**Sintoma:** notificações e "Perdeu a senha?" não chegavam às contas de teste.

**Causa:** domínios reservados (`example.org`, `.invalid`) são recusados pelo servidor de e-mail; o contêiner não tinha `sendmail`.

**Correção:** testar e-mail com conta de endereço real; configurar SMTP explícito quando o servidor não tem MTA.

**Detecção:** manual.

**Vista em:** plugin de processos.

### L-AMB-37 · Teste de concorrência mascarado pelo servidor
**Sintoma:** 8 pedidos paralelos passavam com teto de 4 vagas.

**Causa:** o servidor web (LiteSpeed) enfileira os pedidos, que quase não se sobrepõem.

**Correção:** baixar o teto para 1 para provar o 429; depois, conferir que nenhuma vaga ficou presa.

**Detecção:** manual.

**Vista em:** plugin de impressão de itens.

### L-AMB-38 · Teste "simulado" chamando o provedor real
**Sintoma:** chamadas reais pagas durante os testes.

**Causa:** o teste trocou só um provedor de IA por mock, e o configurado era outro.

**Correção:** mock no nível da camada de provedor, independente da configuração (todos os provedores substituídos). O teto de gasto só conta modelos com preço cadastrado: modelo sem preço fica fora do teto e precisa ser bloqueado em teste.

**Detecção:** manual: rodar a suíte sem rede ou sem chaves e exigir que passe.

**Vista em:** gerador de documentação com IA.

### L-AMB-39 · Estruturalmente válido não é funcional: testar contra vetores conhecidos
**Sintoma:** QR que nenhum leitor lê.

**Causa:** polinômio gerador Reed-Solomon invertido; a checagem estrutural não pega.

**Correção:** testar codificadores e formatos contra vetores de teste conhecidos da norma (e um leitor real).

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

### L-AMB-40 · Script que reescreve arquivo confere antes de gravar
**Sintoma:** arquivo de código zerado.

**Causa:** `file_put_contents( $f, preg_replace( ... ) )` com `preg_replace` devolvendo `null` (erro de regex ou backtrack).

**Correção:** abortar se a âncora não casou ou o resultado é `null`; comparar antes e depois; preferir ferramentas de edição.

**Detecção:** manual: `file_put_contents(\$\w+,\s*preg_replace(` sem checar `null`.

**Vista em:** plugin de pesquisas (questionários).

### L-AMB-41 · Antes de processar em lote: auditar pré-condições e medir defasagem
**Sintoma:** nenhum dos 89 participantes e 4 organizadoras era emissível e nada avisou antes do lote; em outro momento, reprocessamento inútil de documentos já emitidos "por precaução".

**Causa:** pré-condição invisível (carga horária zero bloqueava a emissão, em cascata participante → categoria → evento sem valor); regeração sem comparar a versão do modelo com a do documento.

**Correção:** auditoria de elegibilidade antes do lote; comparar `template_version` gravada × versão atual do modelo; renderizar prévia com dados reais antes de regerar em massa.

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

---

## Medir e conferir antes de agir

### L-AMB-42 · Conferir a versão instalada antes de tratar relato como defeito
**Sintoma:** funcionalidade "ausente" relatada como bug.

**Causa:** servidor de testes defasado em relação à `main` (o mecanismo de deploy apontava para um branch apagado).

**Correção:** confirmar versão e commit no servidor primeiro; conferir para qual branch o deploy aponta; em seguida confirmar qual callback ou rota realmente responde. Para 404 que "volta" depois de corrigido, pedir hard-refresh e a URL exata antes de investigar código. Ver também OPcache em `release-e-deploy.md` (L-REL-18).

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-AMB-43 · Medir o alcance real antes de investir
**Sintoma:** estimativa de 20% de alcance; o real era 1,9%.

**Causa:** "autoria preenchida" foi tomada por "autoria compartilhada entre instituições".

**Correção:** medir no dado real, com a mesma definição que a funcionalidade vai usar, antes de priorizar.

**Detecção:** manual.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-AMB-44 · Não prometer integração com API que não existe
**Sintoma:** promessa de integração com fontes nacionais que não têm API.

**Causa:** várias fontes só oferecem CSV ou páginas.

**Correção:** confirmar a API (e o acesso) antes de prometer; declarar os limites.

**Detecção:** manual.

**Vista em:** plugin de dados ligados e enriquecimento.

---

## Agentes de IA

### L-AMB-45 · Imagens grandes envenenam a sessão do agente
**Sintoma:** a API passa a recusar qualquer imagem nova.

**Causa:** com muitas imagens no histórico, o limite por imagem cai para 2000 px.

**Correção:** capturas com no máximo ~1600 px de largura; recomeçar a conversa quando acontecer.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-AMB-46 · Frentes paralelas precisam do contrato definido antes
**Sintoma:** duas frentes (login único e segundo fator) entregaram pontos de passagem incompatíveis.

**Causa:** cada agente definiu o seu protocolo.

**Correção:** o coordenador define o contrato de integração antes de dividir o trabalho; uma classe-ponte faz a junção.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-AMB-47 · Resumo de ferramenta troca dígitos
**Sintoma:** número de contato errado gravado como padrão no plugin.

**Causa:** o dado foi lido de um resumo gerado por ferramenta, não do HTML original.

**Correção:** números, contatos, IDs e datas vêm da fonte original, nunca de resumo.

**Detecção:** manual.

**Vista em:** plugin de agendamento.

---

## Práticas de trabalho recorrentes

Regras que se repetiram em vários projetos (consolidadas e sem duplicatas). Servem a qualquer plugin Tainacan ou WordPress trabalhado com agentes.

**Contexto e versionamento**
- A cada mudança entregue: código → testes → contexto do repositório (`AGENTS.md`/`CLAUDE_CONTEXT.md`: o porquê) → changelog (o quê) → commit e push no mesmo passo. Várias instâncias trabalham em paralelo; o que não está no repositório se perde ou é refeito (já houve versão numerada em duplicidade).
- Antes de mexer: ler o contexto do repositório, `git fetch`/`git pull` e `git status` (cópias locais ficam meses atrás; a produção pode ter avançado por outras mãos; preservar mudanças alheias).
- Branch de feature, merge `--no-ff`, revisão antes do merge; nunca commitar direto no branch principal; Conventional Commits explicando o porquê e a evidência medida; nunca push em repositório de terceiros sem confirmar.
- Tag git (ponto de retorno) e backup antes de mudanças grandes; registrar no contexto toda operação feita direto no banco, porque ela não aparece no git.

**Medir antes de agir**
- Medir antes de decidir (plano de execução, contadores do servidor, delta de `_stats` do Elasticsearch), não pelo tempo nem pela documentação; confirmar que um número antigo ainda descreve o sistema.
- O mesmo `WHERE` na medição e na aplicação; amostra dirigida ao caso, com controle (o caso que deveria estar bom).
- Exercitar a operação que falha, não uma vizinha (gravar, não só criar índice).
- Ensaiar em índice ou cópia temporária antes de tocar o real; apagar o temporário depois.
- Ao mudar um tipo, estado ou ramo, procurar todos os `switch`, `if` e seletores CSS que o alcançam; correção aplicada num caminho é procurada nos caminhos irmãos.

**Testar de verdade**
- Bugs só aparecem contra dados reais: testar no servidor de testes designado, com volume real, nunca em produção e nunca sobre dados reais de trabalho (contas e registros descartáveis, cópia do banco).
- Testar como não administrador.
- "Verde não diz nada sobre a tela": olhar a imagem ou o HTML renderizado; validar artefatos pelo conteúdo (texto extraído do PDF, contagem e hashes de entradas do ZIP).
- Para cada bug, um teste isolado (stub mínimo do WordPress + classe real) que falha antes e passa depois. Teste que trava um número não trava o invariante; testes de fonte analisam tokens, não texto.
- Conferir identidades de contagem a cada mudança de relatório (soma por status = total).
- Gatilho de teste de notificação é a interface; SQL só monta o cenário (escrita direta no banco não dispara ganchos).
- Scripts de teste e de prova fora do repositório e fora do webroot (apagar logo após o uso).

**Honestidade de cobertura**
- Cada entrega registra "verificado localmente", "não verificado" e "defeitos que só apareceram no site real"; listar explicitamente o que não foi visto em navegador ou ambiente real.
- Sem credencial para a visão logada: pedir validação a quem tem e marcar "não validado" até lá.
- Critério de aceite não é marcado sem evidência; divergência entre especificação e comportamento resolvida pela interpretação de menor impacto e registrada.
- Nada inventado: todo número vem de medição e toda referência é conferida na fonte; fato inferido não é gravado no catálogo.

**Revisão**
- Revisão em rodadas: implementação → revisão adversarial do diff (só leitura, modelo mais forte) → rodada com critérios ampliados (carga, segurança, PHP 7.4, convivência) medida em laboratório.
- Em revisão conjunta de vários plugins, começar pelo que expõe a API que os outros consomem.
- Diante de aviso de lint: refatorar > API canônica > `phpcs:ignore` com justificativa que sobreviva a revisão (nunca "is safe" ou "false positive").
- Validações mínimas antes de commit: `php -l` em todo arquivo alterado (e `php7.4 -l` se o plugin declara 7.4), phpcs com o ruleset do projeto, lint JS, build + `node --check`, testes; `wp plugin check` à parte, sobre o pacote.

**Segurança operacional e escopo**
- Backup verificado antes de qualquer operação em servidor, com caminho registrado e rollback testável; antes de apagar arquivo de produção, confirmar que é regenerável.
- Nunca apagar dado ou índice em produção sem rollback e sem autorização de quem mantém.
- Nada irreversível (e-mails, publicação, envio a terceiros) sem ordem explícita do responsável.
- Conferir o estado antes de mexer: outras pessoas editam os sites entre sessões.
- Credenciais nunca no repositório, na memória do agente ou redigitadas no chat; trocar as que circularam em texto puro; remover permissões amplas do agente quando fora de uso.
- Não ampliar o escopo para reorganizar código adjacente; confirmar antes de itens com impacto em privacidade, infraestrutura ou identidade visual.
- Antes de aceitar uma dependência grande, ver se ela é necessária; bibliotecas JS/CSS locais, sem CDN em runtime.
- Ao receber alerta de segurança, comparar o horário da observação com a data da correção antes de tratar como reincidência; falhas de segurança de terceiros vão por canal privado.
- Ordem de operações perigosas documentada e seguida (ex.: mesclar duplicados antes de qualquer reprocessamento em massa).
