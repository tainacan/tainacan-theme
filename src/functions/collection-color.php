<?php


class TainacanThemeCollectionColor {


    public $background_color = 'tainacan_theme_collection_background_color';
    public $text_color = 'tainacan_theme_collection_color';
    
    function __construct() {
        add_filter('tainacan-api-response-collection-meta', array($this, 'add_meta_to_response'), 10, 2);

        add_action('tainacan-register-admin-hooks', array($this, 'register_hook'));

        add_action('tainacan-insert-tainacan-collection', array($this, 'save_meta'));
        add_action( 'tainacan-enqueue-admin-scripts', array($this, 'action_tainacan_enqueue_admin_scripts') );
    }

    function action_tainacan_enqueue_admin_scripts() { 
        wp_enqueue_script('colorWell', get_template_directory_uri(). '/functions/collection-color.js', '', '', true);
        wp_enqueue_style('colorWellStyle', get_template_directory_uri(). '/functions/collection-color.css');
    }

    function register_hook() {
        if (function_exists('register_admin_hook')) {
            register_admin_hook( 'collection', array($this, 'form') );
        }
    }

    function form() {

        ob_start();
        ?>  

        <div class="field tainacan-collection--change-color-picker">
            <label class="label"><?php _e('Collection Background Color', 'tainacan-theme'); ?></label>
            <span class="help-wrapper">
                <a class="help-button has-text-secondary">
                    <span class="icon is-small">
                        <i class="mdi mdi-help-circle-outline"/></i>
                    </span>
                </a>
                <div class="help-tooltip">
                    <div class="help-tooltip-header">
                        <h5><?php _e('Collection Background Color', 'tainacan-theme'); ?></h5>
                    </div> 
                    <div class="help-tooltip-body">
                        <p><?php _e('Color that will fill the area over collection\'s header on Tainacan theme.', 'tainacan-theme'); ?></p>
                    </div>
                </div>
            </span>
            <div class="control is-clearfix"> 
                <input 
                    type="color" 
                    value="#2c2d2d" 
                    id="colorpicker"
                    name="<?php echo $this->background_color; ?>">
            </div>
        </div>

        <div class="field tainacan-collection--change-text-color">
            <label class="label"><?php _e('Collection Text Color', 'tainacan-theme'); ?></label>
            <span class="help-wrapper">
                <a class="help-button has-text-secondary">
                    <span class="icon is-small">
                        <i class="mdi mdi-help-circle-outline"/></i>
                    </span>
                </a>
                <div class="help-tooltip">
                    <div class="help-tooltip-header">
                        <h5><?php _e('Collection Text Color', 'tainacan-theme'); ?></h5>
                    </div> 
                    <div class="help-tooltip-body">
                        <p><?php _e('Text color on area over collection\'s header on Tainacan theme.', 'tainacan-theme'); ?></p>
                    </div>
                </div>
            </span>
            <div class="control is-clearfix"> 
                <input
                    type="radio" 
                    value="#fff" 
                    name="<?php echo $this->text_color; ?>"
                    id="white">
                    <label for="white" id="color-white" class="color-text">White</label>
                <input
                    type="radio" 
                    value="#000" 
                    name="<?php echo $this->text_color; ?>"
                    id="black">
                    <label for="black" id="color-black" class="color-text">Black</label>
            </div>
        </div>

        <?php


        return ob_get_clean();

    }

    function add_meta_to_response($extra_meta, $request) {
        $extra_meta = array(
            $this->background_color,
            $this->text_color
        );
        return $extra_meta;
    }

    function save_meta($object) {
        $postdata = file_get_contents("php://input");
        $post = json_decode($postdata);
        
        if ($object->can_edit()) {
            if (isset($post->{$this->background_color})) {
                update_post_meta($object->get_id(), $this->background_color, $post->{$this->background_color});
            }
            if (isset($post->{$this->text_color})) {
                update_post_meta($object->get_id(), $this->text_color, $post->{$this->text_color});
            }
        }

    }


}

new TainacanThemeCollectionColor();

?>
