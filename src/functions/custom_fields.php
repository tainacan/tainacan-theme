<?php

add_filter('admin_init', 'tainacan_theme_address_fields');
 
function tainacan_theme_address_fields() {
    register_setting('general', 'address-field', 'esc_attr');
    register_setting('general', 'telephone-field', 'esc_attr');
    add_settings_field('address-field', '<label for="address-field">'.__('Address' , 'tainacan-theme' ).'</label>' , 'tainacan_theme_address_fields_html', 'general');
    add_settings_field('telephone-field', '<label for="address-field">'.__('Telephone' , 'tainacan-theme' ).'</label>' , 'tainacan_theme_telephone_fields_html', 'general');
}
 
function tainacan_theme_address_fields_html() { ?>
    <input type="text" id="address-field" name="address-field" value="<?php echo get_option( 'address-field', '' ); ?>" />
<?php }
 
 function tainacan_theme_telephone_fields_html() { ?>
     <input type="text" id="telephone-field" name="telephone-field" value="<?php echo get_option( 'telephone-field', '' ); ?>" />
 <?php }