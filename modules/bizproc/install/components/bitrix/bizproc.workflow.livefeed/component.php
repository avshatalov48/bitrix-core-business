<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('bizproc'))
	return false;

if (!$GLOBALS['USER']->IsAuthorized())
	return false;

$arParams['WORKFLOW_ID'] = (empty($arParams['WORKFLOW_ID']) ? $_REQUEST['WORKFLOW_ID'] : $arParams['WORKFLOW_ID']);

if (!$arParams['WORKFLOW_ID'])
	return false;
$arResult['TASKS'] = CBPViewHelper::getWorkflowTasks($arParams['WORKFLOW_ID'], true, true);
$arResult['WORKFLOW_STATE_INFO'] = CBPStateService::getWorkflowStateInfo($arParams['WORKFLOW_ID']);
$arResult['USER_ID'] = (int)$GLOBALS['USER']->GetId();

if (!empty($arResult['TASKS']['RUNNING']))
{
	foreach ($arResult['TASKS']['RUNNING'] as &$t)
	{
		if ($t['IS_INLINE'] == 'Y')
		{
			$controls = CBPDocument::getTaskControls($t);
			$t['BUTTONS'] = $controls['BUTTONS'];
		}
		if (isset($t['PARAMETERS']['AccessControl']) && $t['PARAMETERS']['AccessControl'] == 'Y')
		{
			$t['DESCRIPTION'] = '';
		}
	}
}
$arResult['noWrap'] = isset($arParams['NOWRAP']) && $arParams['NOWRAP'] == 'Y';

ob_start();
$this->IncludeComponentTemplate();
$message = ob_get_contents();
ob_end_clean();

return array(
	'MESSAGE' => $arResult['noWrap']? $message : htmlspecialcharsEx($message),
	'CACHED_JS_PATH' => '/bitrix/js/bizproc/tools.js',
	"CACHED_CSS_PATH" => array(
		$this->getTemplate()->GetFolder()."/style.css",
		// @TODO: for presentation only, fix
		'/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css'
	)
);