<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('form'))
	return;

$arForms = array();
$dbRes = CForm::GetList('s_sort', 'asc', array('ACTIVE' => 'Y'));
while ($arRes = $dbRes->Fetch())
{
	$arForms[$arRes['ID']] = '['.$arRes['SID'].'] '.$arRes['NAME'];
}

$arComponentParameters = array(
	'GROUPS' => array(),
	'PARAMETERS' => array(
		'FORMS' => array(
			'NAME' => GetMessage('FRLM_PARAM_FORMS'),
			'TYPE' => 'LIST',
			'VALUES' => $arForms,
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'Y',
			'PARENT' => 'BASE',
		),
		
		'NUM_RESULTS' => array(
			'NAME' => GetMessage('FRLM_PARAM_NUM_RESULTS'),
			'TYPE' => 'STRING',
			'DEFAULT' => '10',
		),
		
		'LIST_URL' => array(
			'NAME' => GetMessage('FRLM_PARAM_LIST_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'my_result_list.php?WEB_FORM_ID=#FORM_ID#',
		),
		
		'VIEW_URL' => array(
			'NAME' => GetMessage('FRLM_PARAM_VIEW_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'my_result_view.php?WEB_FORM_ID=#FORM_ID#&RESULT_ID=#RESULT_ID#',
		),
		
		'EDIT_URL' => array(
			'NAME' => GetMessage('FRLM_PARAM_EDIT_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'my_result_edit.php?WEB_FORM_ID=#FORM_ID#&RESULT_ID=#RESULT_ID#',
		),
	)
);
?>