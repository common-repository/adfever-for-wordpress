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





class AdfeverBase {
	var $version 		= VERSION;
	var $option_name		= 'adfever';
	var $_shopObj = null;
	
	// Options
	
	var $option_field_home  = 'home-configuration';
	var $current_options 	= array();

	// Adfever class
	var $_shop_obj = null;
	
	// Catégories
	var $_categories = null;

	// Raw data, used for analyse
	var $raw_item 	= '';
	var $raw_order 	= '';

	// Rewriting
	var $base_url 	= '';
	var $rewriting 	= false;
	var $prefix 	= '';

	// Analyse query
	var $current_item 	= null;
	var $current_id   	= null;
	var $current_cat_id = null;
	var $search_cat_id	= null;
	var $total_items 	= null;
	var $pagename		= '';

	// Counter
	var $count_univers 		= 0;
	var $count_categories 	= 0;

	// Boolean conditional vars
	var $is_adfever			= false;

	var $is_home 	 		= false;
	var $is_front_page		= false;

	var $is_home_type1		= false;
	var $is_home_type2		= false;
	var $is_home_type3		= false;

	var $is_univers			= false;
	var $is_sub_univers		= false;

	var $is_category 		= false;
	var $is_product  		= false;
	var $is_maintenance 	= false;
	var $is_404 			= false;
	var $is_search			= false;

	function activate() {
		$current_options = get_option( $this->option_name );
		if ( $current_options !== false ) {
			return false;
		}

		$new_options = array();
		$group_options = $this->getOptions();

		// Make loop on options array, skip text and another non option value
		foreach ( (array) $group_options as $options ) {
			foreach ( (array) $options as $sub_options ) {
				foreach ( (array) $sub_options as $key_option => $option ) {
					if ( $option['type'] == 'text' || substr($option['type'], 0, 5) == 'title' ) {
						continue;
					} else {
						$new_options[ $key_option ] = $option['default'];
					}
				}
			}
		}

		// Set AID validation to false
		$new_options['valid_aid'] = false;
		

		// Save options
		update_option( $this->option_name, $new_options );

		return true;
	}

	function deactivate() {
		$current_options = get_option( $this->option_name );
		if ( $current_options == false && $current_options['remove'] != '1' ) {
			return false;
		}
		
		

		// Delete options !
		delete_option( $this->option_name );
		
		// Todo : Post meta ? Remove shortcode from post ?

		return true;
	}

