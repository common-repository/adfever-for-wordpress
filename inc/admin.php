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


require_once 'admin.shortcode.php';
require_once 'admin.comparator.php';


class AdfeverAdmin extends AdfeverBase {
	// Error management
	var $message = '';
	var $status = '';

	/**
	 * Constructor
	 *
	 * @return AdfeverAdmin
	 */
	function AdfeverAdmin() {
		// Debug activation
		// parent::activate();

		// Page settings
		add_action ( 'admin_menu', array (&$this, 'addMenu' ) );
		add_action ( 'admin_init', array (&$this, 'loadJavascript' ) );
		

		// Check AID
		$result = parent::isValidAid( true );
		if ( $result === true ) {
			// Add box and options for desactive Adfever on post
			add_action( 'admin_menu', array(&$this, 'initBox') );
			add_action( 'save_post' , array(&$this, 'save') );
			new AdfeverAdmin_Shortcode($this);
			new AdfeverAdmin_Comparator($this);

			// Editor pages only
			global $pagenow;
			if ( in_array( $pagenow, array('post-new.php', 'page-new.php', 'post.php', 'page.php') ) ) {
				wp_enqueue_script( 'thickbox-ext',ADFEVER_URL . '/inc/js/thickbox-ext.js', array('jquery'), $this->version, false );
			}
		}
		
		
		
		
	}
	
	

	/**
	 * Add a page to settings menu
	 *
	 */
	function addMenu() {
		add_menu_page(
			__('AdFever', 'adfever'),
			__('AdFever', 'adfever'), 
			'manage_options', 
			'adfever-general', 
			array (&$this, 'pageGeneral' )
			, ''
		);
		
		
		add_submenu_page(
			'adfever-general', 
			__('General', 'adfever'), 
			__('General', 'adfever'), 
			'manage_options', 
			'adfever-general', 
			array (&$this, 'pageGeneral' )
		);
		
		
		
		
	}
	
	/**
	 * Pugin configuration
	 *
	 */
	function pageGeneral() {
		$options = parent::getOptions( 'general' );
		
		
		// Check update options
		if ( isset($_POST['submit-general-adfever']) ) {
			$this->saveOptions( $options );
			$this->message = __('General settings updated with success !', 'adfever');
			
		}
		$this->displayMessage();
		
		$tpl = '<div class="wrap">
				<h2>'.__('AdFever Settings', 'adfever').'</h2>
				<form action="" method="post">
					<div id="tabs">'.$this->displayOptionsTable( $options ).'</div>
					<p class="submit">
						<input type="submit" name="submit-general-adfever" class="button-primary" value="'.__('Save', 'adfever').'" />
					</p>
				</form>
			</div>';
		
		echo $tpl;
	}

	
	

	/*function pageComparator() {
		$options = parent::getOptions( 'comparator' );

		// Check update options
		if ( isset($_POST['submit-comparator-adfever']) ) {
			$this->saveMultipleCategories();
			$this->saveOptions( $options, false );
			$this->saveHomeConfiguration();
				
			$this->message = __('Comparator settings updated with success !', 'adfever');
		}
		$this->displayMessage();

		// Check AID
		$result = parent::isValidAid( true );
		?>
<div class="wrap">
<h2><?php _e('Adfever Price Comparator', 'adfever'); ?></h2>

		<?php
		if ( $result !== true ) {
			echo '<p>'.__('You must enter an valid AID before can customize the settings of Adfever.', 'adfever').'</p>';
		} else {
			?>
		<p>Url de votre comparateur : <a href="<?php echo get_adfever_link('home'); ?>"><?php echo get_adfever_link(); ?></a></p>
<form id="form-comparator" action="" method="post"
	onsubmit="return validFormCategoriesSelection('<?php echo js_escape(__('No checkbox was checked. You have to check at least one universe or category.', 'adfever')); ?>', '<?php echo js_escape(__('No children categories checked. You must select at least one category.', 'adfever')); ?>', '<?php echo js_escape(__('You can\'t select only one univers. You must take at least one category.', 'adfever')); ?>');">
<div id="tabs"><?php $this->displayOptionsTable( $options ); ?></div>

<p class="submit"><input type="submit" name="submit-comparator-adfever"
	class="button-primary" value="<?php _e('Save', 'adfever'); ?>" /></p>
</form>
			<?php } ?></div>
			<?php
	}

	

	function flushRewriting() {
		global $wp_rewrite;

		// Set prefix comparator
		$current_options = get_option( $this->option_name );
		$this->prefix = $current_options['prefix'];

		$wp_rewrite->flush_rules();
	}
	*/

