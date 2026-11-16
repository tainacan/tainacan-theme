# TNC-T05 · Repositório do Tainacan usado sem singleton ou sem validate()

**Severidade:** bloqueio para `new` de repositório; aviso para `insert()`/`update()` sem `validate()` e para `catch` vazio em volta deles · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
"Call to private ... __construct() from global scope" ao instanciar um repositório com `new`. Ou uma importação, coleta ou rotina em lote termina dizendo "sucesso", mas os itens não foram gravados ou ficaram com campos vazios: o `insert()` lançou `Exception('Entities must be validated before you can save them')` e alguém engoliu a exceção.

## Causa
Os repositórios usam o trait `Singleton_Instance`, com construtor privado e `get_instance()` estático (`classes/traits/class-tainacan-singleton-instance.php:12-28`); o acesso é por `Repositório::get_instance()` ou pelas funções `tainacan_items()`, `tainacan_metadata()` etc. (`classes/tainacan-loaders.php:9-82`). `Repository::insert()` lança a exceção quando o status está em `publish`, `future`, `private` ou `pending` (lista filtrável por `tainacan-status-require-validation`) e a entidade não foi validada (`classes/repositories/class-tainacan-repository.php:164-166`). `Item_Metadata::insert()` exige validação sempre, sem olhar o status (`classes/repositories/class-tainacan-item-metadata.php:30-34`). Rascunhos podem ser gravados sem `validate()`. `insert()` devolve uma nova instância da entidade (`classes/repositories/class-tainacan-repository.php:244`).

## O que a regra detecta
- `new \Tainacan\Repositories\X()`, ou `new Repositories\X()` em arquivo com `namespace Tainacan;` ou `use Tainacan\Repositories;`: bloqueio.
- `->insert( $var )` ou `->update( $var )` em repositório do Tainacan (chamada direta em `tainacan_items()` e similares, em `\Tainacan\Repositories\X::get_instance()`, ou em variável/propriedade atribuída a partir deles no mesmo arquivo) sem `$var->validate()` nem `$var->get_validated()` antes, na mesma função: aviso.
- Não confere o status: um rascunho gravado sem `validate()` também é acusado.
- Não confere se o resultado de `validate()` foi usado: `$item->validate(); $repo->insert( $item );` é aceito.
- A conferência de `validate()` não dispara quando o argumento não é uma variável simples (`->insert( $this->item )`, `->insert( new Item() )`).
- `catch` com corpo vazio (ou só com comentário) logo depois de um `try` que chama `insert()` ou `update()` de repositório do Tainacan (reconhecido como no segundo item desta lista): aviso, na linha do `catch`. Com vários `catch` em sequência, cada um vazio gera um achado. Aqui o argumento pode ser qualquer expressão (`->insert( $this->item )` também conta).
- Não dispara para `catch` que tem qualquer instrução no corpo, mesmo que não registre a falha de verdade (por exemplo, só `$ok = false;`), nem quando a gravação está numa função chamada dentro do `try`.

## Como corrigir
1. Use o singleton ou as funções de acesso.
2. Valide antes de gravar e trate a falha como erro contabilizado, com `get_errors()`.
3. Guarde a instância devolvida pelo `insert()`.
4. Não use `catch` vazio em volta da gravação; registre e conte a falha.

```php
// errado
$repo = new \Tainacan\Repositories\Items();
$item->set_status( 'publish' );
try { $repo->insert( $item ); } catch ( \Exception $e ) {}

// certo
$repo = \Tainacan\Repositories\Items::get_instance();
$item->set_status( 'publish' );
if ( $item->validate() ) {
    $item = $repo->insert( $item );
} else {
    $falhas[] = $item->get_errors();
}
```

## Quando suprimir
Gravação de rascunho (`draft`, `auto-draft`) que o núcleo aceita sem validação e que será validada antes de publicar: `// tnc-check-ignore TNC-T05: rascunho, validado na etapa de publicação`. Validação feita num método auxiliar que a regra não enxerga: cite o método no motivo. Não suprima `new` de repositório.

## Origem
Num plugin agregador, `set_document()` falhava em 100% dos itens coletados porque o `update()` era chamado sem `validate()`, e um `catch` genérico engolia a exceção enquanto a coleta reportava sucesso.
