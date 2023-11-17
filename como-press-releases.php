<?php
/*
Plugin Name: Como Press Releases
Plugin URI: http://www.comocreative.com/
Version: 1.0.5
Author: Como Creative LLC
Description: Added features to allow use of posts as Prtess Releases
*/
defined('ABSPATH') or die('No Hackers!');
/* Include plugin updater. */
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/updater.php' );
//Define News category
function comoCreatePressReleaseCategory(){
	$comopressrelease_cat = array('cat_name' => 'Press Release', 'category_description' => 'Press Releases', 'category_nicename' => 'press-release', 'category_parent' => '');
	wp_insert_category($comopressrelease_cat);
}
add_action('admin_init','comoCreatePressReleaseCategory');
function comoPressRelease_file_enqueue() {
    global $typenow;
    if( $typenow == 'post' ) {
        wp_enqueue_media();
        // Registers and enqueues the required javascript.
        wp_register_script( 'comoPressRelease-file-upload', plugin_dir_url( __FILE__ ) . '/js/file-upload.js', array( 'jquery' ) );
        wp_localize_script( 'comoPressRelease-file-upload', 'meta_image',
            array(
                'title' => 'Choose or Upload a File',
                'button' => 'Use this File',
            )
        );
        wp_enqueue_script( 'comoPressRelease-file-upload' );
    }
}
add_action( 'admin_enqueue_scripts', 'comoPressRelease_file_enqueue' );


// Adds the meta box stylesheet when appropriate 
function comoPressRelease_admin_styles(){
    global $typenow;
    if($typenow == 'post') {
        wp_enqueue_style('comopressrepease_meta_box_styles', plugin_dir_url( __FILE__ ) .'css/admin.min.css');
    }
}
add_action('admin_print_styles', 'comoPressRelease_admin_styles');

