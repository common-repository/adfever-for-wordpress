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





class AdfeverClient_Shortcode extends AdfeverBase {
	
	var $_ok = false;
	
	function AdfeverClient_Shortcode() {
		// add Shortcode on WP
		$this->_ok = parent::isValidAid( true );
		
		add_shortcode( 'adfever', array(&$this, 'shortCode') );
		
		if ( !is_admin() ) {
			wp_enqueue_style( 'adfever-shortcode', get_bloginfo('siteurl').'/?adfever-css=shortcode', array(), $this->version, 'all' );
			$this->checkCSS();
		}
	}

	function shortCode($atts) {
		global $post;
		if ( get_post_meta($post->ID, 'desactive_adfever', true) == '1' ) { // Options desactive adfever
			return '';
		}
		
		if($this->_ok!==true) {
			return '';
		}
		
		extract(shortcode_atts(array(
			'type' => '',
			'value' => '',
		), $atts));

		if ( empty($type) || empty($value) ) {
			return '';
		}

		$shop_obj = $this->getShopObj();


		switch ( $type ) {
			case 'top-category' :

				$item = $shop_obj->getTop( $value, 5 );
				if ( !is_array($item["shopping"]["products"]["product"]) ) {
					return '';
				}
				$key  = array_rand( $item["shopping"]["products"]["product"] );
				return $this->buildTableProduct( $item["shopping"]["products"]["product"][$key] );
				break;
			case 'product' :
			default :
					
				// Get item and stores
				$item = $shop_obj->find( $value );
				
				$item = $item["shopping"]["product"];

				if($item) {
					return $this->buildTableProduct( $item );
				}
				else {
					return "";
				}
				break;
		}

		return '';
	}

	function buildTableProduct( $item = null ) {
		if ( is_null($item) || ( isset($item[0]) && empty($item[0]) ) ) {
			//return __( 'Internal error.', 'adfever' );
			return "";
		}

		$stores = isset($item["offers"]["offer"]) ? $item["offers"]["offer"] : (isset($item[0]["offers"]["offer"]) ? $item[0]["offers"]["offer"]: False);
		if (!$stores) return "";
		$item = isset($item['name']) ? $item : $item[0];

		$current_options = get_option( $this->option_name );

		$output = '';
		$output .= '<div class="adfever-container">' . "\n";
			
		if ( isset($current_options['photo']) && $current_options['photo'] == '1' && !empty($item['image']) )
		$output .= '<img src="'.clean_url($item['image']).'" alt="'.attribute_escape($item['name']).'" class="adfever-img" />' . "\n";

		if(isset($current_options['title']) && $current_options['title']==1) {
			$output .= '<h5>'.wp_specialchars($item['name']).'</h5>' . "\n";
		}
		if(isset($current_options['description']) && $current_options['description']==1) {
			$output .= apply_filters('the_adfever_description', $item['description']) . "\n";
		}
			
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
		
		$stores = array_slice( $stores, 0, (isset($current_options['max-store']) ? $current_options['max-store'] : 5));
		foreach( (array) $stores as $store ) {

			$output .= '<tr>' . "\n";
				
			$logo_url = $store['logo'];
			if ( isset($current_options['logos']) && $current_options['logos'] == '1' && !empty($logo_url) )
			$output .= '<td><a '.($current_options['link-target'] ? 'target="_blank"' : '').' '.($current_options['nofollow'] ? 'rel="nofollow"' : '').' style="text-decoration: none;" href="'.$store['link'].'"><img src="'.$logo_url.'" alt="'.attribute_escape($store['merchant']).'" /></a></td>' . "\n"; // Display logo for each stores
			else
			$output .= '<td><a '.($current_options['link-target'] ? 'target="_blank"' : '').' '.($current_options['nofollow'] ? 'rel="nofollow"' : '').' style="text-decoration: none;" href="'.$store['link'].'">'.wp_specialchars($store['merchant']).'</a></td>' . "\n";

			$output .= '<td><a '.($current_options['link-target'] ? 'target="_blank"' : '').' '.($current_options['nofollow'] ? 'rel="nofollow"' : '').' style="text-decoration: none;" href="'.$store['link'].'">'.$store['stock'].'</a></td>' . "\n";
			$output .= '<td><a '.($current_options['link-target'] ? 'target="_blank"' : '').' '.($current_options['nofollow'] ? 'rel="nofollow"' : '').' style="text-decoration: none;" href="'.$store['link'].'"><strong>'.$store['total_price']['value'].'</strong> <br /> <img src="'.ADFEVER_URL.'/inc/images/bt-go.jpg" border="0" /></a></td>' . "\n";
			$output .= '</tr>' . "\n";
		}

		$output .= '</tbody>' . "\n";
		$output .= '</table>' . "\n";
		else :
		$output .= '<p>'.__('No retailers for this product.', 'adfever').'</p>' . "\n";
		endif;
			
		$output .= parent::getCopyright();
		$output .= '</div>' . "\n";

		return $output;
	}

	function checkCSS() {
		if ( isset($_GET['adfever-css']) && $_GET['adfever-css'] == 'shortcode' ) {
			$this->displayCSS();
			exit();
		}
	}

	function displayCSS() {
		$expires_offset = 31536000;
		header('Content-Type: text/css');
		header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
		header("Cache-Control: public, max-age=$expires_offset");

		$current_options = get_option( $this->option_name );
		
		require_once ADFEVER_DIR.'/inc/css/shortcode.css';
		exit();
	}


	
}
?>
