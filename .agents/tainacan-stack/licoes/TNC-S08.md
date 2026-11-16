# TNC-S08 · Credencial gravada no código

**Severidade:** bloqueio para chave com formato conhecido; aviso para atribuição de senha, token ou segredo a um literal · **Categoria:** seguranca · **Arquivos:** .php, .inc, .js, .jsx, .ts, .tsx, .vue, .json, .yml, .yaml, .env, .ini, .sh, .py, .md, .txt, .xml, .conf, .sql

## Sintoma
Uma chave de API, token ou senha aparece no repositório, no ZIP publicado ou num arquivo de contexto de agente. Quem tem acesso ao código, ao histórico do git ou ao pacote passa a ter acesso ao serviço. Às vezes a chave é revogada pelo provedor ao ser detectada num repositório público.

## Causa
O valor foi escrito no código ou num arquivo versionado em vez de ser lido de constante do `wp-config.php`, variável de ambiente ou opção protegida. Remover a linha depois não basta: o segredo continua no histórico do git e em qualquer cópia já distribuída.

## O que a regra detecta
- Bloqueio, em qualquer linha: formato de chave Anthropic (`sk-ant-...`), OpenAI (`sk-...`, `sk-proj-...`), token do GitHub (`ghp_`, `gho_`, `ghu_`, `ghs_`, `ghr_`, `github_pat_`), chave AWS (`AKIA...`), chave Google (`AIza...`), token Slack (`xox[baprs]-...`) e cabeçalho de chave privada (`-----BEGIN ... PRIVATE KEY-----`).
- Aviso: atribuição de literal com 8 ou mais caracteres sem espaço a nome que contém `password`, `passwd`, `senha`, `secret`, `segredo`, `api_key`/`apikey`, `access_token`, `auth_token`, `client_secret` ou `private_key` (como variável, chave de array, JSON ou YAML).
- Não dispara quando o valor parece marcador (`xxx`, `***`, `changeme`, `your...`, `exemplo`, `placeholder`, `test`, `<...>`, `{...}`, `$...`, `%...`), é só letras minúsculas e sublinhado (nome de campo), é só maiúsculas, dígitos e sublinhado começando por letra (nome de constante ou de variável de ambiente, como `MEU_PLUGIN_API_KEY`), contém `&`, `=`, `?` ou `/` (trecho de URL ou caminho, como `&api_key=`), ou começa com `get_option`, `getenv`, `defined` ou `constant`.
- Não dispara em linhas com `__(`, `_e(`, `esc_html__`, `label`, `placeholder`, `description`, `nonce`, `_field` ou `_name`.
- Não dispara em arquivos sob `test/`, `tests/`, `fixtures/`, `example/`, `examples/`, `exemplo/`, `exemplos/`, em arquivos `.example`, `.sample` ou `.dist`, nem em linhas com mais de 2000 caracteres (código minificado).

## Como corrigir
1. Revogue a credencial no provedor: considere-a vazada.
2. Remova do código e, se o repositório for compartilhado, do histórico do git.
3. Leia de constante do `wp-config.php` ou de variável de ambiente; se precisar guardar em opção, cifre e nunca devolva o valor à tela ou à API.

```php
// errado: a chave real escrita no arquivo
$api_key = '<chave real colada aqui>';

// certo
$api_key = defined( 'MEU_PLUGIN_API_KEY' ) ? MEU_PLUGIN_API_KEY : getenv( 'MEU_PLUGIN_API_KEY' );
```

## Quando suprimir
Valor que tem formato de credencial mas é público por natureza, como uma chave de API de navegador restrita por domínio e documentada como pública: `// tnc-check-ignore TNC-S08: chave pública de mapa, restrita por referer no provedor`. Exemplos em documentação devem usar marcador evidente (`sua-chave-aqui`), não suprimir.

## Origem
Credenciais de serviços circularam em arquivos de contexto, configurações e memória de agentes da equipe, e credenciais de um índice de busca acabaram legíveis em opção do banco. A regra também segue a boa prática geral de manter segredos fora do código.
