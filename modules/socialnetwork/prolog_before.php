<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (
	!defined('PUBLIC_AJAX_MODE') 
	&& !IsModuleInstalled("im")
	&& $GLOBALS["USER"]->IsAuthorized()
)
{
	CUser::SetLastActivityDate($GLOBALS["USER"]->GetID(), true);		
}
?>