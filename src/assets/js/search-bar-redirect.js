function onTainacanSearchSubmit($event) {

	if (document['tainacan-search-form']) {

        if (document['tainacan-search-form'].archive && tainacan_plugin !== undefined) {
            switch (document['tainacan-search-form'].archive.value) {
                case 'tainacan-items':
                    document['tainacan-search-form'].action = tainacan_plugin.theme_items_list_url + (document['tainacan-search-form'].s ? '?search=' + document['tainacan-search-form'].s.value : '');
                    break;
                case 'tainacan-collections':
                    document['tainacan-search-form'].action = tainacan_plugin.theme_collection_list_url + (document['tainacan-search-form'].s ? '?s=' +  document['tainacan-search-form'].s.value : '');
                    break;
                case 'posts':
                    document['tainacan-search-form'].action = '/' + (document['tainacan-search-form'].s ? '?onlyposts=true&s=' +  document['tainacan-search-form'].s.value : '');
                    break;
                case 'pages':
                    document['tainacan-search-form'].action = '/' + (document['tainacan-search-form'].s ? '?onlypages=true&s=' +  document['tainacan-search-form'].s.value : '');
                    break;
                case 'global':
                default:
                    document['tainacan-search-form'].action = '/' + (document['tainacan-search-form'].s ? '?s=' +  document['tainacan-search-form'].s.value : '');
            }
        } else {
            document['tainacan-search-form'].action = '/' + (document['tainacan-search-form'].s ? '?s=' +  document['tainacan-search-form'].s.value : '');
        }
        
        if ($event)
	        $event.preventDefault();
    }
	return true;
}