<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
		"ADDITIONAL_SETTINGS" => array(
			"NAME" => GetMessage("F_ADDITIONAL_SETTINGS"),
		),
	),
	
	"PARAMETERS" => Array(
		"CACHE_TIME" => Array(),
		
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => intval(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),
		
		"URL_TEMPLATES_TOPIC_SEARCH" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_SEARCH_TEMPLATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "topic_search.php",
			"COLS" => 25
		),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"PAGE_NAVIGATION_WINDOW" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
			"TYPE" => "STRING",
			"DEFAULT" => "11"),
	)
);
?>
