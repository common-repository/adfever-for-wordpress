<?php

require_once 'client.comparator.php';

 class AdfeverAdmin_Comparator extends AdfeverClient_Comparator {
 	
 	function AdfeverAdmin_Comparator($parent) {
 		parent::AdfeverClient_Comparator();
		add_action ( 'admin_menu', array (&$this, 'addMenu' ) );
		$this->parent = $parent;
 	}
 	
 	function addMenu() {
 		$options = get_option('adfever');
 		
 		if(isset($options['comparator-active']) && $options['comparator-active']==1) {
	 		add_submenu_page(
					'adfever-general', 
					__('Price comparator', 'adfever'),
				 	__('Price comparator', 'adfever'), 
				 	'manage_options', 
				 	'adfever-comparator',
				  	array (&$this, 'pageComparator' )
				);
 		}
 	}
 	
 	function pageComparator() {
		$options = parent::getOptions( 'comparator' );

		// Check update options
		if ( isset($_POST['submit-comparator-adfever']) ) {
			$this->saveMultipleCategories();
			$this->parent->saveOptions( $options, false );
			$this->saveHomeConfiguration();
				
			$this->message = __('Comparator settings updated with success !', 'adfever');
		}
		$this->parent->displayMessage();

		// Check AID
		$result = parent::isValidAid( true );
		?>
<div class="wrap">
<h2><?php _e('AdFever Price Comparator', 'adfever'); ?></h2>

		<?php
		if ( $result !== true ) {
			echo '<p>'.__('You must enter a valid AID before can customize the settings of AdFever.', 'adfever').'</p>';
		} else {
			?>
		<p>Url de votre comparateur : <a href="<?php echo $this->link('home'); ?>"><?php echo $this->link('home'); ?></a></p>
<form id="form-comparator" action="" method="post"
	onsubmit="return validFormCategoriesSelection('<?php echo js_escape(__('No checkbox was checked. You have to check at least one universe or category.', 'adfever')); ?>', '<?php echo js_escape(__('No children category checked. You must select at least one category.', 'adfever')); ?>', '<?php echo js_escape(__('You can\'t select only one universe. You must choose at least one category.', 'adfever')); ?>');">
<div id="tabs"><?php echo $this->parent->displayOptionsTable( $options ); ?></div>

<p class="submit"><input type="submit" name="submit-comparator-adfever"
	class="button-primary" value="<?php _e('Save', 'adfever'); ?>" /></p>
</form>
			<?php } ?></div>
			<?php
	}
 	
 function saveMultipleCategories() {
		$current_options = get_option( $this->option_name );

		if ( $_POST['categories_selection'] == 'all' )  {

			$current_options['multiples_cat'] = array();
			$current_options['multiples_cat_raw'] = array();
				
		} elseif ( isset($_POST['multiples_cat']) ) {
				
			$result_options = array();
			foreach( (array) $_POST['multiples_cat'] as $categories ) {

				foreach( (array) $categories as $cat_id => $sub_category ) {
						
					if ( $cat_id != 0 ) // Skip univers where ID is 0.
					$result_options['univers'][] = $cat_id;
					foreach( (array) $sub_category as $sub_cat_id ) {
						$result_options['categories'][] = $sub_cat_id;
					}
						
				}
			}
				
			// Unique array ?
			$result_options['univers'] = array_unique($result_options['univers']);
			$result_options['categories'] = array_unique($result_options['categories']);
				
			// Remove univers in categories array
			foreach( $result_options['categories'] as $key => $value ) {
				if ( in_array($value, (array) $result_options['univers']) ) {
					unset($result_options['categories'][$key]);
				}
			}
				
			$current_options['multiples_cat'] = $result_options;
			$current_options['multiples_cat_raw'] = $_POST['multiples_cat'];
		}

		update_option( $this->option_name, $current_options );
	}

	function saveHomeConfiguration() {
		$this->current_options = get_option( $this->option_name );

		if ( $this->current_options['categories_selection'] == 'all' )  {
			$this->current_options[$this->option_field_home] = array();
		} else {

			$conf = array();
				
			// Useful for debug : var_dump($this->current_options['multiples_cat']);
			$this->count_univers 	= count($this->current_options['multiples_cat']['univers']);
			$this->count_categories = count($this->current_options['multiples_cat']['categories']);
			if(
			($this->count_univers == 1 		&& $this->count_categories == 0) ||
			($this->count_categories == 0 	&& $this->count_univers == 0) ||
			($this->count_categories == 1 	&& $this->count_univers == 0)
			) {

				// Condition 1 : Once univers, without categories ? impossible ! You can't have ONE univers without at least ONE category.
				// Condition 1 : Javascript check is this case...
				// Condition 2 : No univers and categories checked, impossible because JS check this part.
				// Condition 3 : One category, no univers, impossible because JS check this also this part
				$conf['tpl_file']		= 'maintenance.php';
				$conf['is_maintenance']	= true;

			} elseif( $this->count_categories == 1 && $this->count_univers >= 1 ) {

				// User select only one category ?
				$conf['current_cat_id']	= current($this->current_options['multiples_cat']['categories']);
				$conf['current_item'] 	= $conf['current_cat_id'];
				$conf['tpl_file']		= 'category.php';
				$conf['is_category']	= true;
				$conf['is_home_type3']	= true;

			} elseif(
			$this->count_categories > 1 && $this->count_univers == 1 ||
			( count($_POST['multiples_cat']) == 1 && count(current($_POST['multiples_cat'])) > 2 ) // For sub levels
			) {

				// User select only one univers ?
				/*$conf['current_cat_id']	= current($this->current_options['multiples_cat']['univers']);
				if ( count($_POST['multiples_cat']) == 1 && count(current($_POST['multiples_cat'])) > 2 ) {
					$conf['is_sub_univers'] = true;
					$tmp = current($_POST['multiples_cat']);
					if ( isset($tmp[ $conf['current_cat_id'] ]) ) {
						$conf['current_cat_id'] = current($tmp[ $conf['current_cat_id'] ]);
					}
				}*/
				$conf['current_cat_id'] = $this->current_options['multiples_cat']['univers'][0];
				$conf['tpl_file']		= 'univers.php';
				$conf['is_univers']		= true;
				$conf['is_home_type2']	= true;

			} else { // Home with no all categories, when render

				$conf['current_cat_id']		= 'filter';
				$conf['is_home'] 			= true;
				$conf['tpl_file'] 			= 'home.php';
				$conf['is_home_type1']		= true;
					
			}
				
			// Useful debug :
			// var_dump($conf);
				
			$this->current_options[$this->option_field_home] = $conf;

		}

		return update_option( $this->option_name, $this->current_options );
	}
 }