	/**
	 * Return nice title for each tabs
	 *
	 * @param string $id
	 * @param boolean $return_all
	 * @return string|array
	 */
	function getTabTitle( $id = '', $return_all = false ) {
		$titles = array(
			'account' => __('Account', 'adfever'), 
			'design' => __('Design', 'adfever'),
			'uninstall' => __('Uninstall', 'adfever'),
			'visitors' => __('Visitors', 'adfever'),
			'categories' => __('Categories', 'adfever'),
		);
		 
		if ( $return_all == true ) {
			return $titles;
		}

		if ( isset($titles[$id]) ) {
			return $titles[$id];
		}

		return $id;
	}

	/**
	 * Build table HTML form options
	 *
	 * @param array $options
	 */
	function displayOptionsTable( $options = array() ) {
		
		ob_start();
		$current_options = get_option( $this->option_name );

		if( count($options) > 1 ) {
			echo '<ul>' . "\n";
			foreach ( (array) $options as $key => $sub_options ) {
				echo '<li><a href="#'.$key.'"><span>'.$this->getTabTitle($key).'</span></a></li>' . "\n";
			}
			echo ' </ul>' . "\n";
		}

		$i = 0;
		foreach ( (array) $options as $key => $sub_options ) {
			$i++;
			echo '<div id="'.$key.'">' . "\n";

			if ( $i == 1 ) {
				$this->displaySidebar();
			}
				
			echo '<h3>'.$this->getTabTitle($key).'</h3>' . "\n";
			echo '<table class="form-table">' . "\n";
				
			foreach ( (array) $sub_options as $key_option => $option ) {

				// Get values
				if ( isset($_POST[$key_option]) ) { // Take POST datas
					$value = stripslashes($_POST[$key_option]);
				} elseif ( isset($current_options[$key_option]) ) { // Take DB
					$value = stripslashes($current_options[$key_option]);
				} else { // Take default
					$value = stripslashes($option['default']);
				}

				echo '<tr valign="top">' . "\n";
					
				if ( $option['type'] == 'text' ) { // Texts in form, one colunm
					echo '<td colspan="2" class="text">'.$option['name'].'</td>' . "\n";
					continue;
				} elseif ( substr($option['type'], 0, 5) == 'title' ) { // Titles in form, one colunm
					$level_title = substr($option['type'], 6);
					echo '<td colspan="2"><'.$level_title.' class="sub-title">'.$option['name'].'</'.$level_title.'></td>' . "\n";
					continue;
					//} elseif ( $option['type'] == 'checkbox' ) { // No label here for checkbox
					//	echo '<th scope="row">'.$option['name'].'</th>' . "\n";
				} else {
					echo '<th scope="row"><label for="'.$key_option.'">'.$option['name'].'</label></th>' . "\n";
				}
					
				echo '<td>' . "\n";
				switch ( $option['type'] ) {
						
					case 'checkbox':
						echo '<input type="checkbox" name="'.$key_option.'" id="'.$key_option.'" value="1" '.(($value=='1')?'checked="checked"':'').' />' . "\n";
						break;

					case 'selectbox':
						echo '<select name="'.$key_option.'"  id="'.$key_option.'">' . "\n";
						foreach( (array) call_user_func($option['callback']) as $select_value ) {
							$selected = ( $select_value == $value ) ? 'selected="selected"' : '';
							echo '<option '.$selected.' value="'.$select_value.'">'.$select_value.'</option>' . "\n";
						}
						echo '</select>' . "\n";
						break;

					case 'colorbox':
						echo '<input style="float:left;" type="text" class="color-picker" name="'.$key_option.'" id="'.$key_option.'" value="'.$value.'" /> <span id="preview-'.$key_option.'" style="display:block; float:left; margin:0 0 0 5px; width:20px; height:20px; border:1px solid #ccc; background:'.$value.';"></span>' . "\n";
						break;

					case 'multiples_cat':
						$this->displayMultiCat( $key_option, $value );
						break;

					case 'textbox':
					default :
						echo '<input type="text" class="" name="'.$key_option.'" id="'.$key_option.'" value="'.$value.'" />' . "\n";
						break;
				}
				if ( isset($option['description']) && !empty($option['description']) )
				echo '<br /><span class="ad-desc">'.wp_specialchars($option['description']).'</span>';
				echo '</td>' . "\n";
					
				echo '</tr>' . "\n";

			}
				
			echo '</table>' . "\n";
			echo '</div>' . "\n";
		}
		
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	

	/**
	 * Check POST data for update options in DB
	 *
	 * @param array $options
	 */
	function saveOptions( $options = array(), $delete_post = true ) {
		// Get current options
		$current_options = get_option( $this->option_name );
	
		if ( $current_options == false )
		$current_options = array();
			
		// Save original options
		$original_options = $current_options;
			
		// Make loop on options array, skip text and another non option value
		foreach ( (array) $options as $sub_options ) {
			foreach ( (array) $sub_options as $key_option => $option ) {

				if ( !empty($option['check']) ) {
					$checks = explode(',', $option['check']);
					foreach( (array) $checks as $check ) {
						if ( $check == 'slug' ) {
							$_POST[$key_option] = sanitize_title( $_POST[$key_option] );
						}
					}
				}

				if ( $option['type'] == 'text' || substr($option['type'], 0, 5) == 'title' ) {
					continue;
				} else {
					if ( isset($_POST[$key_option]) )
					$current_options[ $key_option ] = trim ( stripslashes( $_POST[$key_option] ) );
					elseif ( $option['type'] == 'checkbox' )
					$current_options[ $key_option ] = '0';
				}

			}
		}

		// If enable colors is uncheck, fix default value for all colors options, for shortcode
		if ( @$current_options[ 'enable_colors' ] == '0' ) {
			$options_to_reset = array( 'text', 'border-table', 'bg-header-table', 'text-header-table', 'bg-table', 'text-table' );
			$default_options = parent::getOptions('banners');
				
			foreach ( (array) $default_options as $sub_options ) {
				foreach ( (array) $sub_options as $key_option => $option ) {
						
					if ( in_array( $key_option, $options_to_reset ) ) {
						$current_options[ $key_option ] = $option['default'];
					}

				}
			}
		}

		// If enable colors is uncheck, fix default value for all colors options, for comparator
		if ( $current_options[ 'c-enable-colors' ] == '0' ) {
			$options_to_reset = array( 'c-enable-colors', 'c-title', 'c-text', 'c-price', 'c-links-navigation', 'c-border-best-price', 'c-border-table', 'c-bg-table', 'c-text-table', 'c-bg-header-table', 'c-text-header-table' );

			$default_options = parent::getOptions('comparator');
				
			foreach ( (array) $default_options as $sub_options ) {
				foreach ( (array) $sub_options as $key_option => $option ) {
						
					if ( in_array( $key_option, $options_to_reset ) ) {
						$current_options[ $key_option ] = $option['default'];
					}

				}
			}
		}

		// Save options in DB.
		update_option( $this->option_name, $current_options );

		// Delete POST values
		if ($delete_post == true )
		unset($_POST);

		// Test site AID
		$result = parent::isValidAid( false, true, true, $original_options['aid'] );
		if ( $result !== true ) {
			$this->message = $result;
			$this->status = 'error';
			$this->displayMessage();
		}
		
		

		return true;
	}

	/**
	 * Build HTML for siderbar block
	 *
	 */
	function displaySidebar() {
		?>
<div class="block-sidebar">
<h4><?php _e("Useful links", 'adfever'); ?></h4>

<ul>
	<li><a href="http://www.adfever.com/plugin-wordpress.html"><?php _e('Plugin\'s presentation', 'adfever'); ?></a></li>
	<li><a href="http://www.adfever.com/inscription.html"><?php _e('Sign up for AdFever', 'adfever'); ?></a></li>
</ul>
		<?php if ( parent::isValidAid( true ) ) : ?>
<ul>
	<li><a href="http://www.adfever.com/editor"><?php _e('My account', 'adfever'); ?></a></li>
	<li><a href="http://www.adfever.com/editor/rawstats"><?php _e('My statistics', 'adfever'); ?></a></li>
</ul>
		<?php endif; ?>
<ul>
	<li><a href="http://www.adfever.com/AdF-Notice Utilisation Plugin Wordpress-20100128.pdf"><?php _e('Support', 'adfever'); ?></a></li>
</ul>
</div>
		<?php
	}

	/**
	 * Load some javascript and style for settings page
	 *
	 * @return boolean
	 */
	function loadJavascript() {
		global $wp_version;
		$adfever_pages = array( 'adfever-general', 'adfever-banners', 'adfever-comparator' );
		if ( in_array( stripslashes(@$_GET['page']), $adfever_pages ) ) {

			if ( version_compare( $wp_version, '2.8', '<' ) ) { // WP 2.5, 2.6, 2.7
				wp_deregister_script( 'jquery-ui-core' );
				wp_deregister_script( 'jquery-ui-tabs' );
				wp_deregister_script( 'jquery' );

				wp_enqueue_script( 'jquery', ADFEVER_URL . '/lib/jquery-1.3.2.min.js', array(), '1.3.2', true );
				wp_enqueue_script( 'adfever-jquery-ui-core', ADFEVER_URL . '/lib/ui.core.js', array('jquery'), '1.7.2', true  );
				wp_enqueue_script( 'adfever-jquery-ui-tabs', ADFEVER_URL . '/lib/ui.tabs.js', array('jquery'), '1.7.2', true  );
			} else { // WP 2.8
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-tabs' );
			}
				
			wp_enqueue_script( 'jquery-cookie', ADFEVER_URL . '/lib/jquery.cookie.js', array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'jquery-checkboxtree', ADFEVER_URL . '/lib/checkboxtree/js/jquery.checkboxtree.js', array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'jquery-colorpicker', ADFEVER_URL . '/lib/colorpicker/js/colorpicker.js', array('jquery', 'jquery-cookie'), '1.0.0', true );
			wp_enqueue_script( 'jquery-colorpicker-eye', ADFEVER_URL . '/lib/colorpicker/js/eye.js', array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'jquery-colorpicker-utils', ADFEVER_URL . '/lib/colorpicker/js/utils.js', array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'jquery-colorpicker-layout', ADFEVER_URL . '/lib/colorpicker/js/layout.js', array('jquery'), '1.0.2', true );

			wp_enqueue_script( 'adfever', ADFEVER_URL . '/inc/js/adfever.js', array('jquery'), $this->version, true );
			wp_enqueue_style( 'jquery-colorpicker', ADFEVER_URL . '/lib/colorpicker/css/colorpicker.css', array(), '1.0.0', 'screen');
			wp_enqueue_style( 'jquery-checkboxtree', ADFEVER_URL . '/lib/checkboxtree/css/checkboxtree.css', array(), '1.0.0', 'screen' );
			wp_enqueue_style( 'jquery-ui-tabs-redmond', ADFEVER_URL . '/lib/jquery-ui-theme/redmond/jquery-ui-1.7.2.custom.css', array(), '1.0.0', 'screen');
			wp_enqueue_style( 'adfever', ADFEVER_URL . '/inc/css/adfever.css', array(), $this->version, 'screen');

			return true;
		}
		return false;
	}

	/**
	 * Display WP alert
	 *
	 */
	function displayMessage() {
		if ( $this->message != '') {
			$message = $this->message;
			$status = $this->status;
			$this->message = $this->status = ''; // Reset
		}
		
		

		if ( isset($message) && $message ) {
			$tpl = '<div id="message" class="'.(($status == '') ? 'updated' : $status).' fade">
					<p><strong>'.$message.'</strong></p>
					</div>';
					
			echo $tpl;
		}
	}

	function initBox() {
		add_meta_box('nouveautes-div', __('AdFever', 'adfever'), array(&$this, 'blockAdfeverWrite'), 'post', 'normal', 'core');
	}

	function blockAdfeverWrite( $post ) {
		echo '<p>
				<input type="checkbox" name="desactive_adfever" value="1" id="desactive_adfever" '.((get_post_meta($post->ID, 'desactive_adfever', true)=='1')?'checked="checked"':'').' />
				<label for="desactive_adfever">'.__('Deactivate AdFever on this post', 'adfever').'</label>
			</p>';	
	}

	function save( $id, $post = '' ) {
		if (isset($_POST['desactive_adfever'])) {
			update_post_meta( $id, 'desactive_adfever', 1 );
		} else {
			update_post_meta( $id, 'desactive_adfever', 0 );
		}
			
	}

	/**
	 * Display categories for  multiples choices.
	 *
	 * @param $key_option
	 * @param $value
	 * @return unknown_type
	 */
	function displayMultiCat( $key_option = '', $value ) {
		if ( empty($value) ) {
			$value = 'all';
		}

		// Init Shopping class
		$current_options = get_option( $this->option_name );
		$current_options['multiples_cat'] = ( isset($current_options['multiples_cat']) ? (array) $current_options['multiples_cat']: array());
		$shop = new Shopping ( $current_options['aid'], ADFEVER_UUID );

		// Get categories tree
		$categories = $shop->getCategories();
		?>
<script type="text/javascript">
			<!--
			jQuery(document).ready(function(){
				jQuery("input#<?php echo $key_option; ?>-all").click(function () { jQuery("div#select_categories").css( 'display', 'none' ); });
				jQuery("input#<?php echo $key_option; ?>-sel").click(function () { jQuery("div#select_categories").css( 'display', 'block'  ); });
				
				jQuery("div#select_categories #tree").checkboxTree({
					checkchildren: true,
					checkparents: true,
					collapsedarrow: "<?php echo ADFEVER_URL; ?>/lib/checkboxtree/images/checkboxtree/img-arrow-collapsed.gif",
					expandedarrow: "<?php echo ADFEVER_URL; ?>/lib/checkboxtree/images/checkboxtree/img-arrow-expanded.gif",
					blankarrow: "<?php echo ADFEVER_URL; ?>/lib/checkboxtree/images/checkboxtree/img-arrow-blank.gif"
				});
			});
			-->
		</script>
<div id="select_quantity"><input
<?php if ( $value == 'all' ) echo 'checked="checked" '; ?> type="radio"
	name="<?php echo $key_option; ?>" value="all"
	id="<?php echo $key_option; ?>-all" /> <label
	for="<?php echo $key_option; ?>-all"><?php _e('All categories', 'adfever'); ?></label>
<input <?php if ( $value == 'sel' ) echo 'checked="checked" '; ?>
	type="radio" name="<?php echo $key_option; ?>" value="sel"
	id="<?php echo $key_option; ?>-sel" /> <label
	for="<?php echo $key_option; ?>-sel"><?php _e('Selection of categories', 'adfever'); ?></label>
</div>
<div id="select_categories" style="display:<?php echo ($value=='sel')?'block':'none';?>">
<?php
if ( empty($categories['shopping']['category'][0]['categories']['category']) ) {
	echo '<p>'.__('No category currently. Strange ! Perhaps your server can\'t call the AdFever server', 'adfever').'</p>';
} else {
	echo '<ul id="tree" class="unorderedlisttree">' . "\n";
	if ( !empty($current_options['multiples_cat']) )
	$current_categories = array_merge($current_options['multiples_cat']['univers'], $current_options['multiples_cat']['categories']);
	recursive_categories_checkbox( $categories['shopping']['category'][0]['categories']['category'], 'univers', $current_categories );
	echo '</ul>' . "\n";
}
?></div>
<?php
	}

	
}
?>
