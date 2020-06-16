function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.setProperty('display', 'none');
    textArea.style.setProperty('visibility', 'hidden');
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Fallback: Copying text command was ' + msg);
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
            console.log('Async: Copying to clipboard was successful!');
            alert(tainacan_copyLinkVars.linkCopied);
        }, 
        (err) => {
            console.error('Async: Could not copy text: ', err);
        });
}