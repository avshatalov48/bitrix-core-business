<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (($_REQUEST["src_site"] ?? '') <> '')
{
	$gd_site_id = $_REQUEST["src_site"];
}
else
{
	$gd_site_id = SITE_ID;
}

if (
	IsModuleInstalled("socialnetwork")
	&& COption::GetOptionString("socialnetwork", "allow_frields", "Y", $gd_site_id) == "Y"
)
{
	$arDescription = [
		"NAME"=>GetMessage("GD_SONET_USER_FRIENDS_NAME"),
		"DESCRIPTION"=>GetMessage("GD_SONET_USER_FRIENDS_DESC"),
		"ICON"=>"",
		"GROUP"=> ["ID"=>"sonet"],
		"NOPARAMS"=>"Y",
		"SU_ONLY" => true,
		"FRIENDS_ONLY" => true,
		"CAN_BE_FIXED"=> true,
	];
}
else
{
	return false;
}
