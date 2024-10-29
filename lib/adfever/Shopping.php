<?php
/*******************************************************************************
 *  Copyright (c) 2009 Inteliscent SAS.
 *  All rights reserved. This program and the accompanying materials
 *  are made available under the terms of the GNU Public License v2.0
 *  which accompanies this distribution, and is available at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *  
 *  Contributors:
 *      Inteliscent SAS - initial API and implementation
 *      
 ******************************************************************************/





/**
 * Constantes pour les tris
 */
// Tri par nom
define('SORT_NAME', 1); // produit et marchand
//Tri par prix
define('SORT_PRICE', 2); // produit et marchand
// Tri naturel
define('SORT_NATURAL', 3); // produit




/**
 * Classe d'accès au web service shopping d'AdFever
 */
class Shopping {
	var $version = '1.2';
	//var $url = 'http://dev.adfever.com/web_service/';
	var $url = 'http://ws-shopping-fr.adfever.com/v2/';

	/**
	 * Constructeur PHP4
	 * @return void
	 * @param string $sid : L'identificateur du site sur AdFever
	 * @param string $uuid : identifiant unique de session
	 */
	function Shopping($sid, $uuid) {
		$this->sid = $sid;
		$this->uuid = $uuid;
	}

	/**
	 * Constructeur PHP5
	 * @return void
	 * @param string $sid : L'identificateur du site sur AdFever
	 * @param string $uuid : identifiant unique de session
	 */
	function __construct($sid, $uuid) {
		$this->Shopping($sid, $uuid);
	}


	/**
	 * Génère un identifiant unique à mettre en session
	 * @return string
	 */
	function genUuid() {
		return md5(uniqid(mt_rand(), true));
	}

	/**
	 * Récupère les catégories sous forme de tableau
	 * @return array
	 * @param integer $id[optional] : identifiant de la catégorie
	 */
	function getCategories($id=False) {
		//$id = (int)$id;

		$url = $this->url.'?action=categories&site-id='.$this->sid.'&uuid='.$this->uuid.($id ? '&category='.$id : "");
		$content = $this->parseXML( fetchUrl($url) );
		return $content;
	}

