function onTainacanSearchSubmit($event) {

	if (document['tainacan-search-form']) {
        var selectedForm = document['tainacan-search-form'];
        
        if (document['tainacan-search-form'] instanceof HTMLCollection) {
            for (const child of document['tainacan-search-form']) {
                if (child.s && child.s.value)
                    selectedForm = child;
            }
        }
        console.log('aaa')
        console.log(selectedForm.archive)
        if (tainacan_search_info !== undefined) {
            if (selectedForm.archive) {
                switch (selectedForm.archive.value) {
                    case 'tainacan-items':
                        selectedForm.action = tainacan_search_info.theme_items_list_url + (selectedForm.s ? '?search=' + selectedForm.s.value : '');
                        break;
                    case 'tainacan-collections':
                        selectedForm.action = tainacan_search_info.theme_collection_list_url + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
                        break;
                    case 'posts':
                        selectedForm.action = tainacan_search_info.site_url + '/' + (selectedForm.s ? '?onlyposts=true&s=' +  selectedForm.s.value : '');
                        break;
                    case 'pages':
                        selectedForm.action = tainacan_search_info.site_url + '/' + (selectedForm.s ? '?onlypages=true&s=' +  selectedForm.s.value : '');
                        break;
                    case 'global':
                    default:
                        selectedForm.action = tainacan_search_info.site_url + '/' + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
                }
            } else {
                selectedForm.action = tainacan_search_info.site_url + '/' + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
            }
        }
        
        if ($event)
	        $event.preventDefault();
    }
	return true;
}