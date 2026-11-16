# TNC-R02 · readme.txt ausente ou incompleto

**Severidade:** aviso (sempre) · **Categoria:** release · **Arquivos:** readme.txt na raiz do plugin (só com `tnc-check --release`)

## Sintoma
O envio ao WordPress.org é recusado ou o Plugin Check reclama do `readme.txt`. Quem instala pelo ZIP não sabe a versão mínima do WordPress ou do PHP, e o painel de plugins não mostra a compatibilidade.

## Causa
O WordPress.org lê o `readme.txt` para montar a página do plugin e decidir compatibilidade. Os campos `Requires at least`, `Tested up to`, `Requires PHP` e `License` são esperados no cabeçalho do arquivo.

## O que a regra detecta
- Ausência de `readme.txt` na raiz do plugin (um achado).
- `readme.txt` sem a linha `Requires at least:`, `Tested up to:`, `Requires PHP:` ou `License:` (um achado por campo ausente).
- Não confere os valores (por exemplo, se `Requires PHP` bate com o cabeçalho do plugin ou se `Tested up to` está atualizado).
- Não confere outras seções exigidas pelo WordPress.org, como "External services" para plugins que chamam serviços externos.

## Como corrigir
Crie ou complete o cabeçalho do `readme.txt`.

```
=== Meu Plugin ===
Contributors: equipe
Tags: tainacan, acervo
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
```

## Quando suprimir
As conferências de release não aceitam supressão em linha. Plugin interno que nunca será publicado nem instalado por ZIP pode conviver com o aviso, mas o `readme.txt` continua útil como registro de versões mínimas.

## Origem
Exigência do WordPress.org e do Plugin Check. Num plugin de biblioteca, o Plugin Check rodado sobre o repositório apontou mais de mil achados, entre eles exigências do `readme.txt` desconhecidas pela equipe.
