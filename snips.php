<?php
/*
Plugin Name: Snips
Plugin URI: http://toki-woki.net/blog/
Description: Easy snippets
Author: Quentin T
Version: 0.2
Author URI: http://toki-woki.net/
*/
add_filter('the_content', 'goSnips');
$snipsFolder=dirname(__FILE__);
$snipSep="\n---";
$defaultSep="\n";
function goSnips($s) {
	// On chope tout ce qui ressemble à un appel
	return preg_replace_callback("/\\[(.*?):(.*?)\\]/", "snipser", $s);
}
function snipser($m) {
	global $snipsFolder, $snipSep, $defaultSep;
	// On récupère la clef et les paramêtres
	$k=$m[1];
	$v=$m[2];
	// L'objet sur lequel on va travailler
	$snipPath=$snipsFolder.'/'.$k.'-model.txt';
	// Si on ne gère pas cette clef, on renvoie tout
	if (!file_exists($snipPath)) return $m[0];
	// On chope le contenu du fichier
	if (!$content=file_get_contents($snipPath)) {
		return _e("Couldn't read $snipPath !");
	}
	// Les valeurs contenues dans le fichier de modèle
	$arContent=explode($snipSep, $content);
	// Le modèle de Snip
	$s=$arContent[0];
	// Les valeurs par défaut
	$rawDefaults=explode($defaultSep, $arContent[1]);
	$defaults=array();
	foreach($rawDefaults as $rdK=>$rdV) {
		$pair=explode("=", $rdV);
		$defaults[$pair[0]]=$pair[1];
	}
	// On découpe en arguments
	$p=explode(",", $v);
	// On calcule le nombre d'éléments à chercher (on s'appuie sur les éléments fournis et les éléments par défaut)
	$maxArgs=max(count($p), max(array_keys($defaults)));
	// Pour chaque argument on remplace son appel
	for($i=0; $i<$maxArgs; $i++) {
		$index=$i+1;
		$placeholder="#".$index."#";
		$given=$p[$i];
		// On regarde si l'argument en question a été fourni et qu'il n'est pas vide
		if (isset($given) && $given!='') $val=$given;
		// Sinon on cherche une valeur par défaut
		else if (isset($defaults[$index])) $val=$defaults[$index];
		// Sinon on ne touche à rien...
		else $val=$placeholder;
		// Hop !
		$s=str_replace($placeholder, $val, $s);
	}
	// On vire les éventuels arguments non fournis...
	$s=preg_replace("/#(\\d*)#/", "", $s);
	// On gère certaines variables magiques
	$s=str_replace("#up#", get_option('home')."/".get_option('upload_path'), $s);
	$s=str_replace("#home#", get_option('home'), $s);
	// Out.
	return $s;
}
?>