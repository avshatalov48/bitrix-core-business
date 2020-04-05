<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"REGISTER_PAGE" => array(
			"NAME" => GetMessage("SPCD1_REGISTER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "register.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		
		"SHOP_NAME" => array(
			"NAME" => GetMessage("SPCD1_SHOP_NAME"), 
			"TYPE"=>"STRING", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"", 
			"COLS"=>25,
			"PARENT" => "BASE",
		),
		
		"SHOP_URL" => array(
			"NAME" => GetMessage("SPCD1_SHOP_URL"), 
			"TYPE"=>"STRING", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"", 
			"COLS"=>25,
			"PARENT" => "BASE",
		),
		
		"AFF_REG_PAGE" => array(
			"NAME" => GetMessage("SPCD1_AFF_REG_PAGE"), 
			"TYPE"=>"STRING", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"/affiliate/register.php", 
			"COLS"=>25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" => array(),
	)
);
?>