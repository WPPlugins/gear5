<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists("Gear5_Settings")) :

class Gear5_Settings {

    	public static $default_settings = 
		array( 	
			  	'text_api_id' => '',
			  	'text_url' => ''
				);
	
        var $pagehook, $page_id, $settings_field, $options;

    	function __construct() {	
		$this->page_id = 'Gear5';
		// This is the get_options slug used in the database to store our plugin option values.
		$this->settings_field = 'gear5_options';
		$this->options = get_option( $this->settings_field );

		add_action('admin_init', array($this,'admin_init'), 20 );
		add_action('admin_menu', array($this, 'admin_menu'), 20);
	}

	function admin_init() {
		register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
		add_option( $this->settings_field, Gear5_Settings::$default_settings );		
		
		/* 
			This is needed if we want WordPress to render our settings interface
			for us using -
			do_settings_sections
			
			It sets up different sections and the fields within each section.
		*/
		add_settings_section('gear5_main', '',  
			array($this, 'gear5_section_text'), 'gear5_settings_page');

		add_settings_field('api_id', 'Api id', 
			array($this, 'render_api_id'), 'gear5_settings_page', 'gear5_main');
		
	}
        
        function admin_menu() {
		if ( ! current_user_can('update_plugins') )
			return;
	
		// Add a new submenu to the standard Settings panel
		$this->pagehook = $page =  add_options_page(	
			__('Gear5', 'gear5'), __('Gear5', 'gear5'), 
			'administrator', $this->page_id, array($this,'render') );
		
		// Executed on-load. Add all metaboxes.
		add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );
                // Include js, css, or header *only* for our settings page
                add_action("admin_print_scripts-$page", array($this, 'js_includes'));

	}

        function metaboxes() {
		// Metabox for simple settings with input text box for api key, rendered in HTML in the setup_box function
		add_meta_box( 'gear5-settings', __( 'Basic setup', 'gear5' ), array( $this, 'setup_box' ), $this->pagehook, 'main', 'high' );

	}

	/*
		Sanitize our plugin settings array as needed.
	*/	
	function sanitize_theme_options($options) {
		$options['example_text'] = stripcslashes($options['example_text']);
		return $options;
	}

        function render() {
		global $wp_meta_boxes;

		$title = __('Gear5 Settings', 'gear5');
		?>
		<div class="wrap">   
			<h2><?php echo esc_html( $title ); ?></h2>
		
			<form method="post" action="options.php">
				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>

                                    <div class="metabox-holder">
                                        <div class="postbox-container" style="width: 99%;">
                                        <?php 
                                                                    // Render metaboxes
                                            settings_fields($this->settings_field); 
                                            do_meta_boxes( $this->pagehook, 'main', null );
                                            if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
                                                                            do_meta_boxes( $this->pagehook, 'column2', null );
                                        ?>
                                        </div>
                                    </div>

				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>
			</form>
		</div>
        
                <!-- Needed to allow metabox layout and close functionality. -->
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
	
        <?php }

        function setup_box() {

		?>
                <p>
                Enter your api key from gear5 settings page: 
                </p>

                <div style="background-color: #fffbcc; width:600px;padding: 5px;border: 1px #DDDDDD solid;">
                <p>

                    <label for="<?php echo $this->get_field_id( 'text_api_id' ); ?>" style="font-weight: bold;"><?php _e( '&nbsp;Api key :', 'gear5' ); ?></label></b>
			<input type="text" name="<?php echo $this->get_field_name( 'text_api_id' ); ?>" id="<?php echo $this->get_field_id( 'text_api_id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'text_api_id' ) ); ?>" style="width:89%;" />

                </p>
                </div>
                <p>
                To find you api key, please follow these instructions:
                </p>
                <ol>
                    <li>Go to your Gear5 Dashboard at <a href="http://www.gear5.me/dashboard">http://www.gear5.me/dashboard</a></li>
                    <li>click "Edit" for your web site</li>
                    <li>On top of General setting your api key will be displayed.</li> 
                    <li>Copy and paste api code and press "Save Options".</li>
                    <li>You are set up!</li>
                </ol>
                <p>
                    If you do not have Gear5 account, please register at <a href="http://www.gear5.me">http://www.gear5.me</a>
                </p>

		<?php

	}


        protected function get_field_name( $name ) {

		return sprintf( '%s[%s]', $this->settings_field, $name );

	}

	protected function get_field_id( $id ) {

		return sprintf( '%s[%s]', $this->settings_field, $id );

	}

	protected function get_field_value( $key ) {

		return $this->options[$key];

	}

       	function js_includes() {
		// Needed to allow metabox layout and close functionality.
		wp_enqueue_script( 'postbox' );
	}

}
endif;
