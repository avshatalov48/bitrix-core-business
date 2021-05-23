<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arFilter = array();

$db_events = GetModuleEvents("main", "OnEventLogGetAuditHandlers");
while($arEvent = $db_events->Fetch())
{
	$arModuleEvents = ExecuteModuleEventEx($arEvent);
	$arFilter = $arFilter + $arModuleEvents->GetFilter();
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"FILTER_SETTINGS" => array(
			"NAME" => GetMessage("EVENT_LIST_FILTER_SETTINGS"),
		)
	),
	"PARAMETERS" => Array(
		"PAGE_NUM" => Array(
			"NAME" => GetMessage("EVENT_LIST_LOG_CNT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "10",
			"COLS" => 3,
			"PARENT" => "ADDITIONAL_SETTINGS",		
		),		
		"FILTER" => Array(
			"NAME" => GetMessage("EVENT_LIST_FILTER"),			
			"TYPE" => "LIST",
			"VALUES" => $arFilter,
			"MULTIPLE" => "Y",
			"SIZE" => count($arFilter),
			"DEFAULT" => array_keys($arFilter),
			"PARENT" => "FILTER_SETTINGS",
		),			
		"USER_PATH" => Array(
			"NAME" => GetMessage("EVENT_LIST_USER_PATH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "#SITE_ID#company/personal/user/#user_id#/",
			"COLS" => 40,  
			"PARENT" => "URL_TEMPLATES", 
		),
		"FORUM_PATH" => Array(
			"NAME" => GetMessage("EVENT_LIST_FORUM_PATH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "#SITE_ID#community/forum/forum#FORUM_ID#/",
			"COLS" => 40,
			"PARENT" => "URL_TEMPLATES",
		),
		"FORUM_TOPIC_PATH" => Array(
			"NAME" => GetMessage("EVENT_LIST_FORUM_TOPIC_PATH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "#SITE_ID#community/forum/forum#FORUM_ID#/topic#TOPIC_ID#/",
			"COLS" => 40,
			"PARENT" => "URL_TEMPLATES",
		),
		"FORUM_MESSAGE_PATH" => Array(
			"NAME" => GetMessage("EVENT_LIST_FORUM_MESSAGE_PATH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "#SITE_ID#community/forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#",
			"COLS" => 40,
			"PARENT" => "URL_TEMPLATES",
		),		
	)
);
?>