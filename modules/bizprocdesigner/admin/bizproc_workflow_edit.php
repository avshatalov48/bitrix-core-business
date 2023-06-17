<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

$APPLICATION->IncludeComponent(
	"bitrix:bizproc.workflow.edit",
	"",
	array(
		'IS_ADMIN_SECTION' => 'Y',
		"MODULE_ID" => MODULE_ID,
		"ENTITY" => ENTITY,
		"DOCUMENT_TYPE" => $_REQUEST['document_type'],
		"ID" => $_REQUEST['ID'] ?? null,
		"EDIT_PAGE_TEMPLATE" => "/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&entity=".AddSlashes(ENTITY)."&document_type=".AddSlashes($_REQUEST['document_type'])."&ID=#ID#&back_url_list=".urlencode($_REQUEST["back_url_list"]),
		"LIST_PAGE_URL" => "/bitrix/admin/".MODULE_ID."_bizproc_workflow_admin.php?lang=".LANGUAGE_ID."&entity=".AddSlashes(ENTITY)."&document_type=".AddSlashes($_REQUEST['document_type'])."",
		"SHOW_ADMIN_TOOLBAR" => "Y",
		"SET_TITLE" => 'Y',
	)
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");