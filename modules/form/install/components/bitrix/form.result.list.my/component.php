<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage('FRLM_NEED_AUTH'));
	return false;
}

if (!CModule::IncludeModule('form'))
{
	ShowError('FRLM_MODULE_NOT_INSTALLED');
	return false;
}

if (!is_array($arParams['FORMS']))
	$arParams['FORMS'] = array();
else
	TrimArr($arParams['FORMS']);

$arParams['NUM_RESULTS'] = intval($arParams['NUM_RESULTS']);
if($arParams['NUM_RESULTS'] <= 0)
	$arParams['NUM_RESULTS'] = 50;

$arResult['FORMS'] = array();
$arResult['RESULTS'] = array();

if (count($arParams['FORMS']) <= 0)
{
	$dbRes = CForm::GetList('sort', 'asc', array('SITE' => SITE_ID));
	while ($arRes = $dbRes->GetNext())
	{
		$arParams['FORMS'][] = $arRes['ID'];
		$arResult['FORMS'][$arRes['ID']] = $arRes;
	}

}

foreach ($arParams['FORMS'] as $FORM_ID)
{
	if (is_array($arResult['FORMS'][$FORM_ID]))
	{
		$arForm = $arResult['FORMS'][$FORM_ID];
	}
	else
	{
		$dbRes = CForm::GetByID($FORM_ID);
		$arForm = $dbRes->GetNext();
	}

	if ($arForm)
	{
		if ($arParams['LIST_URL'])
			$arForm['__LINK'] = str_replace('#FORM_ID#', $FORM_ID, $arParams['LIST_URL']);

		$arResult['FORMS'][$FORM_ID] = $arForm;
		$arResult['RESULTS'][$FORM_ID] = array();

		$dbRes = CFormResult::GetList($FORM_ID, 's_timestamp', 'desc', array('USER_ID' => $USER->GetID()), null, 'Y', $arParams['NUM_RESULTS']);
		$bFirst = true;
		while ($arRes = $dbRes->GetNext())
		{
			if ($bFirst)
			{
				$arResult['FORMS'][$FORM_ID]['__LAST_TS'] = MakeTimeStamp($arRes['TIMESTAMP_X']);
				$bFirst = false;
			}

			$arValues = CFormResult::GetDataByID($arRes['ID'], array(), $arRes1 = null, $arAnswers = null);

			$first_res = current($arValues);
			$arRes['__TITLE'] = trim($first_res[0]['USER_TEXT'] ? $first_res[0]['USER_TEXT'] : $first_res[0]['MESSAGE']);

			$arRes['__RIGHTS'] = CFormResult::GetPermissions($arRes['ID']);

			if ($arParams['EDIT_URL'] && in_array('EDIT', $arRes['__RIGHTS']))
				$arRes['__LINK'] = str_replace(array('#FORM_ID#', '#RESULT_ID#'), array($FORM_ID, $arRes['ID']), $arParams['EDIT_URL']);
			elseif ($arParams['VIEW_URL'])
				$arRes['__LINK'] = str_replace(array('#FORM_ID#', '#RESULT_ID#'), array($FORM_ID, $arRes['ID']), $arParams['VIEW_URL']);

			$arResult['RESULTS'][$FORM_ID][] = $arRes;
		}
	}

	if (!is_array($arResult['RESULTS'][$FORM_ID]) || count($arResult['RESULTS'][$FORM_ID]) <= 0)
	{
		unset($arResult['FORMS'][$FORM_ID]);
		unset($arResult['RESULTS'][$FORM_ID]);
	}
}

if(!function_exists('BX_FSBT'))
{
	function BX_FSBT($a,$b)
	{
		$q='__LAST_TS';
		$c=$a[$q];
		$d=$b[$q];
		return($c==$d?0:($c<$d?1:-1));
	}
};
uasort($arResult['FORMS'], 'BX_FSBT');

$this->IncludeComponentTemplate();