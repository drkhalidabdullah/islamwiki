function copyWikiLink(filename) {
    const wikiLink = `[[File:${filename}]]`;
    navigator.clipboard.writeText(wikiLink).then(() => {
        alert('Wiki link copied to clipboard: ' + wikiLink);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = wikiLink;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Wiki link copied to clipboard: ' + wikiLink);
    });
}
