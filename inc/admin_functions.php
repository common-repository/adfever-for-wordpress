<?php
// Activate Adfever
function activateAdfever() {
	$shopbot = new AdfeverBase ( );
	$shopbot->activate();
	unset($shop_bot);
}

register_activation_hook(ADFEVER_FOLDER.'/adfever-for-wordpress.php', 'activateAdfever' );


// Deactivate
function deactivateAdfever() {
	$shopbot = new AdfeverBase ( );
	$shopbot->deactivate();
	unset($shop_bot);
}
register_deactivation_hook(ADFEVER_FOLDER.'/adfever-for-wordpress.php', 'deactivateAdfever');


// Notice for activation
function adfever_print_notice() {
	
	$current_options = get_option('adfever');
	if ((!isset($current_options['aid']) || !$current_options['aid']) && !isset($_REQUEST['aid'])) {
	
		$buffer = array();
		$buffer[] = '<div class="updated fade"><p><strong>'.__('AdFever plugin has been activated', 'adfever').'</strong>.</p>';
		$buffer[] = '<p>'.sprintf(
	              __('You need to <a href="%s">setup your AdFever tracking ID</a>', 'adfever'),
	              'admin.php?page=adfever-general'
	            ).'</p>';
	    $buffer[] = '</div>';
	    
	    echo join('', $buffer);
	}
}
add_action('admin_notices', 'adfever_print_notice');


// Rewriting
function createRewriteRules() {
	
	global $wp_rewrite;
	
	$options = get_option('adfever');
	$prefix = $options['prefix'];

	// Add rewrite tokens for main rewriting
	$token_adfever = '%'.$prefix.'%';
	$wp_rewrite->add_rewrite_tag($token_adfever, '(.+?)', $prefix.'=');

	// Add seconds parameter, for order by example
	$key_adfever_order = '%'.$prefix.'-order%';
	$wp_rewrite->add_rewrite_tag($key_adfever_order, '([^/]+?)', $prefix.'-order=');

	// Build rules
	$adfever_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->root . $prefix."/".$token_adfever."/1/".$key_adfever_order."/", EP_NONE, true, false );

	// Add new rules in WP array
	$wp_rewrite->rules = $adfever_rewrite + $wp_rewrite->rules;

	return $wp_rewrite->rules;
}

function adfever_flush_rewrite() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

if(isset($_POST['submit-general-adfever'])) {
	global $wp_rewrite;
	$options = get_option('adfever');
	$options['prefix'] = $_POST['prefix'];
	$options['comparator-active'] = $_POST['comparator-active'];
	update_option('adfever', $options);
	
	add_action('init', 'adfever_flush_rewrite');
	
	

}
add_filter('generate_rewrite_rules', 'createRewriteRules');

// Admin
$shopbot = new AdfeverAdmin();
