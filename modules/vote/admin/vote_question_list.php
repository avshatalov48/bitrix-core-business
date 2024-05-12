<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2019 Bitrix			 #
# https://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################

global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$voteId = intval($request->getQuery("VOTE_ID"));
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE", array("#ID#"=> $voteId)));

(new CAdminContextMenu(array(array(
	"TEXT"	=> GetMessage("VOTE_BACK_TO_VOTE"),
	"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteId,
	"ICON" => "btn_list"))))->Show();
?><?$APPLICATION->IncludeComponent("bitrix:voting.admin.questions", ".default",
	array(
		"VOTE_ID" => $voteId,
		"SHOW_FILTER" => "Y"
	));?><?

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
