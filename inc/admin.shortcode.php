<?php

require_once 'client.shortcode.php';

class AdfeverAdmin_Shortcode extends AdfeverClient_Shortcode {
	
	function AdfeverAdmin_Shortcode($parent) {
		parent::AdfeverClient_Shortcode();
		wp_enqueue_style( 'adfever-shortcode', get_bloginfo('siteurl').'/?adfever-css=shortcode', array(), $this->version, 'all' );
		$this->checkCSS();
		$this->parent = $parent;
		
		if ( $this->_ok ) {
			
			// Wait init for init TinyMCE
			add_action( 'init', array(&$this, 'initEditor') );
		}
		
		add_action ( 'admin_menu', array (&$this, 'addMenu' ) );
		
		
	}
	
	function addMenu() {
		add_submenu_page(
	
			'adfever-general', 
			__('Banners', 'adfever'), 
			__('Banners', 'adfever'), 
			'manage_options', 
			'adfever-banners', 
			array (&$this, 'pageBanners' )
		);
	}

	function initEditor() {
		
		// Register editor button hooks
		add_filter( 'tiny_mce_version', array(&$this, 'tiny_mce_version') );
		add_filter( 'mce_external_plugins', array(&$this, 'mce_external_plugins') );
		add_filter( 'mce_buttons', array(&$this, 'mce_buttons') );

		// Register quick tags
		add_action( 'edit_form_advanced', array(&$this, 'AddQuicktagsAndFunctions') ); // Post
		add_action( 'edit_page_form', array(&$this, 'AddQuicktagsAndFunctions') ); // Page
	}

	/**
	 * Break the browser cache of TinyMCE
	 *
	 * @param string $version
	 * @return string
	 */
	function tiny_mce_version( $version ) {
		return $version . '-adfever' . $this->version . 'line3';
	}


	/**
	 * Load the custom TinyMCE plugin
	 *
	 * @param array $plugins
	 * @return array
	 */
	function mce_external_plugins( $plugins ) {
		$plugins['adfever'] = ADFEVER_URL.'/inc/js/tinymce3/editor_plugin.js';
		return $plugins;
	}


	/**
	 * Add the custom TinyMCE buttons
	 *
	 * @param array $buttons
	 * @return array
	 */
	function mce_buttons( $buttons ) {
		array_push( $buttons, 'separator', 'adfever' );
		return $buttons;
	}

	/**
	 * Add the old style buttons to the non-TinyMCE editor views and output all of the JS for the button function + dialog box
	 *
	 */
	function AddQuicktagsAndFunctions() {
		
		$buttonshtml .= '<input type="button" class="ed_button" onclick="AdfeverButtonClick(\'adfever\')" title="' . __('Add an adfever block', 'adfever') . '" value="' . __('AdFever', 'adfever') . '" />';
		?>
<script type="text/javascript">
		//<![CDATA[
			// This function is run when a button is clicked. It creates a dialog box for the user to input the data.
			function AdfeverButtonClick( tag ) {
				tb_open_new('<?php echo ADFEVER_URL; ?>/inc/lightbox.php?tag='+tag+'&TB_iframe=true');	
			}
		
			// On page load...
			jQuery(document).ready(function(){
				// Add the buttons to the HTML view
				jQuery("#ed_toolbar").append('<?php echo $this->js_escape( $buttonshtml ); ?>');
			});
		//]]>
		</script>
		<?php
	}

	/**
	 * WordPress' js_escape() won't allow <, >, or " -- instead it converts it to an HTML entity. This is a "fixed" function that's used when needed.
	 *
	 * @param string $text
	 * @return string
	 */
	function js_escape($text) {
		$safe_text = addslashes($text);
		$safe_text = preg_replace('/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes($safe_text));
		$safe_text = preg_replace("/\r?\n/", "\\n", addslashes($safe_text));
		$safe_text = str_replace('\\\n', '\n', $safe_text);
		return apply_filters('js_escape', $safe_text, $text);
	}
	
	/**
	 * Banners configuration
	 * 
	 */
	function pageBanners() {
		$options = parent::getOptions( 'banners' );

		// Check update options
		if ( isset($_POST['submit-banners-adfever']) ) {
			$this->parent->saveOptions( $options );
			$this->parent->message = __('Banners settings updated with success !', 'adfever');
		}
		$this->parent->displayMessage();

		// Check AID
		$result = parent::isValidAid( true );
		
		$tpl = '<div class="wrap"><h2>'. __('Banners', 'adfever').'</h2>';
		
		if($result!==true) { 
			$tpl .= '<p>'.__('You must enter a valid AID before can customize the settings of AdFever.', 'adfever').'</p>'; 
		}
		else {
			$tpl .= '<form action="" method="post">
			<div id="tabs">'.$this->parent->displayOptionsTable( $options ).'</div>

			<p class="submit"><input type="submit" name="submit-banners-adfever"
				class="button-primary" value="'.__('Save', 'adfever').'" /></p>
			</form>';
			
		}
		
		$tpl .+ '</div>';
		
		echo $tpl;
			
	}
}