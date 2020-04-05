<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::includeModule('sender'))
{
	return;
}

use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = CommonAjax\ActionGetTemplate::get();
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();