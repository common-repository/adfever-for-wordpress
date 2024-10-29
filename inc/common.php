<?php
add_filter('the_adfever_description', 'wptexturize');
add_filter('the_adfever_description', 'convert_smilies');
add_filter('the_adfever_description', 'convert_chars');
add_filter('the_adfever_description', 'wpautop');
add_filter('the_adfever_description', 'prepend_attachment');