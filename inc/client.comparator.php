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





class AdfeverClient_Comparator extends AdfeverBase {

	var $active_cats = array();
	var $inactive_cats = array();
	
	var $_ok = false;


	/**
	 * Init Adfever Client comparator, registrer filters, etc...
	 **/
	function AdfeverClient_Comparator() {
		$this->_ok = parent::isValidAid( true );
		
		// Set prefix comparator
		$this->current_options = get_option( $this->option_name );
		$this->prefix = $this->current_options['prefix'];

		// URL and Rewriting
		add_action('init', array(&$this, 'rewriteInit') 	);
		add_filter('query_vars' , array(&$this, 'addQueryVars'));
		add_action('parse_query', array(&$this,'parseQuery'));

		$this->checkJS();

		// Home ? page ?
		add_action( 'template_redirect', array(&$this, 'checkHomePage') );

		if ( !is_admin() ) {
			// Check for CSS, load CSS and exit !
			$this->checkCSS();

		}
		
		
	}
	
	function &getInstance()
	{
		static $singleton;

		if (!$singleton)
			$singleton = new AdfeverClient_Comparator();

		return $singleton;
	}
	

	/**
	 * Be tolerant with users URL, allow to redirect the adresse : monblog.com/adfever-comparator to the adfever home if rewriting is enabled.
	 **/
	function checkHomePage() {
		global $wp_query;
		
		if ( $wp_query->query_vars['pagename'] == $this->prefix && $wp_query->is_404 == true && $this->rewriting == true ) {
			wp_redirect( $this->link('home') );
			exit();
		}
		return false;
	}

	/**
	 * Add a robots meta HTML for allow or not crowlings... Adfever option.
	 **/
	function displayRobots() {
       if ( $this->current_options['noindex'] != 1 )
               echo "\n" . '<meta name="robots" content="noindex,nofollow" />  <!-- adfever -->' . "\n";
     }

