.adfever-container-comparator { 
	position:relative; 
}
.adfever-container-comparator {
	<?php if($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-text'].';'; ?>
}
.adfever-container-comparator h3 {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-title'].';';?>
}
.adfever-clear {
	clear:both;
} 
.adfever-pages {
	text-align:right;
}
.adfever-search select.search-adfever-cats { 
	width:200px; 
}
.adfever-search input.search-adfever-submit {
	border:1px solid #FFF;
	background:#ff6600; 
	color:#fff; 
	padding:1px 5px; 
}
.adfever-breadcrumb { 
	position:relative;
	clear:both; 
}
.adfever-breadcrumb a {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-links-navigation'].';';?>
} 
.adfever-breadcrumb span.quantity-right {
	display:block;
	float:right;
}
#list-univers { 
	clear:both; 
} 
#list-univers ul {
	margin:0;
	padding:0;
	list-style:none; 
} 
#list-univers ul li {
	margin:0;
	padding:0; 
	display:block; 
	float:left; 
	width:45%;
	padding-right:3%; 
	margin-bottom:10px; 
	position:relative;
} 
#list-univers ul li a.univers, #list-univers ul li span.univers {
	font-weight:700;
	color:#000; 
} 
#list-univers ul li a.more-cats {
	font-weight:700;
	color:#FFF;
	background:#666;
	padding:0 2px; 
}
#list-univers ul li img.pic-category { 
	float:left; 
	margin:10px 5px 10px 0; 
	width:38px; 
} 
#list-univers ul ul { 
	margin-left:43px; 
} 
#list-univers ul ul li { 
	margin:0;
	padding:0; 
	display:inline; 
	float:none; 
	width:auto;
	padding-right:0; 
	margin-bottom:0; 
}
#list-categories { 
	clear:both; 
} 
#list-categories ul {
	margin:0;
	padding:0;
	list-style:none; 
}
#list-categories ul li {
	margin:0;
	padding:0; 
	display:block; 
	float:left; 
	width:30%;
	padding-right:3%; 
	margin-bottom:10px; 
	font-weight:700; 
}
#list-categories ul li a { 
	color:#000; 
} 
#list-categories ul ul {
	list-style:square; 
} 
#list-categories ul ul li { 
	margin:0;
	padding:0;
	display:inline; 
	float:none; 
	width:auto; 
	padding-right:0;
	margin-bottom:0; 
	font-weight:400; 
} 
body .adfever-top-categories { 
	position:relative; 
	clear:both; 
} 
body .adfever-top-categories h3 { 
	margin-bottom:10px;
} 
body .adfever-top-categories ul {
	list-style:none;
	margin:0;
	padding:0;
	width:100%;
} 
body .adfever-top-categories ul li {
	display:block;
	float:left;
	margin:0;
	padding:0;
	width:21%;
	margin:0 2%; 
}
body .adfever-top-categories ul li a {
	display:block;
	float:left;
	text-align:center;
	width:100%; 
} 
body .adfever-top-categories ul li a img { 
	display:block;
	border:1px solid #ccc;
	width:95%; 
} 
body .adfever-top-products {
	position:relative; 
	clear:both; 
} 
body .adfever-top-products h3 {
	margin-bottom:10px;
}
body .adfever-top-products h3 {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-title'].';';?>
} 
body .adfever-top-products ul {
	list-style:none;
	margin:0;
	padding:0;
	width:100%; 
} 
body .adfever-top-products ul li { 
	display:block; 
	float:left;
	margin:0;
	padding:0;
	width:21%;
	margin:0 2%; 
} 
body .adfever-top-products ul li a { 
	display:block; 
	float:left;
	text-align:center;
	width:100%; 
} 
body .adfever-top-products ul li a img { display:block;border:1px solid #ccc;max-width:95%; } 
#tri-price { 
	display:block;
	padding:10px;
	border:1px solid #ccc;
	border-width:1px 0;
	text-align:right; 
	margin:10px 0; 
} 
table#list-products { 
	display:block;
	width:100%;
	border-collapse:collapse; 
} 
table#list-products td { 
	vertical-align:middle; 
} 
table#list-products a.link-image img {
	width:80%; 
	border:1px solid #ccc; 
	margin:3px 5px; 
} 
table#list-products h2 { 
	font-size:13px;
	margin:0; 
} 
table#list-products span.product-price {
	font-size:16px; 
	font-weight:700;
}
table#list-products span.product-price {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-price'].';';?>
} 
.adfever-product table.best-price {
	width:100%; 
}
.adfever-product table.best-price {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'border:2px solid'.$this->current_options['c-border-best-price'].';';?>
}
.adfever-product table.best-price td { 
	padding:10px; 
	text-align:center;
} 
.adfever-product table.best-price td h4 { 
	margin:0;
}
.adfever-product table.best-price td h4 {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-title'].';';?>
} 
.adfever-product table.best-price td a { 
	color:#000; 
}
.adfever-product table.best-price td a.link-see { 
	background:#FF7E00;
	color:#fff;
	padding:2px 4px; 
}
.adfever-product img.adfever-img { 
	float:left; 
	margin:0 8px 8px 0;
} 
.adfever-product table.list-stores { 
	width:100%; 
	margin:10px 0; 
} 
.adfever-product table.list-stores { 
	position:relative;
	width:100%;
	border-collapse:collapse;
}
.adfever-product table.list-stores {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'border:1px solid'.$this->current_options['c-border-table'].';';?>
} 
.adfever-product table.list-stores tr { }
.adfever-product table.list-stores th { 
	padding:3px; 
	text-align: center;
	font-weight:700; 
} 
.adfever-product table.list-stores td { 
	padding:3px;
	text-align: center;
	border-collapse:collapse; 
}
.adfever-product table.list-stores td a {
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-text-table'].';';?>
} 
.adfever-product table.list-stores thead { } 
.adfever-product table.list-stores tbody { } 
a.adf_price { 
	font-weight: bold;
}
a.adf_price { 
	<?php if ($this->current_options['c-enable-colors']=='1')  echo 'color:'.$this->current_options['c-price'].';';?>
}
a.adf_text { 
	font-weight: bold;
}
a.adf_text { 
	<?php if ($this->current_options['c-enable-colors']=='1') echo 'color:'.$this->current_options['c-text'].';';?>
}
		