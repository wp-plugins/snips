<?php
/*
Plugin Name: Snips
Plugin URI: http://toki-woki.net/blog/
Description: Easy snippets/Shortcode editor
Author: Quentin T
Version: 2.0
Author URI: http://toki-woki.net/
*/

$snipsFolder=dirname(__FILE__);
$snipSep="\r\n---\r\n";
$defaultSnipSep="\n";

function getAvailableSnipFiles() {
	global $snipsFolder;
	$snipFiles=array();
	$snipsHandle = opendir($snipsFolder);
	while (false !== ($file = readdir($snipsHandle))) {
		$fileData=explode("-", $file);
		if (count($fileData)==2 && $fileData[1]=="model.txt") {
			$snipName=$fileData[0];
			$snipFiles[$snipName]=$snipsFolder.'/'.$file;
		}
	}
	closedir($snipsHandle);
	return $snipFiles;
}
$availableSnipFiles=getAvailableSnipFiles();

function getSnipData($snipName) {
	global $snipSep, $defaultSnipSep, $snipsFolder, $availableSnipFiles;
	$snipData=array();
	$arSnipContent=explode($snipSep, file_get_contents($availableSnipFiles[$snipName]));
	$snipData["model"]=$arSnipContent[0];
	$defaults=&$snipData["defaults"];
	$defaults=array();
	if (count($arSnipContent)==2) {
		$rawDefaults=explode($defaultSnipSep, $arSnipContent[1]);
		foreach($rawDefaults as $rdK=>$rdV) {
			$pair=explode("=", $rdV);
			if (count($pair)<2) continue;
			$defaults[$pair[0]]=$pair[1];
		}
	}
	return $snipData;
}
$snipsData=array();
foreach ($availableSnipFiles as $curSnipName=>$curSnipFile) {
	$curSnipData=getSnipData($curSnipName);
	$snipsData[$curSnipName]=$curSnipData;
	add_shortcode($curSnipName, create_function('$atts', 'return snipsHandler("'.$curSnipName.'", $atts);'));
}

function snipsHandler($snipName, $atts) {
	global $snipsData;
	$snipModel=$snipsData[$snipName]['model'];
	$snipDefaults=$snipsData[$snipName]['defaults'];
	// LEGACY
	if (count($atts)==1 && substr($atts[0], 0, 1)==":") {
		$snipValues=explode(',', substr($atts[0], 1));
		// Dummy data (first value has to be #1#)
		array_unshift($snipValues, "snips-legacy-dummy-data");
		$snipValues=array_merge($snipValues, shortcode_atts($snipDefaults, $snipValues));
	} else {
		$snipValues=array_merge(shortcode_atts($snipDefaults, $atts), $atts);
	}
	$res=$snipModel;
	foreach ($snipValues as $k=>$v) {
		$res=str_replace("#$k#", $v, $res);
	}
	return $res;
}

function storeSnip($name, $model, $defaults) {
	global $snipsFolder, $defaultSnipSep, $snipSep;
	$content=$model.$snipSep;
	$defCpt=0;
	foreach ($defaults as $defK => $defV) {
		$defCpt++;
		$content.="$defK=$defV";
		if ($defCpt<count($defaults)) $content.=$defaultSnipSep;
	}
	return file_put_contents("$snipsFolder/$name-model.txt", $content);
}

/* 
 * 
 * ADMIN
 * 
 */

add_action('admin_init', 'snipsAdminInit');
add_action('admin_menu', 'snipsAminMenu');

function snipsAdminInit() {
	wp_register_script('snipsScript', WP_PLUGIN_URL . '/snips/script.js');
}

function snipsAminMenu() {
	$page=add_options_page('Snips Options', 'Snips', 8, 'snips-options', 'snipsOptions');
	add_action('admin_print_scripts-' . $page, 'loadSnipsScript');
}
function loadSnipsScript() {
	wp_enqueue_script('snipsScript');
}

function snipsOptions() {
	global $availableSnipFiles;
	//
	if (empty($_POST["snip-action"])) $step="selection";
	else $step=$_POST["snip-action"];
	if (array_key_exists("new-snip", $_GET)) $step="new";
	//
	if(array_key_exists("delete-snip", $_GET)) {
		@unlink($availableSnipFiles[$_GET["delete-snip"]]);
		$availableSnipFiles=getAvailableSnipFiles();
	}
	//
	echo '<div class="wrap"><h2>Snips</h2>';
	if ($step=="save" || $step=="edit") {
		$snipName=$_POST["snip-name"];
		echo "<h3>Editing a Snip: $snipName <a class='button-primary' href='options-general.php?page=snips-options&delete-snip=$snipName'>Delete it</a></h3>";
	}
	if ($step=="save") {
		$newModel=stripslashes($_POST["snip-model"]);
		$newDefaults=array();
		foreach ($_POST as $postK => $postV) {
			if(substr($postK, 0, 9)=="snip-def-" && $postV!="") {
				$newDefaults[substr($postK, 9)]=$postV;
			}
		}
		if (storeSnip($snipName, $newModel, $newDefaults)) echo '<div class="updated fade"><p><strong>Snip saved.</strong></p></div>';
		$availableSnipFiles=getAvailableSnipFiles();
	}
	$action=$step=="new" ? "options-general.php?page=snips-options" : "";
	echo '<form method="post" action="'.$action.'">
		<table class="form-table">';
	if ($step=="selection") {
		echo '<tr valign="top">
				<th scope="row">Select which Snip to edit</th>
				<td><select name="snip-name">';
		foreach ($availableSnipFiles as $k=>$path) {
			echo "<option>".$k."</option>";
		}
		echo '</select></td>
			</tr></table><input type="hidden" name="snip-action" value="edit" />';
		$submitLabel="Edit that Snip!";
	} else {
		if ($step=="new") {
			$snipData=array("model"=>"", "defaults"=>array());
			echo '<tr valign="top"><th scope="row">New Snip\'s name</th>
			<td><input class="regular-text" type="text" name="snip-name" value="" /></td>';
		} else {
			$snipData=getSnipData($snipName);
		}
		echo '<tr valign="top">
				<th scope="row">Model</th>
				<td><textarea class="large-text code" cols="50" rows="10" name="snip-model" id="snipModel">'.esc_attr($snipData["model"]).'</textarea></td>
			</tr></table><h3>Parameters and default values</h3><h4>No default value means the variable is mandatory</h4>
			<table class="form-table" id="snipsDefaults"><tr valign="top" id="snip-row-model" style="display:none"><th scope="row">defName</th>
			<td><input class="regular-text" type="text" name="snip-def-model" value="" /></td>';
		foreach ($snipData["defaults"] as $defaultName => $defaultVal) {
			echo '<tr valign="top" id="snip-def-'.$defaultName.'-row"><th scope="row">'.$defaultName.'</th>
			<td><input class="regular-text" type="text" name="snip-def-'.$defaultName.'" value="'.$defaultVal.'" /></td>';
		}
		echo '</table><input type="hidden" name="snip-action" value="save" />';
		if ($step!="new") echo '<input type="hidden" name="snip-name" value="'.$snipName.'" />';
		$submitLabel="Save Changes";
	}
	echo '<p class="submit">
			<input class="button-primary" type="submit" value="'.$submitLabel.'" name="Submit"/>';
	if ($step!="selection") echo " <a class='button-primary' href='options-general.php?page=snips-options'>Back</a>";
	else echo " <a class='button-primary' href='options-general.php?page=snips-options&new-snip'>Create new Snip</a>";
	echo '</p>
	</form>
	</div>';
}
?>