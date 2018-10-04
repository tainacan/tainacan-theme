<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<!-- MENU DE ACESSIBILIDADE -->
	<div class="accessibility-bar compact-mode">
		<div class="accessibility-bar__container">
			<ul class="accessibility-shortcuts" role="menubar">
				<li role="menuitem"><a href="#content" accesskey="c"><span>c</span> Ir para o conteúdo</a></li>
				<li role="menuitem"><a href="#menu-menu-1" accesskey="m"><span>m</span> Ir para o menu</a></li>
				<li role="menuitem"><a href="#tainacan-search-header" accesskey="b"><span>b</span> Ir para a busca</a></li>
				<li role="menuitem"><a href="#footer" accesskey="r"><span>r</span> Ir para o rodapé</a></li>
			</ul>

			<ul class="accessibility-options" role="menubar">
				<li role="menuitem">
					<span>Fonte</span>
					<button type="button" class="button-text-minus" accesskey="5">A-</button>
					<button type="button" class="button-text-default" accesskey="6">A</button>
					<button type="button" class="button-text-plus" accesskey="7">A+</button>
				</li>
				<li role="menuitem">
					<span>Contraste</span>
					<button type="button" class="button-high-contrast" accesskey="8">Alto Contraste</button>
				</li>
			</ul>
		</div>
	</div>

	<!-- AVISO DE ERRO CASO O JS ESTEJA DESATIVADO OU NÃO ESTEJA FUNCIONANDO -->
	<noscript>
		<style>
			noscript {
				margin: 0;
				padding: 12px 15px;
				font-size: 18px;
				color: #000;
				text-align: center;
				display: block;
				background-color: #FFC107;
			}
		</style>

		<span>Seu navegador não tem suporte a JavaScript ou o mesmo está desativado.</span>
	</noscript>

	<nav class="navbar navbar-expand-md navbar-light bg-white menu-shadow px-0">
		<div class="container-fluid max-large px-0 margin-one-column" id="topNavbar">
			<?php echo tainacan_get_logo(); ?>
			<div class="btn-group ml-auto"> 
				<form class="form-horizontal my-2 my-md-0 tainacan-search-form" [formGroup]="searchForm" role="form" (keyup.enter)="onSubmit()">
					<div class="input-group">
						<label for="tainacan-search-header" class="sr-only">Digite o que procura</label>
						<input type="text" name="s" placeholder="<?php _e('Search', 'tainacan-interface'); ?>" class="form-control" formControlName="searchText" size="50" id="tainacan-search-header">
						<button type="submit" class="form-control-feedback tainacan-search-button"><span class="text-midnight-blue input-group-btn mdi mdi-magnify"></span></button>
					</div>
				</form>
			</div>
		</div>
	</nav>

	<a href="javascript:" id="return-to-top"><i class="mdi mdi-menu-up"></i><span class="sr-only">Voltar ao topo</span></a>
