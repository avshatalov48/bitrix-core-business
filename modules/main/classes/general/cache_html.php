<?
/**
 * This file is used only for compatibility.
 * Some scripts could include cache_html.php using code like this
 * require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_html.php");
 */

$oldClassName = "CHTMLPagesCache";
$newClassName = "Bitrix\\Main\\Composite\\Helper";
$newClassFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/composite/helper.php";

if (!class_exists($oldClassName, false))
{
	if (class_exists($newClassName, false))
	{
		class_alias($newClassName, $oldClassName, false);
	}
	else if (file_exists($newClassFile))
	{
		require_once($newClassFile);
	}
}