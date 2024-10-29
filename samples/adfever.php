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

get_header(); 



?>

<div id="content" class="narrowcolumn" role="main">
<h2><?php _e('Price comparator', 'adfever'); ?></h2>

<!-- You can delete this line in your theme : Start --> <!--
		<p style="font-style:italic;font-weight:bold;color:red;"><?php _e('You currently use the sample template present into the adfever plugin. You must copy and adapt this file in your theme. Usually, inspire you from the model page.php for build this page.', 'adfever'); ?></p>
	--> <!-- You can delete this line in your theme : End --> <?php if ( function_exists('adfever_render') ) adfever_render(); ?>

</div>
<?php 
$options = get_option('adfever'); 
if(isset($options['sidebar']) && $options['sidebar']==1) get_sidebar(); ?>
<?php get_footer(); ?>
