<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T04: opção de tipo de metadado lida pela chave crua.
 *
 * As opções ficam no meta serializado metadata_type_options e, como índice,
 * em postmetas separados com prefixo _option_ (classes/repositories/
 * class-tainacan-metadata.php:1733-1757). get_post_meta( $id, 'taxonomy_id' )
 * devolve vazio sempre, sem erro.
 */
final class MetadatumOptionKey extends Rule
{
    const ID = 'TNC-T04';
    const TITLE = 'Opção de tipo de metadado lida sem o prefixo _option_';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'tainacan';

    /** Chaves de opção dos tipos nativos, específicas o bastante para não colidir com meta comum. */
    const KEYS = [
        'taxonomy_id', 'allow_new_terms', 'link_filtered_by_current_collection', 'link_filtered_by_collections',
        'hide_hierarchy_path', 'do_not_dispaly_term_as_link', 'visible_options_list',
        'display_related_item_metadata', 'display_in_related_items', 'accept_draft_items',
        'accept_only_items_authored_by_current_user', 'default_author', 'map_provider', 'initial_zoom',
        'maximum_zoom', 'initial_latitude', 'initial_longitude', 'control_metadatum', 'control_metadatum_options',
        'only_repository', 'children_order', 'link-as-button', 'force-iframe', 'iframe-min-height', 'is-image',
    ];

    protected function check(SourceFile $f)
    {
        $keys = array_flip(self::KEYS);
        foreach ($f->calls(['get_post_meta', 'update_post_meta', 'add_post_meta', 'delete_post_meta', 'get_metadata', 'metadata_exists']) as $c) {
            $idx = in_array($c['name'], ['get_metadata', 'metadata_exists'], true) ? 2 : 1;
            if (!isset($c['args'][$idx])) {
                continue;
            }
            $key = $f->literal($c['args'][$idx][0], $c['args'][$idx][1]);
            if ($key === null || !isset($keys[$key])) {
                continue;
            }
            $verb = $c['name'] === 'get_post_meta' || $c['name'] === 'get_metadata' ? 'devolve vazio' : 'grava numa chave que o Tainacan não lê';
            $this->report($f, $c['line'], '`' . $c['name'] . '( …, \'' . $key . '\' )` ' . $verb . ': as opções de tipo de metadado ficam em `_option_' . $key . '` (índice) e em metadata_type_options.', 'Leia $metadatum->get_metadata_type_options()[\'' . $key . '\'] ou get_post_meta( $id, \'_option_' . $key . '\', true ); para gravar, use o repositório (update_metadata_type_index é refeito no insert).');
        }
    }
}
