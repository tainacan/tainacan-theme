# TNC-W16 — MutationObserver no documento que escreve no DOM

**Sintoma.** A página carrega, desenha tudo certo e para de responder. Nenhum
erro no console, nenhuma linha no log do servidor, resposta 200. Rolagem
travada, clique sem efeito. Numa sessão de teste automatizado o sinal foi
outro: `Runtime.evaluate` pelo protocolo do Chrome passou a estourar o tempo
limite, porque a thread principal nunca devolvia o controle.

**Causa.** Um `MutationObserver` escutando `childList` com `subtree` em
`document.body`, cujo callback escreve no DOM. Escrever é mutar:

```js
function decorar() {
    document.querySelectorAll( '.cartao' ).forEach( function ( cartao ) {
        if ( cartao.querySelector( '.chip' ) ) {
            return;                       // a guarda parece suficiente…
        }
        cartao.appendChild( chip() );
    } );
    render();                             // …mas isto reescreve textContent
}

new MutationObserver( decorar ).observe( document.body, { childList: true, subtree: true } );
```

A guarda do `appendChild` existe, mas `render()` faz
`botao.textContent = 'Comparar'` em todos os botões. Atribuir `textContent`
substitui o nó de texto — mutação de `childList` — mesmo quando o texto é
idêntico. Cada passe acorda o observador, que chama o callback, que escreve de
novo. O laço roda para sempre dentro de uma única tarefa de microfila: a aba
congela.

Num tema do Tainacan o risco é alto porque a lista é um aplicativo Vue que
muda o DOM o tempo todo, e o tema decora os cartões que ela monta. O observador
no documento inteiro é a maneira natural de esperar o aplicativo — e a
armadilha vem junto.

**Correção.** Três coisas, nesta ordem de importância:

1. **Calar o observador enquanto se escreve.**

```js
var observador = null;

function semObservar( fn ) {
    if ( observador ) {
        observador.disconnect();
    }
    try {
        fn();
    } finally {
        if ( observador ) {
            observador.observe( document.body, { childList: true, subtree: true } );
        }
    }
}
```

2. **Juntar as mutações de um quadro num passe só**, com
   `requestAnimationFrame` e uma trava de agendamento. Uma troca de página do
   aplicativo produz centenas de mutações; sem isso o callback roda centenas de
   vezes.

3. **Escrever só o que mudou**: `if ( botao.textContent !== texto )`. Barato, e
   corta o laço na raiz mesmo que alguém esqueça o item 1.

Quando dá, melhor ainda: observar o nó certo em vez do documento. Depois que o
aplicativo monta, a paginação, a barra de controles ou o contêiner da lista
bastam.

**Como a regra decide.** Acusa por arquivo quando há `new MutationObserver`, um
`.observe()` em `document`/`document.body` com `childList` ou `subtree`,
escrita que mexe em filhos (`appendChild`, `textContent =`, `innerHTML =`,
`insertAdjacentElement`, `remove`, …) e **nenhuma** chamada a `disconnect()`.
Um `disconnect()` em qualquer lugar do arquivo já tira a regra do caminho: é
sinal de que quem escreveu conhece o problema.

**Origem.** Tema Tainacan Acervo 3.5.1, recurso "Comparar itens". A aba travava
em toda lista; a pilha só apareceu ao pausar o depurador do Chrome numa aba já
congelada (`Debugger.pause` → `decorate @ compare.js`). O mesmo padrão estava,
adormecido, no "Ler mais" do mesmo tema — salvo por uma guarda que qualquer
refatoração podia remover.
