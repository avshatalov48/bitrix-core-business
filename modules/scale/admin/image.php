<?
/** Bitrix Framework
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\IO\File;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if ($USER->IsAdmin())
{
	$server = isset($_REQUEST['SERVER']) ? trim($_REQUEST['SERVER']): false;
	$param = isset($_REQUEST['PARAM']) ? trim($_REQUEST['PARAM']): false;
	$period = isset($_REQUEST['PERIOD']) ? trim($_REQUEST['PERIOD']): false;

	if($server && $period && $param)
	{
		$pathToImages = "/var/lib/munin";
		$path = $pathToImages.'/'.$server.'/'.$server.'/'.$param.'-'.$period.'.png';
		$f = new File($path);

		if($f->isExists())
		{
			header("Content-type: image/png");
			echo $f->getContents();
		}
	}
}

die();