	/**
	 * Determine if adfever rewriting must be init and build base URL for adfever
	 **/
	function rewriteInit() {
		
		global $wp_rewrite;

		// Detect permalink type & construct base URL for local links
		$this->base_url = get_option('home') . '/';
		
		if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) { // Permalink
			
			$this->rewriting = true;
			//$this->base_url .= ( substr($wp_rewrite->front, 0, 1) == '/' ) ? substr($wp_rewrite->front, 1, strlen($wp_rewrite->front)) : $wp_rewrite->front;
			//wp_die($this->base_url);
			$this->base_url .= $wp_rewrite->root; // set to "index.php/" if using that style
			$this->base_url .= $this->prefix . '/';

			add_filter('generate_rewrite_rules', array(&$this, 'createRewriteRules' ) );

		} else { // Old school links

			$this->rewriting = false;
			$this->base_url .= '?' . $this->prefix . '=';

		}
	}

	/**
	 * Add rewrite rules for allow adfever rewriting with order param
	 **/
	function createRewriteRules() {
		global $wp_rewrite;

		// Add rewrite tokens for main rewriting
		$token_adfever = '%'.$this->prefix.'%';
		$wp_rewrite->add_rewrite_tag($token_adfever, '(.+?)', $this->prefix.'=');

		// Add seconds parameter, for order by example
		$key_adfever_order = '%'.$this->prefix.'-order%';
		$wp_rewrite->add_rewrite_tag($key_adfever_order, '([^/]+?)', $this->prefix.'-order=');

		// Build rules
		$adfever_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->root . "$this->prefix/$token_adfever/$this->prefix-order/$key_adfever_order/", EP_NONE, true, false );

		// Add new rules in WP array
		$wp_rewrite->rules = $adfever_rewrite + $wp_rewrite->rules;

		return $wp_rewrite->rules;
	}

	/**
	 * Add Adfever keywords in WordPress query vars to be accessible with WordPress fonction
	 **/
	/*function addQueryVars( $query_vars = array() ) {
		// Execute hook once !
		remove_filter('query_vars', array(&$this, 'addQueryVars'));

		$query_vars[] = $this->prefix;
		$query_vars[] = $this->prefix.'-order';

		return $query_vars;
	}*/

	/**
	 * Parse query and determine if adfever process must be launch...
	 **/
	function parseQuery() {
		// Execute hook once !
		remove_action('parse_query', array(&$this, 'parseQuery'));

		$this->raw_item  = stripslashes(get_query_var($this->prefix));
		$this->raw_order = stripslashes(get_query_var($this->prefix.'-order'));
		if ( get_magic_quotes_gpc() ) { // why so many freakin' slashes?
			$this->raw_item  = stripslashes($this->raw_item);
			$this->raw_order = stripslashes($this->raw_order);
		}

		if ( !empty($this->raw_item) ) {

			// Parse query
			$this->is_adfever = true;
			$this->analyseQuery();

			// Add JS for product
			if ( $this->is_product == true ) {
				wp_enqueue_script( 'jquery-tablesorter', ADFEVER_URL . '/lib/jquery-tablesorter/jquery.tablesorter.min.js', array('jquery'), '2.0.3' );
				wp_enqueue_script( 'adfever-comparator', ADFEVER_URL . '/inc/js/comparator.js', array('jquery', 'jquery-tablesorter'), $this->version );
				wp_enqueue_style ( 'jquery-tablesorter', ADFEVER_URL . '/lib/jquery-tablesorter/style.css', array(), '2.0.3', 'all' );
			}

			// Add CSS on header
			wp_enqueue_style( 'adfever-comparator', get_bloginfo('siteurl').'/?adfever-css=comparator', array(), $this->version, 'all' );
			wp_enqueue_script('loadimages', get_bloginfo('siteurl').'/?adfever-js=comparator', array(), $this->version, 'all' );

			// Redirect to specific template
			add_action('template_redirect', array(&$this, 'templateRedirect'), 1);
			add_filter('wp_title', array(&$this, 'addTitle') );
			add_action( 'wp_head', array(&$this, 'displayRobots') );
			return true;
		}
		return false;
	}
	
	function addQueryVars($query_vars = array()) {
		$current_options = get_option( 'adfever' );
		$prefix = $current_options['prefix'];
		
		remove_filter('query_vars', 'addQueryVars');
		$query_vars[] = $prefix;
		$query_vars[] = $prefix.'-order';

		return $query_vars;
	}



	/**
	 * Specify a title for adfever comparator, optimization SEO
	 **/
	function addTitle( $title = '', $sep = '&raquo;' ) {
		if ( !empty($this->pagename) ) {
			return $sep . wp_specialchars($this->pagename);
		}
		return __('Home', 'adfever') . ' ' . $sep . ' ' . __('Price comparator', 'adfever') . ' ' . $sep;
	}

	/**
	 * Redirect view to adfever template if it exist...
	 **/
	function templateRedirect() {
		if ( !empty($this->raw_item) ) {

			$template = '';
			if ( is_file( TEMPLATEPATH . '/' . ADFEVER_THEME_FILE ) ) {
				$template = TEMPLATEPATH . '/' . ADFEVER_THEME_FILE;
			}
			else if ( is_file( ADFEVER_DIR . '/samples/' . ADFEVER_THEME_FILE ) ) {
				$template = ADFEVER_DIR . '/samples/' . ADFEVER_THEME_FILE;
			}
			else {
				wp_die( sprintf(__('Template file <code>%s</code> are required for this plugin.', 'adfever'), ADFEVER_THEME_FILE ) );
			}

			if ( !empty($template) ) {
				load_template($template);
				exit();
			}
		}

		return false;
	}

	/**
	 * Count how many univers and categories is selected
	 **/
	function countUserSelection() {
		$this->count_univers 	= count($this->current_options['multiples_cat']['univers']);
		$this->count_categories = count($this->current_options['multiples_cat']['categories']);
	}

	/**
	 * Analyse the query for determine wich template must be loaded...
	 **/
	function analyseQuery() {


		// Check for valid AID
		$result = parent::isValidAid( true );

		if ( isset($this->current_options['categories_selection']) && $this->current_options['categories_selection'] == 'sel' ) $this->countUserSelection();

		// Init shopping before API Call
		// Init only if Shopping isn't already loaded

		$shop_obj = $this->getShopObj();


		if ( $result == true ) {
				
			if ( $this->raw_item == 'home') {

				$this->is_front_page = true;

				if ( $this->current_options['categories_selection'] == 'sel' ) {

					// Load pre-saved configuration.
					foreach( (array) $this->current_options[$this->option_field_home] as $class_var_name => $value ) {
						$this->{$class_var_name} = $value;



					}

				}
				else { // All categories, no filter, too easy !

					$this->is_home = true;
					$this->tpl_file = 'home.php';

				}

			}
			else if( $this->raw_item == 'search' ) {

				$this->is_search 		= true;
				$this->tpl_file			= 'search.php';
				$this->current_item 	= stripslashes( $_GET['terms']  );
				$this->current_cat_id 	= intval( $_GET['cat_id'] );

				// Search on univers, but only selected category
				if ( $this->current_options[$this->option_field_home]['is_home_type2'] == true &&
				in_array( $this->current_cat_id, $this->current_options['multiples_cat']['univers'] ) ) {

					$this->search_cat_id = $this->current_options['multiples_cat']['categories'];

				}
				else if ( $this->current_options[$this->option_field_home]['is_home_type1'] == true && in_array( $this->current_cat_id, $this->current_options['multiples_cat']['univers'] ) ) {

					if ( in_array( $this->current_cat_id, $this->current_options['multiples_cat']['categories'] ) ) {
						$this->search_cat_id = $this->current_cat_id;
					}
					else {
						$this->search_cat_id = $this->getCategoryHierarchy( $this->current_cat_id );
					}
				}
				else {

					$this->search_cat_id = $this->current_cat_id;

				}

			}
			else {
					
				$parts = explode('/', $this->raw_item);

				if ( $parts[0] == 'category' ) {

					$this->tpl_file 	= 'category.php';
					$this->is_category 	= true;
					$this->current_item = $parts[1];

					$cat_id = $this->isUnivers($this->current_item, true);
						
					if ( $cat_id !== false ) {
						$this->current_cat_id	= $cat_id;
						$this->tpl_file			= 'univers.php';
						$this->is_univers 		= true;
					}

				}
				else if ( $parts[0] == 'product' ) {

					$this->tpl_file 	= 'product.php';
					$this->is_product 	= true;
					$this->current_item = $parts[1];

				}
					
			}

		}
		else {
			$this->tpl_file = 'maintenance.php';
			$this->is_maintenance = true;
		}

		// If empty template, load 404.
		if ( empty($this->tpl_file) ) {
			$this->tpl_file = '404.php';
			$this->is_404 = true;
		}



	}

	function isUnivers( $current_item = 0, $sub_category = false ) {
		$shop_obj = $this->getShopObj();

		$all_categories = $this->getCategories();

		$all_categories = $all_categories['shopping']['category'][0]['categories']['category'];
		foreach( (array) $all_categories as $category ) {

			if ( $sub_category == true && isset( $category["categories"]["category"] ) && is_array( $category["categories"]["category"] ) && !empty($category["categories"]["category"]) ) {
				foreach( $category["categories"]["category"] as $sub_category ) {
					if ( isset( $sub_category["categories"]["category"] ) && is_array( $sub_category["categories"]["category"] ) && !empty($sub_category["categories"]["category"]) ) {
						if ( $sub_category['kwd'] == $current_item || (int) $current_item == $sub_category['id'] ) {
							return $sub_category['id'];
						}
					}
				}
			}

			if ( $category['kwd'] == $current_item || (int) $current_item == $category['id'] ) {
				return $category['id'];
			}

		}

		return false;
	}

	function render() {
		$shop_obj = $this->getShopObj();
			
		// Fixed paged
		$paged = (int) get_query_var('paged');
		if ( $paged < 1 ) $paged = 1;

		// Prepare datas for template
		if ( $this->is_home == true ) {
			$all_categories 	= $this->filterCategories( $this->getCategories(), 'both', 'render_home' );
			$max_cat_to_display = parent::getOption( 'c-sub-categories', 'integer', 'comparator', 'design' ); // Default 3

		}
		else if ( $this->is_univers == true ) {
				
			$categories = $this->filterCategories( $this->getCategories($this->current_cat_id), 'categories', 'render_univers' );
				
		}
		else if ( $this->is_category == true ) {
			
			$products = $shop_obj->findForCategory( $this->current_item, $paged, $this->current_options['c-products-per-page'], SORT_PRICE, (( $this->raw_order == 'less' ) ? 'DESC' : 'ASC') );
			$this->current_cat_id 	= $products["shopping"]["meta"]["category_id"];
			$this->total_items 		= $products["shopping"]["meta"]["total"];
			$products 				= $products["shopping"]["products"]["product"];

		}
		else if ( $this->is_search == true ) {
			if($this->raw_order) {
				$sort = SORT_PRICE;
			}
			else {
				$sort = false;
			}
			$products = $shop_obj->search( $this->current_item, $this->search_cat_id, $paged, $this->current_options['c-products-per-page'], $sort, (( $this->raw_order == 'less' ) ? 'ASC' : 'DESC') );
			$this->total_items 	= $products["shopping"]["meta"]["total"];
			$quantity_results 	= $this->total_items;
			$products 			= $products["shopping"]["products"]["product"];
			$terms 				= $this->current_item;

		}
		else if( $this->is_product == true ) {

			$product = $shop_obj->find( $this->current_item );
			$external = (parent::getOption( 'link-target', 'integer', 'general', 'account' )==1 ? 1 : 0);
			$nofollow = (parent::getOption('nofollow', 'integer', 'general', 'account')==1 ? 1 : 0);
			

				
			$product = $product["shopping"]["product"][0];

			if ( empty($product) || is_string($product) ) {
				$this->tpl_file = '404.php';
				$this->is_404 = true;
			}
			else {
				
				$this->pagename		  = $product['name'];
				$this->current_id 	  = $this->current_item;
				$this->current_cat_id = $product['category'][0]['id'];
				$stores  = $product["offers"]["offer"];
			}

		}

		// Put template in buffer, for work on render
		ob_start();
		$tpl_file = $this->getFilePathTemplate($this->tpl_file);
		if ( !empty($tpl_file) ) {
			include( $tpl_file );
		}
		else {
			$tpl_file = $this->getFilePathTemplate('404.php');
			if ( !empty($tpl_file) ) {
				include( $tpl_file );
			}
			else {
				echo __( 'Adfever - Internal error - Impossible to load a template. Please reinstall the plugin.', 'adfever' );
			}
		}
		$output = ob_get_contents();
		ob_end_clean();

		// Include bloc
		$output = str_replace( '{include:search}', $this->includeTemplate('bloc.search.php'), $output );
		$output = str_replace( '{include:top-products}', $this->includeTemplate('bloc.top-products.php'), $output );
		$options = get_option('adfever');
		
		if(isset($options['top-category']) && $options['top-category']==1) {
			$output = str_replace( '{include:top-categories}', $this->includeTemplate('bloc.top-categories.php'), $output );
		}
		else {
			$output = str_replace('{include:top-categories}', '', $output);
		}
		$output = str_replace( '{include:breadcrumb}', $this->includeTemplate('bloc.breadcrumb.php'), $output );
		$output = str_replace( '{pagination}', $this->getPagination(), $output );

		// Add container
		$output = "\n<div class='adfever-container-comparator'> \n" . $output . "\n</div> <!-- end <div class='adfever-container-comparator'> --> \n";

		$options = get_option('adfever');
		
		$output .= $this->getCopyright();
		
		
		return apply_filters( 'adfever_render', $output, $this->tpl_file );
	}

	function includeTemplate( $file_name = '' ) {
		
		// Prepare global variables
		if ( $file_name == 'bloc.top-categories.php') {

			$top_categories = $this->getTopCategories();

		}
		else if ( $file_name == 'bloc.top-products.php' ) {

			$top_products = $this->getTopProducts();

		}
		else if ( $file_name == 'bloc.search.php' ) {

			if ( $this->rewriting == true ) {
				$adfever_search_action = $this->base_url . 'search';
				$adfever_search_hidden = array();
			}
			else {
				$adfever_search_action = '';
				$adfever_search_hidden = array( 'name' => $this->prefix, 'value' => 'search' );
			}

			$all_categories = $this->getSearchCategories();
			$current_cat	= $this->current_cat_id;
			$terms 			= attribute_escape(stripslashes(@$_GET['terms']));

		}
		else if ( $file_name == 'bloc.breadcrumb.php' ) {

			$breadcrumb = $this->getBreadCrumb();
				
			$active_cats = $this->active_cats;
			$inactive_cats = $this->inactive_cats;
				
			$is_sel = ( $this->current_options['categories_selection'] == 'sel' );

		}

		ob_start();
		if ( is_file( ADFEVER_CUSTOM_TEMPLATE . DIRECTORY_SEPARATOR . $file_name ) ) {
			include( ADFEVER_CUSTOM_TEMPLATE . DIRECTORY_SEPARATOR . $file_name );
		}
		else if ( is_file( ADFEVER_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name ) ) {
			include( ADFEVER_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name );
		}
		$output = ob_get_contents();
		ob_end_clean();

		return apply_filters( 'adfever_include_template', $output, $file_name );
	}

	function getSearchCategories() {
		if ( $this->current_options['categories_selection'] == 'sel' ) {

			if ( isset($this->current_options[$this->option_field_home]['is_home_type3']) && $this->current_options[$this->option_field_home]['is_home_type3']== true ) {

				$all_categories = $this->getCategories( $this->current_cat_id );
				return $all_categories["shopping"]["category"];

			}
			else { // Home type 2 or 3

				$all_categories = $this->getCategories();
				$all_categories = $all_categories['shopping']['category'][0]['categories']['category'];

				return $this->filterCategoriesList( 'both', $all_categories, $this->current_options['multiples_cat'] );

			}

		}
		else { // all categories

			$all_categories = $this->getCategories();
			$all_categories = $all_categories['shopping']['category'][0]['categories']['category'];

			array_unshift($all_categories, array('id' => '', 'name' => __('All categories', 'adfever')) );

		}

		return $all_categories;
	}



	function getBreadCrumb() {

		$shop_obj = $this->getShopObj();
		$breadcrumb = array();

		if ( $this->current_cat_id != null && $this->is_front_page == false ) {

			$breadcrumb = $shop_obj->getBreadCrumb( $this->current_cat_id );
				
			if ( is_array($breadcrumb['shopping']['breadcrumb']) ) {
				$breadcrumb = $breadcrumb['shopping']['breadcrumb']['category'][0];
			}

			if ( @$this->current_options[$this->option_field_home]['is_home_type2'] == true ||
			@$this->current_options[$this->option_field_home]['is_home_type3'] == true ) { // Remove home for some home type
				$breadcrumb = $breadcrumb['category'][0];

				if (
				$this->current_options[$this->option_field_home]['is_home_type3'] == true ||
				$this->current_options[$this->option_field_home]['is_sub_univers'] == true ) {
					$breadcrumb['link'] = 'false';
				}
			}
				
		}

		return $breadcrumb;
	}

	function getTopCategories() {
		$shop_obj = $this->getShopObj();

		if ( $this->current_options['categories_selection'] == 'sel' ) {

			if (
			@$this->current_options[$this->option_field_home]['is_home_type2'] == true ||
			@$this->current_options[$this->option_field_home]['is_home_type1'] == true
			) {

				if ( $this->is_front_page == true ) {
					$cat_param = $this->current_options['multiples_cat']['categories'];
				}
				else {
					//$cat_param = $this->getCategoryHierarchy($this->current_cat_id);
					$cat_param = $this->getChildIds($this->current_cat_id);
				}

			}
			else {
					
				$cat_param = $this->current_cat_id;

			}

		}
		else { // All categories

			$cat_param = $this->current_cat_id;
			if ( (int) $cat_param == 0 ) {
				$cat_param = '';
			}

		}

		$top_categories = $shop_obj->getTopCategories( $cat_param, 4 );

		return $this->filterCategoriesImages((isset($top_categories['shopping']['categories']['category']) ? $top_categories['shopping']['categories']['category'] : array()), 'getTopCategories' );
	}

	function getTopProducts() {

		$shop_obj = $this->getShopObj();

		if ( $this->is_front_page == true ) {
			if ( $this->current_options['categories_selection'] == 'sel' ) {
				if ( @$this->current_options[$this->option_field_home]['is_home_type3'] == true ) {

					$c = $this->current_cat_id;

				}
				else if (
				@$this->current_options[$this->option_field_home]['is_home_type2'] == true ||
				@$this->current_options[$this->option_field_home]['is_home_type1'] == true
				) {

					$c = $this->current_options['multiples_cat']['categories'];

				}
					
			}
		}
		else {
			$c = $this->current_cat_id;
		}

		$top_products = $shop_obj->getTop( $c, 4 );
		$top_products = @$top_products['shopping']['products']['product'];

		return $top_products;
	}

	function getFilePathTemplate( $file_name = '' ) {
		if ( is_file(ADFEVER_CUSTOM_TEMPLATE . DIRECTORY_SEPARATOR . $file_name ) ) {
			$file = ADFEVER_CUSTOM_TEMPLATE . DIRECTORY_SEPARATOR . $file_name;
		}
		else if ( is_file( ADFEVER_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name ) ) {
			$file = ADFEVER_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file_name;
		}
		else {
			$file = '';
		}

		return apply_filters( 'adfever_get_filepath_template', $file, $file_name );
	}

	function getPagination() {
		// Fix paged
		$paged = (int) get_query_var('paged');
		if ( $paged < 1 ) $paged = 1;

		// Build base
		if ( $this->is_search == true ) {
			$cat_id = ( (int) $_GET['cat_id'] != 0 ) ? '&amp;cat_id=' . (int) $_GET['cat_id'] : '';
			$order  = ( !empty($this->raw_order) )   ? '&amp;' .  $this->prefix. '-order=' . $this->raw_order : '';
			if ( $this->rewriting == true ) {
				$uri = $this->base_url . 'search?terms=' . stripslashes($_GET['terms']) . $cat_id . $order . '&amp;page=%#%';
			} 
			else {
				$uri = $this->base_url . 'search&amp;terms=' . stripslashes($_GET['terms']) . $cat_id . $order . '&amp;page=%#%';
			}
		} 
		else {
			$uri = $this->base_url . $this->raw_item . '/' .$this->prefix. '-order/' . $this->raw_order . '/page/%#%';
		}

		$this->current_options['c-products-per-page'] = parent::getOption( 'c-products-per-page', 'integer', 'comparator', 'design' ); // Default 20

		// Use WP functions build pagination
		$page_links = paginate_links( array(
			'base' =>  clean_url($uri),
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil($this->total_items / $this->current_options['c-products-per-page']),
			'current' => $paged
		));

		if ( $page_links ) return "<div class='adfever-pagination'><div class='adfever-pages'>$page_links</div><br class='clear' /></div>";
		return '';
	}

	function link( $object_type = 'category', $id = null ) {
		
		// Invalid link ? Put home...
		if ( $object_type == 'home' || ($id == null && empty($id)) || ($object_type == 'category' && ($id == 1||$id == 'index')) ) {
			return clean_url($this->base_url . sanitize_title(__('home', 'adfever')));
		}

		if ( $object_type == 'category' ) {
			return clean_url($this->base_url . 'category/' . $id);
		} 
		else if ( $object_type == 'product' ) {
			return clean_url($this->base_url . 'product/' . $id);
		} 
		else if ( $object_type == 'category-order' ) {

			// Prepare paged
			if ( $this->rewriting == true ) {
				$paged = (int) get_query_var('paged');
				$paged = ( $paged != 1 && $paged != 0 ) ? '/page/' . $paged : '';
				return clean_url($this->base_url . $this->raw_item . '/' .$this->prefix. '-order/' . $id . $paged);
			} 
			else {
				$paged = (int) get_query_var('paged');
				$paged = ( $paged != 1 && $paged != 0 ) ? '&amp;page=' . $paged : '';
				return clean_url($this->base_url . $this->raw_item . '&amp;' .$this->prefix. '-order=' . $id . $paged);
			}

		} 
		else if ( $object_type == 'search-order' ) {

			// Prepare paged
			$paged = (int) $_GET['page'];
			$paged = ( $paged != 1 && $paged != 0 ) ? '/page/' . $paged : '';

			// Prepare cat ID
			$cat_id = ( (int) $_GET['cat_id'] != 0 ) ? '&amp;cat_id=' . (int) $_GET['cat_id'] : '';

			if ( $this->rewriting == true ) {
				$url_suffix = '?terms=' . stripslashes($_GET['terms']) . $cat_id . '&amp;' .  $this->prefix. '-order=' . $id . str_replace('/page/', '&amp;page=', $paged);
			} 
			else {
				$url_suffix = '&amp;terms=' . stripslashes($_GET['terms']) . $cat_id . '&amp;' .  $this->prefix. '-order=' . $id . str_replace('/page/', '&amp;page=', $paged);
			}

			return clean_url($this->base_url . 'search' . $url_suffix);
		}

		return '';
	}


	function filterCategories( $categories, $category_context = '', $images_context = '' ) {
		
		$categories = $categories['shopping']['category'][0]['categories']['category'];
		

		if (
			$this->current_options[$this->option_field_home]['is_home_type1'] ||
			$this->current_options[$this->option_field_home]['is_home_type2']
		) {
			$categories = $this->filterCategoriesList( $category_context, $categories, $this->current_options['multiples_cat'] );
		}
		$categories = $this->filterCategoriesImages( $categories, $images_context );

		return $categories;
	}

	function filterCategoriesImages( $categories = array(), $context = '' ) {
		// Set local images ?
		foreach( (array) $categories as $key => $category ) {

			$file_name = $category['kwd'] . '.png';
			if ( is_file(ADFEVER_CUSTOM_IMAGES_DIR . DIRECTORY_SEPARATOR . $file_name ) ) {

				$categories[$key]['img'] = ADFEVER_CUSTOM_IMAGES_URL . '/' . $file_name;
					
			} 
			else if ( is_file( ADFEVER_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'img-plugin-200x200' . DIRECTORY_SEPARATOR . $file_name ) ) {
					
				$categories[$key]['img'] = ADFEVER_URL . '/inc/images/img-plugin-200x200/' . $file_name;

			} 
			else { // No file, put default image

				$file_name = 'no-image.png';
				if ( is_file( ADFEVER_CUSTOM_IMAGES_DIR . DIRECTORY_SEPARATOR . $file_name ) ) {
					$categories[$key]['img'] = ADFEVER_CUSTOM_IMAGES_URL . '/' . $file_name;
				} 
				else if ( is_file( ADFEVER_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'img-plugin-200x200' . DIRECTORY_SEPARATOR . $file_name ) ) {
					$categories[$key]['img'] = ADFEVER_URL . '/inc/images/img-plugin-200x200/' . $file_name;
				}

			}
		}

		return apply_filters( 'adfever_filter_categories_image', $categories, $context );
	}


	function filterSubCat($categories, $multiples_cat) {

		$keep = array();

		if(is_null($categories)) return;

		foreach($categories as $key=>$category) {
				
			if(isset($category['categories']['category'])) {
				$res = $this->filterSubCat($category['categories']['category'], $multiples_cat);
				if($res) {
						
					if(count($res)==1 && $category['level']!=1) {
						$category = $res[0];
					}
					else {
						$category['categories']['category'] = $res;
					}
				}
			}
			
			if(
				in_array($category['id'], $multiples_cat['univers'])
				||
				in_array($category['id'], $multiples_cat['categories'])
			) {
				$keep[] = $category;
				if(isset($category['categories']['category']) && count($category['categories']['category'])>1) {
					$this->active_cats[] = $category['id'];
				}
				else {
					$this->inactive_cats[] = $category['id'];
				}
			}
				
		}

		return $keep;

	}

	function filterCategoriesList( $context = 'both', $all_categories, $multiples_cat ) {

		$new_categories = array();

		$new_categories = $this->filterSubCat($all_categories, $multiples_cat);

		return $new_categories;
	}

	function checkCSS() {
		if ( isset($_GET['adfever-css']) && $_GET['adfever-css'] == 'comparator' ) {
			$this->displayCSS();
			exit();
		}
	}

	function displayCSS() {
		$expires_offset = 31536000;
		header('Content-Type: text/css');
		header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
		header("Cache-Control: public, max-age=$expires_offset");
		
		require_once ADFEVER_DIR.'/inc/css/comparator.css';
		exit();
	}

	/**
	 * This function allow to search on only selected categories. Used for search only.
	 *
	 */
	function getCategoryHierarchy( $parent_id = 0 ) {



		if ( $parent_id == 0 ) {
			return array();
		}

		// Parent ID is parent univers ?
		if ( isset($this->current_options['multiples_cat_raw'][$parent_id]) ) {

			if ( isset($this->current_options['multiples_cat_raw'][$parent_id][$parent_id]) ) { // One level ?
					
				$parent_id_lvl2 = $this->current_options['multiples_cat_raw'][$parent_id][$parent_id];
				if (is_array($parent_id_lvl2) && count($parent_id_lvl2) == 1) {
					$parent_id_lvl2 = current($parent_id_lvl2);
					if ( isset($this->current_options['multiples_cat_raw'][$parent_id][$parent_id_lvl2]) ) {
						return $this->current_options['multiples_cat_raw'][$parent_id][$parent_id_lvl2];
					}
					else {
						return $parent_id_lvl2;
					}
				}
				else if (is_array($parent_id_lvl2) && count($parent_id_lvl2) > 1) {
					$cats_id = array();
					foreach( (array) $parent_id_lvl2 as $child_id_lvl2 ) {
						if ( isset($this->current_options['multiples_cat_raw'][$parent_id][$child_id_lvl2]) ) {
							$cats_id = array_merge( $cats_id, $this->current_options['multiples_cat_raw'][$parent_id][$child_id_lvl2] );
						}
					}
					if ( !empty($cats_id) ) return $cats_id;

					return $parent_id_lvl2;
				}

			}

		} 
		else { // No parent ? find it ?

			foreach( (array) $this->current_options['multiples_cat_raw'] as $key_lvl1 => $lvl1 ) {

				// In array ?
				foreach( (array) $lvl1 as $lvl2 ) {
					if( in_array( $parent_id, (array) $lvl2 ) ) {
						if ( $this->current_options['multiples_cat_raw'][$key_lvl1][$parent_id] ) return $this->current_options['multiples_cat_raw'][$key_lvl1][$parent_id];
						return $parent_id;
					}
				}

			}

		}

		return $parent_id;
	}

	function getIds($category) {
		$ids = array();

		if(isset($category['categories']['category'])) {
			foreach($category['categories']['category'] as $key=>$cat) {
				$ids = array_merge($ids, $this->getIds($cat));
			}
		}
		else if(in_array($category['id'], $this->current_options['multiples_cat']['categories'])) {
			array_push($ids, $category['id']);
		}

		return $ids;
	}

	function getChildIds($parent=null) {
		if(is_null($parent) || empty($parent) || !$parent) {
			return false;
		}
		else {
			$categories = $this->getCategories($parent);
			$ids = array();

				
			if(isset($categories['shopping']['category'])) {
				foreach($categories['shopping']['category'] as $key=>$category) {
					$ids = array_merge($ids, $this->getIds($category));
				}
			}
				
				
		}

		return $ids;
	}

	
	
	function getCategories($id=false) {
		
		$shop_obj = $this->getShopObj();
		
		if(is_null($this->_categories)) {
			$this->_categories = $shop_obj->getCategories();
		}
		
		if(!$id) {
			return $this->_categories;
		}
		else {
			$category = $this->findInChilds($this->_categories['shopping']['category'][0], $id);

			return array(
				'shopping'=>array(
					'category'=>array(
						$category
					)
				)
			);
		}
	}
	
	function findInChilds($category, $id) {
		if($category['id']==$id) {
			return $category;
		}
		else if(isset($category['categories']['category'])) {
			foreach($category['categories']['category'] as $key=>$cat) {
				$res = $this->findInChilds($cat, $id);
				
				if(isset($res['id'])) return $res;
			}
		}
	}


}
?>
