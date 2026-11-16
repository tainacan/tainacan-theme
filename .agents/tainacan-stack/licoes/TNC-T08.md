# TNC-T08 · Página de admin do Tainacan sem load_page()

**Severidade:** aviso (sempre) · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
A página de admin do plugin, criada estendendo `\Tainacan\Pages`, abre no menu do Tainacan sem o CSS e o JavaScript próprios: leiaute quebrado, componentes Vue que não montam, botões que não fazem nada.

## Causa
`\Tainacan\Pages` liga `admin_menu` e `admin_head` no `init()` (`views/class-tainacan-pages.php:47-52`), mas não chama `load_page()` sozinho; é o `load_page()` que enfileira CSS e JS (`views/class-tainacan-pages.php:63-70`). As páginas nativas fazem `add_action( 'load-' . $page_suffix, [ $this, 'load_page' ] )` logo depois de `add_submenu_page()` (`views/admin/class-tainacan-admin.php:48`, `views/settings/class-tainacan-settings.php:46`). O construtor é `protected` (`views/class-tainacan-pages.php:36-38`), por isso as subclasses usam o trait `Singleton_Instance` (`views/admin/class-tainacan-admin.php:7-8`).

## O que a regra detecta
- Classe que estende `\Tainacan\Pages` (ou `Pages` em arquivo com `use Tainacan\Pages;` ou `namespace Tainacan;`) cujo código não tem, ao mesmo tempo, a string `'load-'` concatenada com `.` (como em `'load-' . $page_suffix`) e a string `'load_page'`. Comentários não contam.
- `"load-{$suffix}"` e `"load-$suffix"` interpolados também valem.
- A verificação é textual: as duas strings podem estar em qualquer lugar do corpo da classe, sem que a regra confira se estão no mesmo `add_action`.
- Não analisa a classe-mãe: uma subclasse intermediária que já liga `load_page()` não evita o achado nas filhas.

## Como corrigir
Guarde o sufixo devolvido por `add_submenu_page()` e ligue `load_page()` a ele.

```php
// errado
public function add_admin_menu() {
    add_submenu_page( 'tainacan_admin', 'Meu plugin', 'Meu plugin', 'manage_tainacan', $this->get_page_slug(), [ $this, 'render_page' ] );
}

// certo
public function add_admin_menu() {
    $suffix = add_submenu_page( 'tainacan_admin', 'Meu plugin', 'Meu plugin', 'manage_tainacan', $this->get_page_slug(), [ $this, 'render_page' ] );
    add_action( 'load-' . $suffix, [ $this, 'load_page' ] );
}
```

## Quando suprimir
Página que não usa os recursos do Tainacan e enfileira os próprios assets de outro jeito, ou subclasse cuja classe-mãe do plugin já liga `load_page()`: `// tnc-check-ignore TNC-T08: load_page ligado na classe-mãe Base_Page`.

## Origem
Páginas de admin de plugins da equipe abriam sem CSS e JS próprios até ligar `load_page()` manualmente; o mesmo aprendizado (construtor protegido e `load_page()` manual) foi registrado num plugin de processos.
