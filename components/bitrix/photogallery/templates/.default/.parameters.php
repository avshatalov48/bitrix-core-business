<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"SHOW_LINK_ON_MAIN_PAGE" => array(
		"NAME" => GetMessage("P_SHOW_LINK_ON_MAIN_PAGE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"id" => GetMessage("P_LINK_NEW"), 
			"shows" => GetMessage("P_LINK_SHOWS"),
			"rating" => GetMessage("P_LINK_RATING"), 
			"comments" => GetMessage("P_LINK_COMMENTS")),
		"DEFAULT" => array("id", "rating", "comments", "shows"),
		"MULTIPLE" => "Y"
	)
);
?>