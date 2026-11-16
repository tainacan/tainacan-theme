# Busca segmentada no tema? Estudo (30/10/2026)

Pergunta: pôr a Busca Segmentada (plugin `tainacan-segmented-search` 0.2.3) dentro do tema, como recurso configurável e desligável.

O plugin mostra, acima dos resultados de uma busca, quantos itens o termo achou em cada metadado ("12 em Autor, 40 em Assunto…", com os valores mais comuns). Mostra também, em cada item, "Encontrado em" com o trecho. Tem dois motores: SQL e Elasticsearch, este pelo Index Manager. Tem ainda correção de palavra digitada errado, cache, limite por IP e uma tela de configurações.

## Medição

O plugin foi instalado só para a medição e removido depois. As medidas são com cache frio, visitante anônimo e servidor PHP (wp eval).

| Site | Acervo | Lista nativa (a busca que a página já faz) | Recortes, SQL | Recortes, índice (ES) | "Encontrado em" (12 itens) |
|---|---|---|---|---|---|
| catalogacao | 49 itens, 19 mil postmeta | 15–31 ms | 15–52 ms (5 consultas) | — | 9–24 ms |
| bpb | 29,8 mil itens, 908 mil postmeta | 27–472 ms | **1,0–1,8 s** (19 consultas) | 0,55–0,74 s (uma vez: timeout de 1,5 s no índice frio) | 79–692 ms |
| brasiliana3 (medido em 09/2026) | ~42 mil itens, 13 M postmeta | — | **6–73 s** | 0,3–0,7 s | — |

- **Pela REST, com o WordPress inteiro:**
  - bpb: 0,8–2,2 s no primeiro pedido e ~0,36 s no repetido (cache de 120 s para anônimos);
  - catalogacao: 0,17–1,75 s, quase tudo inicialização do WordPress na VPS (há disputa de CPU no hospedeiro).
- **No navegador**, só nas páginas de lista:
  - `segments.js` 28 kB (8,9 kB gzip) e `segments.css` 12 kB (3,2 kB gzip);
  - 1 pedido REST por busca e 1 por página de resultados;
  - sem busca, o script sai cedo.
  - O Vue (148 kB) é só da tela de configurações, no painel.

**Leitura dos números.**
- Em acervo pequeno, o custo é desprezível.
- Em acervo médio sem índice, cada busca nova custa de 2 a 40 vezes a própria lista: 1 a 2 s a mais no servidor, mesmo com a lista vindo antes, porque o painel chega por pedido separado.
- Em acervo grande sem índice, é inviável, e o plugin depende do Index Manager.

## Recomendação: não pôr no tema; integrar

1. **Ver o que não é do tema.** O WordPress.org recusa temas com funcionalidade "de plugin" (motor de busca sobre os dados, rotas REST de dados, tela de configurações própria). A meta é a equipe do Tainacan publicar este tema lá, então embutir 4 mil linhas de motor de busca põe essa aceitação em risco. A gaveta já tem uma rota REST, e isso já é o limite do razoável.
2. **O custo depende do acervo e do Index Manager, que é outro plugin.** Um interruptor no Personalizar deixaria um administrador de acervo grande ligar algo que custa de 1 a 70 s por busca sem índice. O plugin já decide o motor com essa informação ("auto" só usa o índice quando o Index Manager roteia a lista) e avisa na aba "Motor".
3. **Serve a qualquer tema.** A Brasiliana usa Blocksy, e o plugin já se adapta ao Tainacan Interface (`.tss--tainacan`, variáveis `--tainacan-*`). No tema, ficaria preso a ele.
4. **O ritmo de versões é outro.** O tema passa a sair pela equipe do Tainacan; o plugin evolui com o Index Manager (`SUPPORTED_MAPPING`).

**O que vale fazer no tema (pouco código, sem custo de servidor):**
- Na seção "Tainacan Interface: listas e página do item" do Personalizar, uma linha informativa:
  - plugin ativo: link para as configurações dele;
  - plugin inativo: o que ele faz e onde obter;
  - sem gravar opção nem instalar nada.
- Conferir no navegador o encaixe com a caixa lateral de prévia. O "Encontrado em" e a caixa usam o mesmo cartão. Se combinarem, a caixa pode mostrar o "Encontrado em" quando o plugin estiver ativo, lendo o que o plugin já pôs no cartão, sem consulta nova.
- Propor o plugin à equipe do Tainacan como extensão oficial, junto com o Index Manager.

**Se ainda assim for para o tema:**
- desligado por padrão;
- só o motor SQL, com trava por tamanho do acervo (por exemplo, desligar sozinho acima de ~5 mil itens sem Index Manager);
- sem a correção ortográfica, que depende do índice;
- sem a tela própria (configurações no Personalizar).

Mesmo assim continua sendo funcionalidade de plugin para o WordPress.org.

## Achado de passagem (corrigido na 0.2.4 do plugin)

No bpb, a primeira consulta ao índice frio estourou aos 1,5 s (`cURL error 28`) e caiu para o SQL, que custa 1–2 s ali.
- **A causa.** O 1,5 s é o tempo limite do Index Manager para consultas de visitante. Cada estouro contava no disjuntor das listas, e três seguidos mandariam as listas do site inteiro para o SQL por 2 minutos.
- **A correção (0.2.4, 30/10/2026).** Os recortes passaram a usar o `search()` comum, com prazo dentro do ES 0,5 s abaixo do `es_timeout`, e ganharam um disjuntor próprio.
- **Resultado no bpb.** 0,6–1,2 s pelo índice e nenhuma queda para o SQL.

O detalhe está no `CLAUDE_CONTEXT.md` do repositório `tainacan-segmented-search`.
