function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    // Out of sight but selectable: a hidden field cannot be copied from.
    textArea.style.setProperty('position', 'fixed');
    textArea.style.setProperty('top', '0');
    textArea.style.setProperty('left', '0');
    textArea.style.setProperty('opacity', '0');
    document.body.appendChild(textArea);
    textArea.focus({ preventScroll: true });
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        if (msg == 'successful') {
            alert(tainacan_copyLinkVars.linkCopied);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }

    document.body.removeChild(textArea);
}

function copyTextToClipboard(text) {
    if (!navigator.clipboard) {
        fallbackCopyTextToClipboard(text);
        return;
    }
    
    navigator.clipboard.writeText(text)
        .then(() => {
            alert(tainacan_copyLinkVars.linkCopied);
        }, 
        (err) => {
            console.error('Async: Could not copy text: ', err);
        });
}

// No jQuery: the design of version 4 does not load it.
document.addEventListener( 'click', function( event ) {
    var button = event.target.closest && event.target.closest( '.tainacan-copy-link-button' );
    if ( button ) {
        copyTextToClipboard( button.getAttribute( 'data-link' ) );
    }
} );