	function getOptions( $base = '' ) {
		$options = array(
			'general' => array(
				'account' => array(
					'general_desc' => array ('name' => __('AdFever\'s plugin enables you to insert a price comparison engine in your website. You will offer a new service to your visitors and benefit from additional sources of revenue. To activate the plugin, please fill in the form below. You will then be able to customize it.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ),
					'aid_desc' => array ('name' => __('You need your AdFever\'s AID to activate your price comparison area. If you do not have any AID, please sign up for AdFever and add your website, you will  shortly receive an email with your AID.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
					'aid' => array ('name' => __('Your AdFever\'s AID', 'adfever'), 'type' => 'textbox', 'check' => 'empty,size:8', 'callback' => '', 'default' => '' ), 
					'aid_helper' => array ('name' => __('If you do not have any AdFever\'AID, please sign in or create an account on adfever.com', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
					'link-target' => array ('name' => __('Link target', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1', 'description' => __('The merchant page will open in a new window', 'adfever') ), 
					'referal-link' => array ('name' => __('Referal link', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '0', 'description'=>__('You have a referal link to adfever site', 'adfever') ),
					'nofollow' => array ('name' => __('Nofollow', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1', 'description'=>__('External link won\'t be followed by search engines', 'adfever') ),
					'comparator-active' => array ('name' => __('Activate comparator', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '0', 'description'=>__('You will have a price comparator section in your site', 'adfever') ),
					'prefix' => array ('name' => __('Prefix of price comparator', 'adfever'), 'type' => 'textbox', 'check' => 'empty,slug', 'callback' => '', 'default' => sanitize_title(__('AdFever Comparator', 'adfever')) ), 
					'prefix_helper' => array ('name' => __('This prefix will be used to create the section "Price comparator" of your site. The name is automatically optimized for the creation of the internet addresses. A slug is in lower case, without space, accents. <strong>Sometimes, you must update your permalink after change this value</strong>, otherwise the link of you comparator of price will not work...', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ),
					
		),
				'uninstall' => array(
					'uninstall_desc' => array ('name' => __('If you want delete all options and datas created by AdFever, you must check this below input, save options and deactive plugin.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
					'remove' => array ('name' => __('Delete all AdFever\'s data', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '0' )
		)
		),
			'banners' => array(
				'design' => array(
					'design_desc' => array ('name' => __('You can customize the AdFever template with theses options.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
			
					'colors' => array ('name' => __('Colors', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
						'enable_colors' => array ('name' => __('Customize colors', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '0', 'description' => __('By default, the AdFever plugin uses your theme\'s colors. If you want to manually customize the colors, please activate this option.', 'adfever') ),
						'text' => array ('name' => __('Text (title, description)', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#000000' ), 
						'border-table' => array ('name' => __('Border table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#FFFFFF' ), 
						'bg-header-table' => array ('name' => __('Background header table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#FFFFFF' ), 
						'text-header-table' => array ('name' => __('Text header table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#000000' ), 
						'bg-table' => array ('name' => __('Background table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#FFFFFF' ), 
						'text-table' => array ('name' => __('Text table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#000000' ), 
						'photo-logs' => array ('name' => __('Photos & Logos', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
						
						'title' => array ('name' => __('Display title', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1' ), 
						'description' => array ('name' => __('Display description', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1' ), 
						
						'photo' => array ('name' => __('Display products\' picture', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1' ), 
						'logos' => array ('name' => __('Display stores\' logos', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '1' ), 
						'max-store' => array ('name' => __('Maximum number of retailers to dispay for each product', 'adfever'), 'type' => 'selectbox', 'check' => '', 'callback' => 'select_quantity_stores', 'default' => '3' ), 
						
		)
		),
			'comparator' => array(
				'design' => array(
					'design_desc' => array ('name' => __('You can customize the AdFever template with theses options.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
			
					'c-colors' => array ('name' => __('Colors', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
						'c-enable-colors' => array ('name' => __('Customize colors', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => '0', 'description' => __('By default, the AdFever plugin uses your theme\'s colors. If you want to manually customize the colors, please activate this option.', 'adfever') ),
						'c-title' => array ('name' => __('Titles', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#b3a948' ), 
						'c-text' => array ('name' => __('Text (description)', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#212121' ), 
						'c-price' => array ('name' => __('Price', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#ff6600' ),
						'c-links-navigation' => array ('name' => __('Navigation links', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#235c89' ), 
						'c-border-best-price' => array ('name' => __('"Best price" border frame', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#215c88' ), 
						'c-border-table' => array ('name' => __('Border table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#d1d1d1' ), 
						'c-bg-table' => array ('name' => __('Background table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#FFFFFF' ), 
						'c-text-table' => array ('name' => __('Text table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#212121' ), 
						'c-bg-header-table' => array ('name' => __('Background header table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#FFFFFF' ), 
						'c-text-header-table' => array ('name' => __('Text header table', 'adfever'), 'type' => 'colorbox', 'check' => '', 'callback' => '', 'default' => '#212121' ), 

					'layout' => array ('name' => __('Layout', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
						'c-sub-categories' => array ('name' => __('Number of sub-categories to display per category', 'adfever'), 'type' => 'selectbox', 'check' => '', 'callback' => 'select_quantity_sub_category', 'default' => '3' ), 
						'c-products-per-page' => array ('name' => __('Number of products to display per page', 'adfever'), 'type' => 'selectbox', 'check' => '', 'callback' => 'select_quantity_products_per_page', 'default' => '20' ), 
						'top-category' => array('name' => __('Display top category', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => 1), 
					
					'sidebar' => array('name' => __('Display sidebar', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => 1), 
		
					
		),
				'categories' => array(
					'categories_desc' => array ('name' => __('You can select the categories of products displaying in your price comparator. By default all the categories of products are shown, according to the subject of your site, it maybe relevant to limit the selected categories.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
			
					'categories_title' => array ('name' => __('Categories of your price comparison engine', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
						'categories_selection' => array ('name' => __('Choice', 'adfever'), 'type' => 'multiples_cat', 'check' => '', 'callback' => '', 'default' => '' ),
		),
				'visitors' => array(
					'visitors_desc' => array ('name' => __('You can choose to make your price comparison pages accessible from search engines such as Google or Yahoo!.', 'adfever'), 'type' => 'text', 'check' => '', 'callback' => '', 'default' => '' ), 
			
					'ranking' => array ('name' => __('Search engine optimization', 'adfever'), 'type' => 'title:h3', 'check' => '', 'callback' => '', 'default' => '' ),
				
				
					'noindex' => array('name' => __('Allow to be indexed', 'adfever'), 'type' => 'checkbox', 'check' => '', 'callback' => '', 'default' => 1, 'description' => __('Include your price comparison engine in search engine\'s index.', 'adfever')),
		)
		)
		);

		if ( !empty($base) ) {
			return $options[$base];
		}
		return $options;
	}

	/**
	 * Try if AID Adfever is valid or not
	 *
	 */
	function isValidAid( $from_db_only = false, $save_option = false, $return_message = false, $original_aid = null ) {
		// Get option
		$current_options = get_option( $this->option_name );

		// Only DB.
		if ( $from_db_only == true ) {
			if ( $current_options['valid_aid'] == true ) {
				return true;
			}
			return false;
		}

		// Init shopping class
		$shopping_object = $this->getShopObj();

		// Check site ID
		$result = $shopping_object->checkSiteId();
		$result = $result['shopping'];

		if ( $result['result']['value'] == 1 && $current_options['aid'] != 0 ) { // Valid
			if ( $save_option == true ) {
				$current_options['valid_aid'] = true;
				update_option( $this->option_name, $current_options );
			}
			return true;
		} else { // Invalid
			if ( $save_option == true ) {
				//$current_options['valid_aid']	= false;
				$current_options['aid'] = $original_aid;
				//var_dump($options);
				update_option( $this->option_name, $current_options );
			}

			if ( $return_message == true ) {
				if ( isset($result['errors']['error']) )
				return $result['errors']['error'];
				elseif ( isset($result['warnings']['warning']) )
				return $result['warnings']['warning'];
				else
				return __( 'Your AID is invalid.', 'adfever' );
			}

			return false;
		}
	}

	function getOption( $key = '', $type = 'integer', $group = '', $parent_key = '' ) {
		if ( empty($key) ) {
			return '';
		}

		if ( $type == 'integer' ) {
				
			$value = (int) $this->current_options[$key];
			if ( $value == 0 ) {
				$default_options = $this->getOptions( $group );
				$value = $default_options[$parent_key][$key]['default'];
				unset($default_options);
			}
				
		}

		return $value;
	}

	function getCopyright() {
		$options = get_option('adfever');
		$aid = $options['aid'];
		$referal = isset($options['referal-link']) ? $options['referal-link'] : 0;
		$target = isset($options['link-target']) ? $options['link-target'] : 0;
		
		if($referal) {
			return '
				<p class="adfever-credits">
					<a '.($target ? 'target="_blank"' : '').' href="http://www.adfever.com/?wp_sid='.$options['aid'].'">'.__('Technology AdFever', 'adfever').'</a>
				</p>';
		}
		else {
			return '
				<p class="adfever-credits">
				'.__('Technology AdFever', 'adfever').'
				</p>';
		}
	}
	
	function & getShopObj() {
		if(is_null($this->_shopObj)) {
			$current_options = get_option( $this->option_name );
			$this->_shopObj = new Shopping ( $current_options['aid'], ADFEVER_UUID );
		}
		
		return $this->_shopObj;
	}
	
	function checkJS() {
		if ( isset($_GET['adfever-js']) && $_GET['adfever-js'] == 'comparator' ) {
			$this->loadJavascript();
			exit();
		}
	}

	function loadJavascript() {
		$expires_offset = 31536000;
		header('Content-Type: text/javascript');
		header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
		header("Cache-Control: public, max-age=$expires_offset");
		$JS = <<<ENDJS
if(typeof(getElementsByClassName)=='undefined') {
	var getElementsByClassName = function (className, tag, elm){
		if (document.getElementsByClassName) {
			getElementsByClassName = function (className, tag, elm) {
				elm = elm || document;
				var elements = elm.getElementsByClassName(className),
					nodeName = (tag)? new RegExp("\\b" + tag + "\\b", "i") : null,
					returnElements = [],
					current;
				for(var i=0, il=elements.length; i<il; i+=1){
					current = elements[i];
					if(!nodeName || nodeName.test(current.nodeName)) {
						returnElements.push(current);
					}
				}
				return returnElements;
			};
		}
		else if (document.evaluate) {
			getElementsByClassName = function (className, tag, elm) {
				tag = tag || "*";
				elm = elm || document;
				var classes = className.split(" "),
					classesToCheck = "",
					xhtmlNamespace = "http://www.w3.org/1999/xhtml",
					namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace : null,
					returnElements = [],
					elements,
					node;
				for(var j=0, jl=classes.length; j<jl; j+=1){
					classesToCheck += "[contains(concat(' ', @class, ' '), ' " + classes[j] + " ')]";
				}
				try	{
					elements = document.evaluate(".//" + tag + classesToCheck, elm, namespaceResolver, 0, null);
				}
				catch (e) {
					elements = document.evaluate(".//" + tag + classesToCheck, elm, null, 0, null);
				}
				while ((node = elements.iterateNext())) {
					returnElements.push(node);
				}
				return returnElements;
			};
		}
		else {
			getElementsByClassName = function (className, tag, elm) {
				tag = tag || "*";
				elm = elm || document;
				var classes = className.split(" "),
					classesToCheck = [],
					elements = (tag === "*" && elm.all)? elm.all : elm.getElementsByTagName(tag),
					current,
					returnElements = [],
					match;
				for(var k=0, kl=classes.length; k<kl; k+=1){
					classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
				}
				for(var l=0, ll=elements.length; l<ll; l+=1){
					current = elements[l];
					match = false;
					for(var m=0, ml=classesToCheck.length; m<ml; m+=1){
						match = classesToCheck[m].test(current.className);
						if (!match) {
							break;
						}
					}
					if (match) {
						returnElements.push(current);
					}
				}
				return returnElements;
			};
		}
		return getElementsByClassName(className, tag, elm);
	};
}


var old_onload = window.onload;

window.onload = function() {
	if(old_onload) {
		old_onload();
	}
	
	var images = getElementsByClassName('adf_img_38');
	
	for(var i=0; i<images.length; i++) {
		var img = images[i];
		
		if( (typeof(img.naturalWidth)=='undefined' && !img.complete) || img.naturalWidth == 0 ) {
			img.src='ADFEVER_URL/inc/images/category/image_novisuel_38x38.gif';
    	}
	}
	
	var images = getElementsByClassName('adf_img_200');
	
	for(var i=0; i<images.length; i++) {
		var img = images[i];
		
		if( (typeof(img.naturalWidth)=='undefined' && !img.complete) || img.naturalWidth == 0 ) {
			img.src='ADFEVER_URL/inc/images/topcategory/image_novisuel_200x200.gif';
    	}
	}
	
	var images = getElementsByClassName('adf_img');
	
	for(var i=0; i<images.length; i++) {
		var img = images[i];
		
		if( (typeof(img.naturalWidth)=='undefined' && !img.complete) || img.naturalWidth == 0 ) {
			img.src='ADFEVER_URL/inc/images/category/image_novisuel_85x85.gif';
    	}
	}
}

ENDJS;

		echo str_replace('ADFEVER_URL', SHOBOT_URL, $JS);
	}

	
}
?>
