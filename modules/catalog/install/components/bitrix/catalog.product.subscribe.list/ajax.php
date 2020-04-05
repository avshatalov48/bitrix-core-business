<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\SubscribeTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!check_bitrix_sessid() || !Loader::includeModule('catalog')) die();

Loc::loadMessages(__FILE__);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['deleteSubscribe'] == 'Y')
{
	if(empty($_POST['listSubscribeId']) || !is_array($_POST['listSubscribeId']))
	{
		echo Bitrix\Main\Web\Json::encode(array('error' => true));
		die();
	}
	try
	{
		$subscribeManager = new \Bitrix\Catalog\Product\SubscribeManager;
		if(!$subscribeManager->deleteManySubscriptions($_POST['listSubscribeId'], $_POST['itemId']))
		{
			$errorObject = current($subscribeManager->getErrors());
			if($errorObject)
			{
				echo Bitrix\Main\Web\Json::encode(array('error' => true,
					'message' => $errorObject->getMessage()));
				die();
			}
		}

		echo Bitrix\Main\Web\Json::encode(array('success' => true));
		die();
	}
	catch(Exception $e)
	{
		echo Bitrix\Main\Web\Json::encode(array('error' => true, 'message' => $e->getMessage()));
		die();
	}
}

echo Bitrix\Main\Web\Json::encode(array());
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
die();