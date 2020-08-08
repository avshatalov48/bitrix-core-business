<?
use Bitrix\Sale\Exchange\Integration\Admin;
use Bitrix\Main\Localization\Loc;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

Bitrix\Main\Loader::includeModule('sale');

$router = new \Bitrix\Sale\Exchange\Integration\Router();
$r = $router->check();
if($r->isSuccess())
{
	$router->redirect();
}
else
{
	Loc::loadLanguageFile(
		\Bitrix\Main\Application::getInstance()->getContext()->getServer()->getDocumentRoot().'/bitrix/modules/sale/lib/exchange/integration/router.php');

	$link = Admin\Factory::create(Admin\ModeType::APP_LAYOUT_TYPE);
	$title = Loc::getMessage("SALE_ROUTER_INTERNAL_ERROR_TITLE");
	$message = implode('<br>', $r->getErrorMessages());
	$link
		->setPage('/bitrix/services/sale/b24integration/500/rest-app-warning.php')
		->setField('message', urlencode($message))
		->setField('title', $title)
		->redirect();
}