// Allow fields to be added below the title and above the editor
if (!function_exists('ai_edit_form_after_title')) {
	function ai_edit_form_after_title() {
		global $post, $wp_meta_boxes;
		do_meta_boxes( get_current_screen(), 'after_title', $post );
		unset( $wp_meta_boxes['post']['after_title'] );
	}
	add_action( 'edit_form_after_title', 'ai_edit_form_after_title' );
}
// Add Subtitle field to Posts
if (!function_exists('pressRelease_subtitle_create_post_meta_box')) {
	add_action( 'admin_menu', 'pressRelease_subtitle_create_post_meta_box' );
	add_action( 'save_post', 'pressRelease_subtitle_save_post_meta_box', 10, 2 );
	function pressRelease_subtitle_create_post_meta_box() {
		add_meta_box( 'pressRelease_subtitle-meta-box', 'Press Release Subtitle', 'pressRelease_subtitle_post_meta_box', 'post', 'after_title', 'high' );
	}
	function pressRelease_subtitle_post_meta_box( $object, $box ) { 
		$comoPressRelease_stored_meta = get_post_meta($object->ID);
		//print_r($comoPressRelease_stored_meta);
		?>
		<p>
			<?php
			$prSubtitle = (isset($comoPressRelease_stored_meta['pressRelease_subtitle']) ?  html_entity_decode($comoPressRelease_stored_meta['pressRelease_subtitle'][0]) : '');
			wp_editor($prSubtitle, 'pressRelease_subtitle', array(
				'wpautop'       => true,
				'textarea_name' => 'pressRelease_subtitle',
				'textarea_rows' => 4
			) );
			?>
		</p>
	<p><strong><?php _e( 'Press Release Dateline', 'como-textdomain' )?></strong><br><input name="post_dateline" id="post_dateline" width="100%" tabindex="2" style="width: 97%;" value="<?=(isset($comoPressRelease_stored_meta['post_dateline']) ?  esc_html($comoPressRelease_stored_meta['post_dateline'][0]) : '')?>" /><br><em>Post Dateline not to be included in excerpt</em></p>

	<p><strong><?php _e( 'Press Release Link', 'como-textdomain' )?></strong><br><input name="post_pr_link" id="post_pr_link" width="100%" tabindex="2" style="width: 97%;" value="<?=(isset($comoPressRelease_stored_meta['post_pr_link']) ?  esc_html($comoPressRelease_stored_meta['post_pr_link'][0]) : '')?>" /><br><em>Link to external Pres Release</em></p>

	<p class="image-upload">
        <label for="comopressrelease-file" class="comometa-row-title"><?php _e( 'Press Release File: ', 'como-textdomain' )?></label>
        <span class="comometa-row-content upload-field">
			<input type="text" name="comopressrelease-file" id="comopressrelease-file" class="como-upload-field" value="<?php if ( isset ( $comoPressRelease_stored_meta['comopressrelease-file'] ) ) echo $comoPressRelease_stored_meta['comopressrelease-file'][0]; ?>" />
			<input type="hidden" name="comopressrelease-file-id" id="comopressrelease-file-id" class="como-upload-id-field" value="<?php if ( isset ( $comoPressRelease_stored_meta['comopressrelease-file-id'] ) ) echo $comoPressRelease_stored_meta['comopressrelease-file-id'][0]; ?>" />	
			<?php
				if (!empty($comoPressRelease_stored_meta['comopressrelease-file'][0])) {
					$upload1class = 'hidden';
					$remove1class = ''; 
				} else {
					$upload1class = '';
					$remove1class = 'hidden';
				}
			?>
			<input type="button" class="remove-upload-button <?=$remove1class?>" value="<?php _e( 'Remove File', 'como-textdomain' )?>" />
			<input type="button" class="meta-upload-button <?=$upload1class?>" value="<?php _e( 'Choose or Upload a File', 'como-textdomain' )?>" />
		</span>	
    </p>
	<input type="hidden" name="pressRelease_subtitle_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
	<?php }
	function pressRelease_subtitle_save_post_meta_box( $post_id, $post ) {
		if (isset($_POST['pressRelease_subtitle_meta_box_nonce'])) {
			if ( !wp_verify_nonce( $_POST['pressRelease_subtitle_meta_box_nonce'], plugin_basename( __FILE__ ) ) )
				return $post_id;
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
			// Specify Meta Variables to be Updated
			$metaVars = array('pressRelease_subtitle','post_dateline','post_pr_link','comopressrelease-file');
			// Update Meta Variables
			foreach ($metaVars as $var) {
				if(isset($_POST[$var])) {
					update_post_meta($post_id, $var, $_POST[$var]);
				}
			}
		}
	}
}
// Press Release Custom Footer
if (!function_exists('postFooter_create_post_meta_box')) {
	add_action( 'admin_menu', 'postFooter_create_post_meta_box' );
	add_action( 'save_post', 'postFooter_save_post_meta_box', 10, 2 );
	function postFooter_create_post_meta_box() {
		add_meta_box( 'postfooter-meta-box', 'Press Release Custom Footer (Overrides default Press Release footer in Settings)', 'postFooter_post_meta_box', 'post', 'normal', 'high' );
	}
	function postFooter_post_meta_box( $object, $box ) { ?>
		<?php
			wp_editor(html_entity_decode(get_post_meta($object->ID, 'pressReleaseFooter', true ), 1 ), 'pressReleaseFooter', array(
				'wpautop'       => true,
				'textarea_name' => 'pressReleaseFooter',
				'textarea_rows' => 10,
				'media_buttons' => false,
				'teeny'			=> true
			) );
		?>
		<input type="hidden" name="postFooter_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
	<?php }
	function postFooter_save_post_meta_box( $post_id, $post ) {
		if (isset($_POST['postFooter_meta_box_nonce'])) {
			if ( !wp_verify_nonce( $_POST['postFooter_meta_box_nonce'], plugin_basename( __FILE__ ) ) )
				return $post_id;
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
			// Specify Meta Variables to be Updated
			$metaVars = array('pressReleaseFooter');
			// Update Meta Variables
			foreach ($metaVars as $var) {
				if(isset($_POST[$var])) {
					update_post_meta($post_id, $var, $_POST[$var]);
				}
			}
		}
	}
}

