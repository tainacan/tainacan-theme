<?php


class TainacanThemeCollectionColor {


    public $meta_name = 'tainacan_theme_collection_color';
    
    function __construct() {
        add_filter('tainacan-api-response-collection-meta', [$this, 'add_meta_to_response'], 10, 2);

        add_action('tainacan-register-admin-hooks', [$this, 'register_hook']);

        add_action('tainacan-insert-tainacan-collection', [$this, 'save_meta']);

    }

    function register_hook() {
        if (function_exists('register_admin_hook')) {
            register_admin_hook( 'collection', [$this, 'form'] );
        }
    }

    function form() {

        ob_start();
        ?>  

        <div class="field">
        <label class="label">Collection Color</label>
        <span class="help-wrapper">
            <a class="help-button has-text-secondary">
                <span class="icon is-small">
                    <i class="mdi mdi-help-circle-outline"/></i>
                </span>
            </a>
            <div class="help-tooltip">
                <div class="help-tooltip-header">
                    <h5>Collection Color</h5>
                </div> 
                <div class="help-tooltip-body">
                    <p>Color that will fill the area over collection's header on Tainacan theme.</p>
                </div>
            </div>
        </span>
        <div class="control is-clearfix"> 
            <input 
                    style="height: 40px;width: 40px;margin: 0.5rem 0.1rem;border: 0;padding: 0;" 
                    type="color" 
                    value="#2c2d2d" 
                    name="<?php echo $this->meta_name; ?>">
            </input>   
        </div>
    
        <?php


        return ob_get_clean();

    }

    function add_meta_to_response($extra_meta, $request) {
        $extra_meta[] = $this->meta_name;
        return $extra_meta;
    }

    function save_meta($object) {
        global $HTTP_RAW_POST_DATA;
        $post = json_decode($HTTP_RAW_POST_DATA);
        
        if ($object->can_edit()) {
            if (isset($post->{$this->meta_name})) {
                update_post_meta($object->get_id(), $this->meta_name, $post->{$this->meta_name});
            }
        }

    }


}

new TainacanThemeCollectionColor();

?>