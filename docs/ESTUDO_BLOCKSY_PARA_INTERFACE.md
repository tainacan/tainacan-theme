# Estudo: transferir um leiaute feito no Blocksy para o Tainacan Interface

Data: 07/11/2026. Base: Tainacan Interface 4.3.0 e os 7 sites em Blocksy da VPS de testes, lidos direto do banco (ajustes do tema, menus, widgets, CSS adicional e plugins). Nenhum site foi alterado.

## 1. A pergunta

Um site montado no Blocksy pode passar a usar o Tainacan Interface sem perder a cara que tem? O que vem sozinho, o que se refaz à mão e o que não tem equivalente?

"Leiaute" aqui são quatro coisas, que se comportam de forma diferente na troca:

| Parte | Onde mora no Blocksy | O que acontece na troca |
|---|---|---|
| Conteúdo das páginas e posts | blocos do editor (Gutenberg, Stackable) | **fica igual**: é do site, não do tema |
| Ajustes de aparência | `theme_mods_blocksy` (cores, tipografia, cabeçalho, blog...) | **não passam sozinhos**: cada tema tem os seus nomes |
| Menus e widgets | locais e áreas com nomes do Blocksy | o WordPress tenta casar os menus pelo nome; os widgets vão para "inativos" |
| CSS adicional | um post por tema (`custom_css`) | **fica no Blocksy**: o Interface começa sem ele, e as classes `.ct-*` não existem no Interface |

## 2. O que existe na VPS

| Site | Tainacan | Ajustes do Blocksy | Construtor de cabeçalho | CSS adicional | Observação |
|---|---|---|---|---|---|
| aqua-lobster | 1.3.0 | 31 | padrão | 0,3 kB | laboratório Tainacan; largura 1900 px, moldura |
| brown-vulture | 1.3.0 | 22 | padrão | 0 | usa o plugin **Tainacan Support for Blocksy 0.4.4**; paleta laranja, logotipo |
| marcozero (tema filho) | 1.2.0 | 72 | personalizado, cabeçalho transparente | 0 | site institucional; Elementor ativo |
| darkturquoise-curlew | não | 50 | personalizado (botão, redes, busca) | **20,5 kB** | site das Oficinas; tipo de post próprio `odc_oficina` com ajustes do Blocksy |
| darkslateblue-lemur, demo-crm | não | 2 | padrão | 0,5 kB | site comercial do CRM; não é acervo |

Os dois primeiros são acervos e quase não personalizam o Blocksy. Os três últimos são sites de outra natureza: o Interface é um tema para acervos Tainacan, e trocar o tema deles não traz ganho.

## 3. O que tem equivalente no Interface 4.3

Comparado opção por opção com os arquivos de opções do Blocksy 2.1.44 (`inc/options/` e `inc/panel-builder/`):

| Blocksy | Tainacan Interface | Fidelidade |
|---|---|---|
| Paleta global (color1 a color8) | cor principal, de destaque, texto, fundo da página, fundo dos cartões | alta: color1 → destaque, color4 → principal, color3 → texto, color7 → fundo, color8 → cartões |
| Tipografia base e títulos (família, peso, tamanho, altura da linha, espaçamento, maiúsculas) | fontes, peso, tamanho (%), espaço entre letras, maiúsculas, altura da linha, tamanho no celular | média: o Interface tem 5 fontes locais (Poppins, Roboto, Lora, sistema, Georgia), sem Google Fonts; outra família cai na mais próxima |
| Largura máxima, espaço do conteúdo, largura estreita, moldura | largura da página, espaço entre as partes, largura estreita, moldura | alta |
| Botões (raio, estilo) e campos (tipo, borda, raio) | forma e estilo dos botões, três estilos de campo | média: o Interface tem formas fixas, não valores livres |
| Voltar ao topo (forma, lado, celular) | igual | alta |
| Trilha (separador, ícone de início) | igual | alta |
| Blog: colunas, tipo de cartão, proporção da imagem, resumo, barra lateral | lista ou cartões em 2 a 4 colunas, proporção, palavras do resumo, barra à direita/esquerda/nenhuma, largura, fixa | alta |
| Post: estrutura (largura estreita), imagem destacada, compartilhar, navegação, título com imagem (hero) | texto estreito, cada parte liga/desliga, barra de progresso, banner com imagem/simples/nenhum, altura e alinhamento | média: sem caixa de autor nem posts relacionados no blog |
| Cabeçalho: logotipo e menu na mesma linha ou empilhados, botão, redes, busca, menu lateral no celular | cabeçalho na cor principal/destaque/escuro/claro, empilhado ou em linha, botão com texto e endereço, redes, busca, painel lateral | média |
| Rodapé: menu, redes, copyright, áreas de widgets | menu do rodapé, redes, linha com {year} e {site}, rodapé compacto ou com widgets | média: uma linha de widgets, não 6 colunas configuráveis |
| Redes sociais (contas da instituição) | 8 redes, no cabeçalho, no rodapé ou nos dois | alta |
| Desempenho: emoji, carregamento preguiçoso | emoji; o carregamento preguiçoso é do WordPress | alta |
| Tainacan Support for Blocksy: ajustes por tipo de item (`tnc_col_<id>_item_single_*`) | ajustes por coleção no formulário dela (leiaute do item, seções, cores, prévia, menu) | média: outro conjunto de opções, mesma ideia |

