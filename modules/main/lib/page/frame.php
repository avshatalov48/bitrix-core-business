<?
//This file still exists because of SiteUpdate features.
//When you reinstall updates (main 17.1.0 with previous ones),
//SiteUpdate includes old version of 'include.php' which invokes Frame::shouldBeEnabled().

$oldClassName = "Bitrix\\Main\\Page\\Frame";
$newClassName = "Bitrix\\Main\\Composite\\Engine";
$newClassFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/composite/engine.php";

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
