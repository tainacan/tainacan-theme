# Estudo de impacto e viabilidade: Tainacan Interface oficial → 4.2.2 nos sites da VPS

07/11/2026. Base: levantamento nos sites da VPS (tema, ajustes, plugins, mu-plugins) e leitura do código do tema (ramo `feature/interface-4.0`, 4.2.2) e do TainacanBiblio.

## 1. Onde o tema roda hoje

| Site | Tema hoje | Tainacan | Itens publicados | Ajustes do Personalizar | O que mais encosta no tema |
|---|---|---|---|---|---|
| catalogacao | **4.2.2** (já trocado) | 1.4.0 | — | 1 KB | vitrine da 4.x |
| bpb | 2.8.3 oficial | 1.3.0 | 49.359 | logo, cores, rodapé, endereço, menu abaixo do cabeçalho, visão "records" | **mu-plugin `bpb-portal`** (CSS sobre o leiaute clássico), TainacanBiblio |
| darkgreen-yak | 2.8.3 oficial | 1.3.0 | 2.026 | esquema "mintgreen", cabeçalho por blocos, 5 widgets na lateral | TainacanBiblio, Blocksy instalado (inativo) |
| memorial | 2.7.11 oficial | **1.0.3** | 2.067 | logo, imagem de cabeçalho, colunas da ficha, CSS adicional (1 KB) | Max Mega Menu, toolkit, indicators |
| gestao | 2.10.0 oficial | 1.3.0 | 22.960 | nenhum | Obatalá (tela própria) |
| arquivo | 2.10.0 oficial | 1.4.0 | 207 | nenhum | Tainacan Arquivo |

Os cinco têm o tema **idêntico ao pacote oficial** do WordPress.org (comparado arquivo a arquivo): não há edição local a perder e não há tema filho.

## 2. Como a troca acontece

A 4.x mantém a identidade do oficial (pasta `tainacan-interface`, prefixo `tainacan_`, mesmos `theme_mods`). A troca é uma **atualização no lugar**, igual à que o WordPress.org faria:

- Na atualização, `inc/upgrade.php` grava `design = classic` e mostra um aviso. **O visitante continua vendo o leiaute de hoje**; o Bootstrap foi trocado por `foundation.css` com as mesmas classes e medidas, conferido em pixels contra a 3.0.3.
- O design novo é escolha posterior, por site, no Personalizar (e por coleção).
- Publicação: `deploy.sh` com uma linha `auto:tema` por site em `projetos.map`. O script guarda a versão anterior e desfaz sozinho se o site der fatal. Voltar atrás = republicar o oficial na mesma pasta; os ajustes não mudam de lugar.

## 3. Compatibilidade verificada

- **Núcleo do Tainacan:** o tema chama 38 funções do núcleo; todas existem no 1.3.0 e no 1.0.3 (as 2 "ausentes" na varredura são nomes dinâmicos do próprio tema). Requisitos: WordPress 6.5+, PHP 7.4+ (a VPS usa 8.2).
- **TainacanBiblio:** usa classes próprias (`tbf-`, `tb-`), sem Bootstrap; os scripts declaram `jquery` como dependência, então o jQuery continua carregando nas telas dele mesmo no design novo, que não carrega jQuery por conta própria. O CSS das listas mira o markup do bloco de busca do Tainacan (independe do tema) e `.page-header-back` / `.page-header-content-title` de `template-parts/headertaxonomy.php`, que segue na 4.x. O gancho `tainacan-interface-term-description` continua disparado.
- **Ajustes:** as chaves `tainacan_*` de hoje são lidas pela 4.x. A trilha 2.10.0 → 3.0 → 4.x foi exercida no catalogacao.

## 4. Riscos

| Risco | Onde | Peso | Como tratar |
|---|---|---|---|
| Salto 2.8.3 → 4.x e 2.7.11 → 4.x **nunca testado** (só 2.10.0 → 4.x) | bpb, darkgreen-yak, memorial | alto | ensaio em cópia antes (seção 5) |
| `bpb-portal` estiliza `#menubelowHeader`, `.tainacan-footer`, `.tainacan-logo` (seguem na 4.x) e `.tainacan-search-container`, `.tainacan-breadcrumb` (não existem mais) | bpb | médio no clássico, alto se ligar o design novo | conferir a home e o rodapé no ensaio; ajustar o CSS do mu-plugin |
| bpb está em uso com público e equipe reais (balcão, portal) | bpb | alto | trocar fora do horário de atendimento, depois do treinamento |
| Cabeçalho por blocos (`tainacan_use_block_template_parts_on_header`) e widgets laterais | darkgreen-yak | médio | conferir no ensaio |
| Max Mega Menu sobre o menu do tema; Tainacan 1.0.3 antigo; CSS adicional | memorial | médio | é cópia de produção de terceiros: só serve de ensaio, a produção segue manual |
| Pendências do próprio tema: licença/autoria (Contributors), Theme URI, `es_ES`, ativação em site limpo não testada, 2 avisos do Theme Check | todos | médio (institucional) | decidir antes de levar a site de terceiros |
| Fora do canal oficial: o WordPress.org pode oferecer a 2.10.x por cima da 4.x | todos | médio | manter a atualização automática do tema desligada nesses sites até a equipe do Tainacan adotar a 4.x |

## 5. Ganhos

- Correções de segurança da 3.0.1 (navegação `?pos=` filtrada, "mostrar mais" sem reinterpretar HTML) — hoje bpb e darkgreen-yak rodam a 2.8.3, sem elas.
- Sem Bootstrap; no design novo, sem jQuery do tema (página pública mais leve).
- Design novo opcional, ajustável por coleção, com menus por coleção, prévia em gaveta, leitor de PDF na página, ficha em destaque.

## 6. Esforço

| Etapa | Esforço |
|---|---|
| Linha em `projetos.map` e deploy por site | minutos por site |
| Ensaio em cópia (bpb sem leitores, darkgreen-yak) com comparação de telas antes/depois | 1 dia |
| Ajuste do CSS do `bpb-portal` | 1–2 h |
| Ligar o design novo e configurar por site (opcional) | ½ dia por site, com revisão visual |
| Fechar pendências institucionais do tema | depende da equipe do Tainacan |

## 7. Recomendação

Viável, com a troca no **design clássico** (o visitante não percebe) e nesta ordem:

1. **arquivo** e **gestao** (sem ajustes, dados de teste): piloto direto.
2. **darkgreen-yak**: depois do ensaio, por causa do cabeçalho por blocos.
3. **bpb**: ensaio numa cópia sem dados pessoais, com fotos antes/depois da home, busca, item, balcão e área do leitor; trocar fora do atendimento; manter a 2.8.3 guardada para voltar.
4. **memorial**: só como ensaio da trilha 2.7.11; a produção do Memorial segue pelo usuário.

Design novo: decidir site a site depois, com revisão visual — é aí que o `bpb-portal` precisa de ajuste.
