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
?>

<div class="adfever-search">


<h3><?php _e('What are you shopping for?', 'adfever'); ?></h3>

<form class="search-form-adfever"
	action="<?php echo $adfever_search_action; ?>" method="get"><?php
	if ( !empty($adfever_search_hidden) )
	echo '<input type="hidden" name="'.$adfever_search_hidden['name'].'" value="'.$adfever_search_hidden['value'].'" />'. "\n";
	?>

<p><input type="text" name="terms" value="<?php echo $terms; ?>" /> <select
	class="search-adfever-cats" name="cat_id">
	<?php recursive_categories_select( $all_categories, $current_cat ); ?>
</select> <input class="search-adfever-submit button" type="submit"
	value="<?php _e('&raquo; Search', 'adfever'); ?>" /></p>
</form>

</div>