## 4. O que não tem equivalente

- **Construtor de cabeçalho e rodapé por arrastar**: três linhas, vários elementos em qualquer posição, cabeçalho transparente sobre o banner, cabeçalho que encolhe ao rolar. O Interface oferece arranjos fixos.
- **Seis áreas de widgets no rodapé** com colunas ajustáveis.
- **Valores por aparelho** (celular, tablet, computador) em quase toda opção. O Interface tem poucos: tamanho do texto no celular, menu e botão de topo.
- **Caixa de autor, posts relacionados, efeito de revelação dos cartões** do blog.
- **Opções por tipo de post próprio** (ex.: `odc_oficina_*` das Oficinas).
- **Recursos do Blocksy Pro e do Companion**: blocos de conteúdo, ganchos, mega menu, condições.
- **CSS adicional escrito para as classes do Blocksy** (`.ct-header`, `.entry-card`...): não se traduz, se reescreve.

Ficaram de fora do Interface de propósito, porque pesam mais do que ajudam num site de acervo, ou dependem das listas do Tainacan acompanharem: o construtor por arrastar, o mega menu, o modo escuro e o valor por aparelho em toda opção.

## 5. Impacto da troca

**Para o visitante.** O conteúdo e os endereços ficam iguais. Mudam cabeçalho, rodapé, tipografia e, nos acervos, a lista e a página do item, que passam a ser as do Interface (com a prévia lateral, o "Como citar", a coluna "Continue explorando" e o cabeçalho da coleção). Num acervo isso é o ganho principal: o Blocksy mostra o Tainacan com o leiaute genérico do tema, e o Interface foi feito para ele.

**Para o administrador.**
- O Blocksy, o Blocksy Companion e o Tainacan Support for Blocksy deixam de ser usados (desativar, não apagar, para poder voltar).
- Menus: o WordPress casa os locais pelo nome na troca; o que não casar se escolhe em Aparência > Menus (`menu_1` → menu principal, `footer` → menu do rodapé).
- Widgets: a barra lateral `sidebar-1` corresponde a `tainacan-sidebar-right`; as áreas do rodapé `ct-footer-sidebar-1..6` se juntam em `tainacan-sidebar-footer`.
- CSS adicional: começa vazio no Interface; o do Blocksy continua guardado para consulta.

**Volta atrás.** A troca é reversível: os ajustes do Blocksy ficam em `theme_mods_blocksy`, intocados. Reativar o Blocksy devolve o site como estava.

**Desempenho.** O Interface 4.3 não carrega jQuery nem Bootstrap; as opções são variáveis CSS e classes no `body`, sem pedido a mais. A medida comparada fica para o teste do item 7.

## 6. Viabilidade por site

| Site | Viabilidade | Por quê |
|---|---|---|
| aqua-lobster | **alta** | acervo, poucos ajustes, quase nenhum CSS; o mapeamento automático cobre quase tudo |
| brown-vulture | **alta** | acervo, paleta e logotipo; o plugin Tainacan Support for Blocksy sai e os ajustes por coleção do Interface entram no lugar |
| marcozero | baixa | site institucional com cabeçalho transparente e Elementor; perderia o cabeçalho que tem, sem ganho de acervo |
| darkturquoise-curlew | baixa | 20,5 kB de CSS para as classes do Blocksy e o tipo de post das Oficinas com ajustes próprios; a troca é refazer o site |
| darkslateblue-lemur, demo-crm | não se aplica | não são acervos |

## 7. Como fazer, se for adiante

1. **Importação com consentimento.** O Interface já oferece, na ativação, trazer os ajustes de um tema derivado dele (aviso com botão). Estender esse aviso ao Blocksy: ao ativar o Interface vindo do Blocksy, o administrador vê o que será trazido e confirma. Nada muda sem o botão, como o diretório de temas exige. A tabela da seção 3 vira uma função de mapeamento (paleta, tipografia, largura, moldura, botão de topo, blog, post, redes, botão do cabeçalho, copyright). Estimativa: 1 a 2 dias, com teste nos dois acervos.
2. **Lista do que não veio**, mostrada depois da importação: o construtor do cabeçalho (com o que estava em cada linha), as áreas de rodapé, o CSS adicional e as opções sem equivalente, para o administrador decidir.
3. **Ensaio** numa cópia do aqua-lobster e do brown-vulture: capturas antes e depois de cada página típica (início, lista de coleções, coleção, item, post), medida de tamanho e pedidos, e a volta ao Blocksy.
4. **Decisão da equipe**: se a importação do Blocksy entra no tema (código que só serve na troca) ou vira um plugin de migração à parte. Pela regra de território de plugin do diretório de temas, ajustes de outro tema lidos uma vez, com consentimento, são aceitos como parte da ativação; ainda assim, um plugin à parte deixa o tema mais leve.

## 8. Conclusão

Para **acervos** em Blocksy, a troca é viável e vale a pena: o que esses sites personalizam no Blocksy tem equivalente no Interface, e o que eles ganham (lista, prévia, página do item e coleção feitas para o Tainacan) é maior do que o que perdem (o construtor por arrastar, que eles quase não usam). Para **sites que não são acervo** ou que dependem do construtor do Blocksy e de CSS próprio, a troca não compensa.
