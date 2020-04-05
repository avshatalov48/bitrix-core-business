<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('pull'))
	return;

if (defined('BX_PULL_SKIP_INIT'))
	return;

$userId = 0;
if (defined('PULL_USER_ID'))
{
	$userId = PULL_USER_ID;
}
else if (is_object($GLOBALS['USER']) && intval($GLOBALS['USER']->GetID()) > 0)
{
	$userId = intval($GLOBALS['USER']->GetID());
}
else if (intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0 && \CPullOptions::GetGuestStatus())
{
	$userId = intval($_SESSION["SESS_GUEST_ID"])*-1;
}

if ($userId == 0)
	return;

if (CPullOptions::CheckNeedRun())
{
	CJSCore::Init(array('pull'));

	$arResult = CPullChannel::GetConfig($userId);

	if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	{
		define("BX_PULL_SKIP_INIT", true);
		$this->IncludeComponentTemplate();
	}
}

return $arResult;
?>