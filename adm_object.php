<?php
require_once "include/ilias_header.inc";
require_once "classes/class.Object.php";	// base class for all Object Types
require_once "classes/class.ObjectOut.php";

if (!isset($_GET["type"]))
{
    $obj = getObject($_GET["obj_id"]);
    $_GET["type"] = $obj["type"];
}

//command should be get Parameter
//if there is a post-parameter it is translated, cause it is a buttonvalue
if ($_POST["cmd"] != "")
{
	switch ($_POST["cmd"])
	{
		case $lng->txt("cut"):
			$_GET["cmd"] = "cutAdm";
			break;
		case $lng->txt("copy"):
			$_GET["cmd"] = "copyAdm";
			break;
		case $lng->txt("link"):
			$_GET["cmd"] = "linkAdm";
			break;
		case $lng->txt("paste"):
			$_GET["cmd"] = "pasteAdm";
			break;
		case $lng->txt("clear"):
			$_GET["cmd"] = "clearAdm";
			break;
		case $lng->txt("import"):
			$_GET["cmd"] = "import";
			break;
		case $lng->txt("delete"):
			$_GET["cmd"] = "deleteAdm";
			break;
	}
}
//if no cmd is given default to first property
if (!$_GET["cmd"])
{
	$_GET["cmd"] = $objDefinition->getFirstProperty($_GET["type"]);
}

// CREATE OBJECT CALLS 'createObject' METHOD OF THE NEW OBJECT
if($_REQUEST["new_type"])
{
	$type = $_REQUEST["new_type"];
}
else
{
	$type = $_GET["type"];
}
$method = $_GET["cmd"]."Object";

// CALL METHOD OF OBJECT
// e.g: cmd = 'view' type = 'frm'
// => $obj = new ForumObject(); $obj->viewObject() 
$class_name = $objDefinition->getClassName($type);
$class_constr = $class_name."Object";

require_once("./classes/class.".$class_name."Object.php");
$obj = new $class_constr();
$data = $obj->$method();


// CALL OUTPUT METHOD OF OBJECT
$class_constr = $class_name."ObjectOut";

require_once("./classes/class.".$class_name."ObjectOut.php");
$obj = new $class_constr($data);
$obj->$method();


// display basicdata formular
// TODO: must be changed for clientel processing
if ($_GET["cmd"] == "view" && $obj->type == "adm")
{
	$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
	$tpl->setCurrentBlock("systemsettings");
	require_once("./include/inc.basicdata.php");
	$tpl->parseCurrentBlock();
}
$tpl->show();

?>