// Press Release Custom Contacts
if (!function_exists('postContacts_create_post_meta_box')) {
	add_action( 'admin_menu', 'postContacts_create_post_meta_box' );
	add_action( 'save_post', 'postContacts_save_post_meta_box', 10, 2 );
	function postContacts_create_post_meta_box() {
		add_meta_box( 'postcontacts-meta-box', 'Press Release Custom Contacts (Overrides default Press Release Contacts in Settings)', 'postContacts_post_meta_box', 'post', 'normal', 'high' );
	}
	function postContacts_post_meta_box( $object, $box ) { ?>
		<?php
			wp_editor(html_entity_decode(get_post_meta($object->ID, 'pressReleaseContacts', true ), 1 ), 'pressReleaseContacts', array(
				'wpautop'       => true,
				'textarea_name' => 'pressReleaseContacts',
				'textarea_rows' => 10,
				'media_buttons' => false,
				'teeny'			=> true
			) );
		?>
		<input type="hidden" name="postContacts_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
	<?php }
	function postContacts_save_post_meta_box( $post_id, $post ) {
		if (isset($_POST['postContacts_meta_box_nonce'])) {
			if ( !wp_verify_nonce( $_POST['postContacts_meta_box_nonce'], plugin_basename( __FILE__ ) ) )
				return $post_id;
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
			// Specify Meta Variables to be Updated
			$metaVars = array('pressReleaseContacts');
			// Update Meta Variables
			foreach ($metaVars as $var) {
				if(isset($_POST[$var])) {
					update_post_meta($post_id, $var, $_POST[$var]);
				}
			}
		}
	}
}

// Options Page
class comoPressReleaseSettingsPage {
    /* Holds the values to be used in the fields callbacks */
    private $options;
    /*  Start up */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
    /*  Add options page */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Press Release Admin', 
            'Press Release Settings', 
            'manage_options', 
            'comoPressRelease-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }
    /* Options page callback */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'como_pressRelease_option_name' );
        ?>
        <div class="wrap">
            <h1>Press Release Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'como_pressRelease_option_group' );
                do_settings_sections( 'comoPressRelease-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
    /*  Register and add settings */
    public function page_init() {        
        register_setting(
            'como_pressRelease_option_group', // Option group
            'como_pressRelease_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
        add_settings_section(
            'setting_section_id', // ID
            'Press Release Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'comoPressRelease-setting-admin' // Page
        );  
        add_settings_field(
            'pressRelease_footer', 
            'Press Release Footer', 
            array( $this, 'pressRelease_footer_callback' ), 
            'comoPressRelease-setting-admin', 
            'setting_section_id'
        );
		add_settings_field(
            'pressRelease_contacts', 
            'Press Release Contacts', 
            array( $this, 'pressRelease_contacts_callback' ), 
            'comoPressRelease-setting-admin', 
            'setting_section_id'
        );
    }
    /* Sanitize each setting field as needed
     * @param array $input Contains all settings fields as array keys */
    public function sanitize( $input ) {
        $new_input = array();
        if( isset( $input['pressRelease_footer'] ) )
            $new_input['pressRelease_footer'] = wp_kses_post( $input['pressRelease_footer'] );
		if( isset( $input['pressRelease_contacts'] ) )
            $new_input['pressRelease_contacts'] = wp_kses_post( $input['pressRelease_contacts'] );
        return $new_input;
    }
    /*  Print the Section text */
    public function print_section_info() {
        print 'Please enter the default Press Release Footer anmd Contacts below:';
    }
    /*  Get the settings option array and print one of its values */
    public function pressRelease_footer_callback() {
		wp_editor(html_entity_decode($this->options['pressRelease_footer']), 'release-footer-editor', array(
			'wpautop'       => true,
			'textarea_name' => 'como_pressRelease_option_name[pressRelease_footer]',
			'textarea_rows' => 10,
			'media_buttons' => false,
			'teeny'			=> true
		) );
    }
	public function pressRelease_contacts_callback() {
		wp_editor(html_entity_decode($this->options['pressRelease_contacts']), 'release-contacts-editor', array(
			'wpautop'       => true,
			'textarea_name' => 'como_pressRelease_option_name[pressRelease_contacts]',
			'textarea_rows' => 10,
			'media_buttons' => false,
			'teeny'			=> true
		) );
    }
}
if( is_admin() ) {
    $my_settings_page = new comoPressReleaseSettingsPage();
}