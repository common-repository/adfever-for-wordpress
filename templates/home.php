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

<div class="adfever-home">{include:search}
<div id="list-univers">
<ul>
<?php
$j = 1;
foreach( (array) $all_categories as $univers ) {
	$j++;
	$flag = false;
	if ( isset($univers['name']) ) {
		$flag = true;
		echo '<li class="'.(($j==2)?'clear':'').'">';

		if ( @$category['link'] != 'false' and count($univers['categories']['category'])!=1) 
		echo '<a class="univers" href="'.get_adfever_link( 'category', (!isset($univers['kwd'])?$univers['id']:$univers['kwd']) ).'">';
		else
		echo '<span class="univers">';
			
		echo '<img class="pic-category" src="'.$univers['img'].'" alt="'.attribute_escape($univers['name']).'" />';
		echo wp_specialchars($univers['name']);
			
		if (@$category['link'] != 'false' )
		echo '</a>' . "\n";
		else
		echo '</span>' . "\n";
			
		if ( is_array($univers['categories']['category']) && !empty($univers['categories']['category']) ) {

			echo '<ul>'. "\n";
			$i = 0;
			$sub_categories = array();
			foreach( (array) $univers['categories']['category'] as $sub_category ) {
				$i++;
				$sub_categories[] = '<li><a href="'.get_adfever_link('category', (!isset($sub_category['kwd'])?$sub_category['id']:$sub_category['kwd']) ).'">'.wp_specialchars(($sub_category['name'])).'</a></li>' . "\n";
				if ( $i == $max_cat_to_display ) break;
			}
			echo implode( ', ', $sub_categories );

			if ( count($univers['categories']['category']) >= $max_cat_to_display )
			echo '<li><a class="more-cats" href="'.get_adfever_link('category', (!isset($univers['kwd'])?$univers['id']:$univers['kwd']) ).'">'.__('More', 'adfever').'</a></li>';
				
			echo '</ul>' . "\n";
				
		}
			
		if ( $flag == true ) {
			echo '</li>' . "\n";
		}
	}

	if ( $j == 2 ){
		$j = 0;
	}
}
?>
</ul>

<div class="clear"></div>
</div>

{include:top-categories} {include:search}</div>
