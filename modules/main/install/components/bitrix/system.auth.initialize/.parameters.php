<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"USER_ID" => array(
			"NAME" => GetMessage("COMP_MAIN_REG_INIT_P_USER_ID_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_id",
		),
		"CHECKWORD" => array(
			"NAME" => GetMessage("COMP_MAIN_REG_INIT_P_CHECKWORD_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "checkword",
		),

	),
);
?>