<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
elseif (!CModule::IncludeModule("im"))
	return;

COption::SetOptionString("im", "path_to_user_profile", WIZARD_SITE_DIR.'people/user/#user_id#/');
?>