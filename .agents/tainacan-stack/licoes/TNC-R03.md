# TNC-R03 · Changelog ausente ou sem a versão atual

**Severidade:** aviso (sempre) · **Categoria:** release · **Arquivos:** CHANGELOG.md, changelog.md, CHANGELOG.txt, changelog.txt, seção `== Changelog ==` do readme.txt (só com `tnc-check --release`)

## Sintoma
Quem atualiza o plugin não sabe o que mudou, e uma regressão leva mais tempo para ser localizada. A aba "Changelog" do WordPress.org fica vazia ou parada numa versão antiga.

## Causa
A versão subiu no cabeçalho sem registro do que mudou. Em plugins mantidos por várias pessoas (e por agentes), o changelog é a forma mais rápida de saber o que cada versão trouxe e o que pode ter quebrado.

## O que a regra detecta
- Nenhum changelog: nem `CHANGELOG.md`, `changelog.md`, `CHANGELOG.txt`, `changelog.txt` na raiz, nem seção `== Changelog ==` no `readme.txt`.
- Changelog que não contém o texto da versão do cabeçalho `Version:`.
- A busca é por substring: com a versão `1.2`, um changelog que só cita `1.2.3` é aceito.
- Só roda quando o cabeçalho tem `Version:`.

## Como corrigir
Acrescente uma entrada para a versão atual antes de publicar, descrevendo o que mudou.

```
== Changelog ==

= 1.4.3 =
* Corrige a contagem por termo quando a coleção tem termos filhos.
* Ganchos de admin passam a devolver string.
```

## Quando suprimir
As conferências de release não aceitam supressão em linha. Se o changelog fica em outro lugar (por exemplo, nas notas de release do GitHub), mantenha ao menos a seção `== Changelog ==` do `readme.txt` com a versão atual.

## Origem
Boa prática geral de publicação de plugins. Um plugin de processos registrou como regra de trabalho "changelog a cada versão", junto com a conferência de versões (ver TNC-R01).
