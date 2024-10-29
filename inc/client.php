<?php
/*******************************************************************************
 *  Copyright (c) 2009 Inteliscent SAS.
 *  All rights reserved. This program and the accompanying materials
 *  are made available under the terms of the GNU Public License v2.0
 *  which accompanies this distribution, and is available at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *  
 *  Contributors:
 *      Be-API - initial API and implementation
 *      Inteliscent SAS - Stabilisation et definitive version
 ******************************************************************************/





class AdfeverClient extends AdfeverBase {
	var $shop_obj = null;

	function AdfeverClient() {
		// add Shortcode on WP
		add_shortcode( 'adfever', array(&$this, 'shortCode') );
		
		// Add CSS on header
		add_action('wp_head', array(&$this, 'displayCSS') );
		
		
		// Check AID
		$result = parent::isValidAid( true );	
		if ( $result === true ) {
			// Wait init for init TinyMCE
			add_action( 'init', array(&$this, 'initEditor') );
		}
	}
	
	function shortCode($atts) {
		extract(shortcode_atts(array(
			'type' => '',
			'value' => '',
		), $atts));
	
		if ( empty($type) || empty($value) ) {
			return '';
		}
		
		global $post;
		if ( get_post_meta($post->ID, 'desactive_adfever', true) == '1' ) { // Options desactive adfever
			return '';
		}
		
		// init once Shopping
		if ( $this->shop_obj == null ) {
			$current_options = get_option( $this->option_name );
			$this->shop_obj = new Shopping ( $current_options['aid'], ADFEVER_UUID );	
		}

		switch ( $type ) {
			case 'top-category' :

				$item = $this->shop_obj->getTop( $value, 5 );
				$key  = array_rand( $item["shopping"]["products"]["product"] );
				return $this->buildTableProduct( $item["shopping"]["products"]["product"][$key] );
				break;
			case 'product' :
			default :
			
				// Get item and stores
				$item = $this->shop_obj->find( $value );
				$item = $item["shopping"]["product"];	
				
				return $this->buildTableProduct( $item );
				break;
		}
		
		return '';
	}
	
	function buildTableProduct( $item = null ) {
		if ( is_null($item) ) {
			return __( 'Internal error.', 'adfever' );
		}
		
		$stores = $item["offers"]["offer"];
		$current_options = get_option( $this->option_name );

		$output = '';
		$output .= '<div class="adfever-container">' . "\n";
			
			if ( $current_options['photo'] == '1' )
				$output .= '<img src="'.clean_url($item['image']).'" alt="'.attribute_escape($item['name']).'" class="adfever-img">' . "\n";
		
			$output .= '<h5>'.wp_specialchars($item['name']).'</h5>' . "\n";
			$output .= apply_filters('the_adfever_description', $item['description']) . "\n";
			
			$output .= '<div style="clear:both;"></div>' . "\n";
			
			if ( is_array($stores) && !empty($stores) ) :
				$output .= '<table class="adfever-table">' . "\n";
					$output .= '<thead>' . "\n";
						$output .= '<tr>' . "\n";
							$output .= '<th>'.__('Retailer', 'adfever').'</th>' . "\n";
							$output .= '<th>'.__('Availability', 'adfever').'</th>' . "\n";
							$output .= '<th>'.__('Total price (incl.delivery)', 'adfever').'</th>' . "\n";
						$output .= '</tr>' . "\n";
					$output .= '</thead>' . "\n";
				
					$output .= '<tbody>' . "\n";
					
						$stores = array_slice( $stores, 0, $current_options['max-store'], true );
						foreach( (array) $stores as $store ) {

							$output .= '<tr>' . "\n";

								if ( $current_options['logos'] == '1' && !empty($store['logo']) )
									$output .= '<td><a href="'.$store['link'].'"><img src="'.$this->fixLogosUrl( $store['logo'] ).'" alt="'.attribute_escape($store['merchant']).'" /></a></td>' . "\n"; // Display logo for each stores
								else
									$output .= '<td><a href="'.$store['link'].'">'.wp_specialchars($store['merchant']).'</a></td>' . "\n";
	
								$output .= '<td><a href="'.$store['link'].'">'.$store['stock'].'</a></td>' . "\n";
								$output .= '<td><a href="'.$store['link'].'"><strong>'.$store['total_price']['value'].'</strong> <br /> <img src="'.ADFEVER_URL.'/inc/images/bt-go.jpg" border="0" /></a></td>' . "\n";
							$output .= '</tr>' . "\n";
						}
						
					$output .= '</tbody>' . "\n";
				$output .= '</table>' . "\n";
			else :
				$output .= '<p>'.__('No retailers for this product.', 'adfever').'</p>' . "\n";
			endif;
			
			$output .= $this->getCopyright();
		$output .= '</div>' . "\n";
		
		return $output;
	}
	
	function getCopyright() {
		return '<p class="adfever-credits"><a href="http://www.adfever.com">'.__('Technologie AdFever', 'adfever').'</a></p>' . "\n";
	}
	
	function fixLogosUrl( $url = '' ) {
		$tmp = $url;
		
		$url = str_replace('http://i.pricerunner.com/images/logos/fr/', 'http://i.pricerunner.com/images/logos/fr/80x35/', $url );
		if ( $url == $tmp )
			$url = str_replace('http://i.pricerunner.com/images/logos/', 'http://i.pricerunner.com/images/logos/80x35/', $url );	
		
		return $url;
	}
	
	function displayCSS() {
		$current_options = get_option( $this->option_name );
		?>
		<style type="text/css">
			body .adfever-container {
			position:relative;
			text-align: left;
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'color:'.$current_options['text'].';';
			}
			?> 
			}
			body .adfever-container p.adfever-credits { text-align:right; font-style: italic; margin-top: 3px; }
			body .adfever-container p.adfever-credits a { 
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'color:'.$current_options['text'].';';
			}
			?> 
			}	
			body .adfever-container h5 { margin-top: 0; }
			body .adfever-container .adfever-img { float:left; margin:0 5px 5px 0; border:1px solid #ccc; }

			body .adfever-container table.adfever-table { 
			position:relative; 
			width:100%; 
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'border:1px solid'.$current_options['border-table'].';';
			}
			?> 
			border-collapse:collapse;
			}
			body .adfever-container table.adfever-table tr { }
			body .adfever-container table.adfever-table th { 
			padding:3px; 
			text-align: center; 
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'color:'.$current_options['text-header-table'].';';
				echo 'background:'.$current_options['bg-header-table'].';';
			}
			?> 
			font-weight:700;
			}
			body .adfever-container table.adfever-table td {
			padding:3px;
			text-align: center;
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'color:'.$current_options['text-table'].';';
				echo 'background:'.$current_options['bg-table'].';';
				echo 'border:1px solid'.$current_options['border-table'].';';
			}
			?> 
			border-collapse:collapse;
			}
			body .adfever-container table.adfever-table td a {
			<?php 
			if ($current_options['enable_colors']=='1') {
				echo 'color:'.$current_options['text-table'].';';
			}
			?>		
			}
			body .adfever-container table.adfever-table thead { }
			body .adfever-container table.adfever-table tbody { }	
			body .adfever-container a img { text-decoration: none; }
		</style>
		<?php
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
		$buttonshtml .= '<input type="button" class="ed_button" onclick="AdfeverButtonClick(\'adfever\')" title="' . __('Add an adfever block', 'adfever') . '" value="' . __('Adfever', 'adfever') . '" />';
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
}
?>
