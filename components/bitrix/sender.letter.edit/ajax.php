<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = CommonAjax\ActionGetTemplate::get();
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();