<?php
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2023 Bitrix           #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/
/* @global \CMain $APPLICATION
 * @global \CUser $USER
*/

use Bitrix\Main;
use Bitrix\Vote;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

if (!Main\Loader::includeModule('vote'))
{
	return;
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/vote/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/vote/include.php');
global $adminPage;
$adminPage->hideTitle();

$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->IncludeComponent("bitrix:voting.admin.votes", "");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
