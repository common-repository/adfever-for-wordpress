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

<div class="adfever-product">{include:search} {include:breadcrumb}
{include:top-categories}

<div id="list-categories">
<h3><?php _e('All categories', 'adfever'); ?></h3>

<?php if ( is_array($categories) && !empty($categories) ) : ?>
<ul>
<?php foreach( (array) $categories as $category ) : ?>

	<li>
	<?php if(count($category['categories']['category'])==1): ?>
		<?php echo wp_specialchars($category['name']); ?>
	<?php else:?>
	<a
		href="<?php echo get_adfever_link('category', (!isset($category['kwd'])?$category['id']:$category['kwd']) ); ?>"><?php echo wp_specialchars($category['name']); ?></a>
	<?php endif;?>
		<?php if ( is_array($category['categories']['category']) && !empty($category['categories']['category']) ) : ?>
	<ul>
	<?php $cat_li = array();?>
	<?php foreach( (array) $category['categories']['category'] as $sub_category ) : ?>
		<?php 
			$cat_li[] = '<li><a
				href="'.get_adfever_link('category', (!isset($sub_category['kwd'])?$sub_category['id']:$sub_category['kwd']) ).'">'.wp_specialchars($sub_category['name']).'</a>
			</li>';
		?>
		<?php endforeach; ?>
		<?php echo join(', ', $cat_li); ?>
	</ul>
	<?php endif; ?></li>
	<?php endforeach; ?>
</ul>
	<?php else : ?>
<p><?php _e('No category for this universe', 'adfever'); ?></p>
	<?php endif; ?>

<div class="clear"></div>
</div>

{include:top-products} {include:search}</div>
