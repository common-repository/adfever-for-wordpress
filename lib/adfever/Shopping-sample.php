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





error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-type: text/html; charset=utf-8');
require_once 'Shopping.php';

// Identifiant du site sur Adfever
define('SITE_ID', 1);

// Na pas en tenir compte pour l'instant
$uuid = 1;

$shop = new Shopping(SITE_ID, $uuid);

// Récupère l'arbre des catégories
//var_dump($shop->getTopCategories(False, 5));

// Récupère l'arbre des catégories limité à la catégorie 188
//var_dump($shop->getCategories(188));

// On récupère les produits de la catégorie 188
// Erreur, la catégorie comporte des enfants
//var_dump($shop->findForCategory(6, 1, 10, SORT_PRICE));

// On récupère les produits de la catégorie 189
//var_dump($shop->findForCategory(189, 1, 2));

// On récupère les produits de la catégorie 189 page 2
//var_dump($shop->findForCategory(189));


// On recherche l'iPod
//var_dump($shop->search('mario kart', array(41, 167), 1, 3));
//var_dump($shop->getBreadCrumb(167));
// On recherche l'iPod page 2
//var_dump($shop->search('iPod', '', 2, 5, SORT_PRICE));

// On récupère le produit 2912674 (iPod 8GB)
//var_dump($shop->find(2238829, SORT_NAME));

// Top produit général
//var_dump($shop->getTop("all", 3));

// Top produit sur la catégorie 189
//var_dump($shop->getTop(2, 4));

// Check du site-id
//var_dump($shop->checkSiteId());