	/**
	 * Recherche de produits
	 * @return array of products
	 * @param string $query : mots clés à rechercher
	 * @param mixed $category[optional] : identifiant de la catégorie ou tableau d'identifiants
	 * @param integer $page : page à récupérer
	 * @param integer $qty : nombre d'éléments à récupérer
	 * @param integer $sortproducts : ordre de tri
	 */
	function search($query, $category=False, $page=1, $qty=10, $sortproducts=false, $sortorder='ASC') {

		if(is_array($category)) {
			$category = join('|', $category);
		}

		$query = sanitize($query);

		$url = $this->url.'?action=search&site-id='.$this->sid.'&uuid='.$this->uuid.'&q='.urlencode($query).($category ? '&category='.$category : "");

		if($page==1 || $page==0) {
			$url .= "";
		}
		else {
			$url .= "&page=".$page;
		}

		$url .= "&qty=".$qty;

		if($sortproducts) {
			$url .= "&sortproducts=".$sortproducts.'&order='.$sortorder;
		}

		
		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Recherche des produits d'une catégorie
	 * @return array of products
	 * @param integer $id : identifiant de la catégorie
	 * @param integer $page : page à récupérer
	 * @param integer $qty : nombre d'éléments à récupérer
	 * @param integer $sortproducts : ordre de tri
	 * @param string $sortorder: tri croissant ou décroissant
	 */
	function findForCategory($id, $page=1, $qty=10, $sortproducts=false, $sortorder='ASC') {
		/*
		 $id = (int)$id;

		 if(!$id) {
			trigger_error("Identifiant du site manquant ou non numérique");
			}
			*/

		$url = $this->url.'?action=search&site-id='.$this->sid.'&uuid='.$this->uuid.'&category='.$id;

		if($sortproducts) {
			$url .= "&sortproducts=".$sortproducts.'&order='.$sortorder;
		}


		if($page==1 || $page==0) {
			$url .= "";
		}
		else {
			$url .= "&page=".$page;
		}

		if($qty==0) $qty = 10;
		
		$url .= "&qty=".$qty;


		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Récupère un produit et ses offres
	 * @return product
	 * @param integer $id : identifiant du produit
	 * @param integer $sortmerchants : ordre de tri des marchands
	 */
	function find($id, $sortmerchants=false) {

		$id = (int)$id;

		if(!$id) {
			trigger_error("Identifiant du produit manquant ou non numérique");
		}


		$url = $this->url.'?action=product&site-id='.$this->sid.'&uuid='.$this->uuid.'&product-id='.$id;

		if($sortmerchants) {
			$url .= "&sortmerchants=".$sortmerchants;
		}

		
		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Fonction qui check le site-id
	 * @return array
	 */
	function checkSiteId() {
		$url = $this->url."?action=checksiteid&site-id=".$this->sid.'&uuid='.$this->uuid;
		$content = fetchUrl($url);

		return $this->parseXML($content);
	}


	/**
	 * Récupère le top produit
	 * @return array
	 * @param category identifiant de la catégorie
	 * @param integer $nb[optional] : nombre de résultats
	 * @param integer $qty : nombre d'éléments à récupérer
	 */
	function getTop($category="all", $qty=3) {
		$category = ($category!="all" ? $category : "all");

		if(is_array($category)) {
			$url = $this->url."?action=top&site-id=".$this->sid.'&uuid='.$this->uuid."&category=".join(";", $category)."&qty=".$qty;
		}
		else {
			$url = $this->url."?action=top&site-id=".$this->sid.'&uuid='.$this->uuid."&category=".$category."&qty=".$qty;
		}
		
			
		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Récupère le top categorie
	 * @return array
	 * @param id des catégories sous forme de tableau
	 *
	 */
	function getTopCategories($id=False, $nb=3) {
		if(is_array($id)) {
			$url = $this->url."?action=topcategories&site-id=".$this->sid.'&uuid='.$this->uuid."&category_id=".join(';', $id)."&nb=".$nb;
		}
		else if($id) {
			$url = $this->url."?action=topcategories&site-id=".$this->sid.'&uuid='.$this->uuid."&category_id=".$id."&nb=".$nb;
		}
		else {
			$url = $this->url."?action=topcategories&site-id=".$this->sid.'&uuid='.$this->uuid."&nb=".$nb;
		}

		
		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Récupère les informations pour construire le fil d'ariane
	 * @return array
	 * @param integer $id : l'identifiant de la catégorie
	 */
	function getBreadCrumb($id) {
		$url = $this->url."?action=breadcrumb&site-id=".$this->sid."&uuid=".$this->uuid."&category_id=".$id;

		
		$content = $this->parseXML( fetchUrl($url) );
		
		return $content;
	}

	/**
	 * Parse un contenu XML
	 * @return mixed
	 * @param string $xml
	 */
	function parseXML($xml) {
		return GetXMLTree($xml);
	}
}




/*************************** UTILITIES ************************************/
/**
 * Fonction utilitaire pour récupérer le contenu d'une url
 * @return string : le contenu de l'url
 * @param string $url
 */
function fetchUrl($url) {
	// Si on utilise Worpress
	if (function_exists ( 'wp_remote_get' )) {
		$request = wp_remote_get ( $url, array('timeout'=>5) );
		if ( !is_wp_error($request) ) {
			return wp_remote_retrieve_body($request);
		}
		return '';
	}
	// Si curl est installé, on s'en sert
	else if(function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	// file_get_contents sous PHP5
	else if(function_exists('file_get_contents')) {
		return file_get_contents($url);
	}
	// Methode par défaut
	else {
		$fp = fopen($url,'r');
		$content = fread($fp);
		fclose($fp);
		return $content;
	}
}




/**
 * Fonction utilitaire pour blanchir une chaine
 * @return string
 * @param string $query
 */
function sanitize($query) {
	return preg_replace("/[^a-zA-Z0-9\-\s]/", "", $query);
}




/**
 * Permet de transformer un fichier xml en tableau
 * @return array
 * @param string $xmldata
 */
function GetXMLTree ($xmldata) {

	$result = array();

	// we want to know if an error occurs
	ini_set ('track_errors', '1');

	$xmlreaderror = false;

	$parser = xml_parser_create ('UTF-8');
	xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
	if (!xml_parse_into_struct ($parser, $xmldata, $vals, $index)) {
		$xmlreaderror = true;
		//echo "error";
	}
	xml_parser_free ($parser);

	//print_r($vals);

	if (!$xmlreaderror) {
		$result = array ();
		$i = 0;
		if (isset ($vals [$i]['attributes'])) {
			foreach (array_keys ($vals [$i]['attributes']) as $attkey)
			$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
				
		}
		else {
			$attributes = array();
		}

		$result [$vals [$i]['tag']] = array_merge ($attributes, GetChildren ($vals, $i, 'open'));
	}

	ini_set ('track_errors', '0');
	return $result;
}

function GetChildren($vals, &$i, $type) {
	if ($type == 'complete') {
		if (isset ( $vals [$i] ['value'] ))
		return ($vals [$i] ['value']);
		else
		return '';
	}

	$children = array (); // Contains node data

	/* Loop through children */
	while ( $vals [++ $i] ['type'] != 'close' ) {
		$type = $vals [$i] ['type'];
		// first check if we already have one and need to create an array
		if (isset ( $children [$vals [$i] ['tag']] )) {
			if (is_array ( $children [$vals [$i] ['tag']] )) {
				$temp = array_keys ( $children [$vals [$i] ['tag']] );
				// there is one of these things already and it is itself an array
				if (is_string ( $temp [0] )) {
					$a = $children [$vals [$i] ['tag']];
					unset ( $children [$vals [$i] ['tag']] );
					$children [$vals [$i] ['tag']] [0] = $a;
				}
			} else {
				$a = $children [$vals [$i] ['tag']];
				unset ( $children [$vals [$i] ['tag']] );
				$children [$vals [$i] ['tag']] [0] = $a;
			}

			$children [$vals [$i] ['tag']] [] = GetChildren ( $vals, $i, $type );
		}
		/** 20090722 : add creating array for offers **/
		elseif ($vals [$i]['tag']=="offer") {
			$children [$vals [$i]['tag']] = array();
			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		}
		elseif ($vals [$i]['tag']=="category") {
			$children [$vals [$i]['tag']] = array();
			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		}  elseif ($vals [$i]['tag']=="group") {
			$children [$vals [$i]['tag']] = array();
			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		} elseif ($vals [$i]['tag']=="property") {
			$children [$vals [$i]['tag']] = array();
			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		} elseif ($vals [$i]['tag']=="product") {
			$children [$vals [$i]['tag']] = array();
			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		} else
		$children [$vals [$i]['tag']] = GetChildren ( $vals, $i, $type );
		// I don't think I need attributes but this is how I would do them:

		if (isset ( $vals [$i] ['attributes'] )) {
			$attributes = array ();
			foreach ( array_keys ( $vals [$i] ['attributes'] ) as $attkey )
			$attributes [$attkey] = $vals [$i] ['attributes'] [$attkey];
			// now check: do we already have an array or a value?
			if (isset ( $children [$vals [$i] ['tag']] )) {
				// case where there is an attribute but no value, a complete with an attribute in other words
				if ($children [$vals [$i] ['tag']] == '') {
					unset ( $children [$vals [$i] ['tag']] );
					$children [$vals [$i] ['tag']] = $attributes;
				} // case where there is an array of identical items with attributes
				elseif (is_array ( $children [$vals [$i] ['tag']] )) {
					$index = count ( $children [$vals [$i] ['tag']] ) - 1;
					// probably also have to check here whether the individual item is also an array or not or what... all a bit messy
					if ($children [$vals [$i] ['tag']] [$index] == '') {
						unset ( $children [$vals [$i] ['tag']] [$index] );
						$children [$vals [$i] ['tag']] [$index] = $attributes;
					}
					$children [$vals [$i] ['tag']] [$index] = array_merge ( $children [$vals [$i] ['tag']] [$index], $attributes );
				} else {
					$value = $children [$vals [$i] ['tag']];
					unset ( $children [$vals [$i] ['tag']] );
					$children [$vals [$i] ['tag']] ['value'] = $value;
					$children [$vals [$i] ['tag']] = array_merge ( $children [$vals [$i] ['tag']], $attributes );
				}
			} else
			$children [$vals [$i] ['tag']] = $attributes;
		}
	}

	return $children;
}
