function onTainacanSearchSubmit($event) {

	if (document['tainacan-search-form']) {
        var selectedForm = document['tainacan-search-form'];
        
        if (document['tainacan-search-form'] instanceof HTMLCollection) {
            for (const child of document['tainacan-search-form']) {
                if (child.s && child.s.value)
                    selectedForm = child;
            }
        }
        
        if (selectedForm.archive && tainacan_search_info !== undefined) {
            switch (selectedForm.archive.value) {
                case 'tainacan-items':
                    console.log(tainacan_search_info.theme_items_list_url)
                    selectedForm.action = tainacan_search_info.theme_items_list_url + (selectedForm.s ? '?search=' + selectedForm.s.value : '');
                    break;
                case 'tainacan-collections':
                    selectedForm.action = tainacan_search_info.theme_collection_list_url + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
                    break;
                case 'posts':
                    selectedForm.action = '/' + (selectedForm.s ? '?onlyposts=true&s=' +  selectedForm.s.value : '');
                    break;
                case 'pages':
                    selectedForm.action = '/' + (selectedForm.s ? '?onlypages=true&s=' +  selectedForm.s.value : '');
                    break;
                case 'global':
                default:
                    selectedForm.action = '/' + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
            }
        } else {
            selectedForm.action = '/' + (selectedForm.s ? '?s=' +  selectedForm.s.value : '');
        }
        
        if ($event)
	        $event.preventDefault();
    }
	return true;
}