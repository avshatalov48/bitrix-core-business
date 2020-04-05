<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';
$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['SEF_MODE']))
{
	$arParams['SEF_MODE'] = 'N';
	if ($arParams['IN_COMPLEX'] == 'Y')
		$arParams['SEF_MODE'] = $this->GetParent()->arResult['SEF_MODE'];
}

if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if(strlen($arParams['PATH_TO_POST_EDIT'])<=0)
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if(strlen($arParams['PATH_TO_HISTORY'])<=0)
	$arParams['PATH_TO_HISTORY'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if(strlen($arParams['PATH_TO_HISTORY_DIFF'])<=0)
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if(strlen($arParams['PATH_TO_DISCUSSION'])<=0)
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if(strlen($arParams['PATH_TO_CATEGORIES'])<=0)
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");

$arParams['PATH_TO_SEARCH'] = trim($arParams['PATH_TO_SEARCH']);
if(strlen($arParams['PATH_TO_SEARCH'])<=0)
{
	$arParams['PATH_TO_SEARCH'] = htmlspecialcharsbx($APPLICATION->GetCurPage());
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y' &&
		strpos($this->GetParent()->GetName(), 'socialnetwork') === false)
		$arParams['PATH_TO_SEARCH'] = $this->GetParent()->arResult['PATH_TO_SEARCH'];
}

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];
}

$GLOBALS['arParams'] = &$arParams;

if (!CModule::IncludeModule('wiki'))
	return;

if(!CModule::IncludeModule('iblock'))
	return;

if (empty($arParams['IBLOCK_ID']))
	return;

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	if(!CModule::IncludeModule('socialnetwork'))
		return;
}

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{
	$iblock_id_tmp = CWikiSocnet::RecalcIBlockID($arParams["SOCNET_GROUP_ID"]);
	if ($iblock_id_tmp)
		$arParams['IBLOCK_ID'] = $iblock_id_tmp;

	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID']))
		return;
}

if (!CWikiUtils::IsWriteable())
	return;

if(CWikiUtils::isCategoryVirtual($arParams["ELEMENT_NAME"]))
	return;

$arParams['ELEMENT_NAME'] = CWikiUtils::htmlspecialcharsback($arParams['ELEMENT_NAME']);
$arFilter = array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'CHECK_PERMISSIONS' => 'N',
	'ACTIVE' => ''
);

$bNotPage = false;
if (empty($arParams['ELEMENT_NAME']))
{
	$bNotPage = true;
	$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);
}

$arResult['ELEMENT'] = array();
$arPages = array();

if (CWikiSocnet::IsSocNet())
{
	$sOper = str_replace('group_wiki_post_', '', $this->GetParent()->__template->__page);
	if ($this->GetParent()->__template->__page == $sOper)
		$sOper = str_replace('group_wiki_', '', $this->GetParent()->__template->__page);

	if ($sOper == 'post_edit' && !isset($_REQUEST[$arParams['OPER_VAR']]))
		$sOper = 'edit';

	if ($sOper != 'post' && $sOper != 'index' && !isset($_REQUEST[$arParams['OPER_VAR']]))
		$_REQUEST[$arParams['OPER_VAR']] = $sOper;
}

if (!is_null($_REQUEST[$arParams['OPER_VAR']]))
{
	$arPages[] = 'article';
	$arPages[] = $_REQUEST[$arParams['OPER_VAR']];
}
if (isset($_REQUEST['oldid']))
{
	$arPages[] = 'article';
	$arPages[] = 'history_diff';
}

$bNotEl =  false;
if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::GetElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
{
	if ($arResult['ELEMENT']['ACTIVE'] == 'N')
	{
		$bNotEl = true;
		$arPages[] = 'add';
	}
	$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
}
else
{
	$bNotEl = true;
	if ($bNotPage || empty($arParams['ELEMENT_NAME']))
	{
		$arResult['ELEMENT']['NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']); //http://jabber.bx/view.php?id=28710

		if(empty($arResult['ELEMENT']['NAME']))
			$arResult['ELEMENT']['NAME'] = GetMessage('WIKI_DEFAULT_PAGE_NAME'); //todo: insert into CWiki::GetDefaultPage()

		$arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME'];
	}
	else
		$arResult['ELEMENT']['NAME'] = $arParams["ELEMENT_NAME"];

	$arParams['ELEMENT_ID'] = 0;
	$arResult['ELEMENT']['ID'] = 0;
	$sServiceName = '';
	$arPages[] = 'add';
}

$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks($arPages);

if ($bNotEl && !empty($arResult['TOPLINKS']))
{
	$arResult['TOPLINKS']['add']['LINK'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'],
			array(
				'wiki_name' => rawurlencode($arResult['ELEMENT']['NAME']),
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		array($arParams['OPER_VAR'] => 'add')
	);

}
$arResult['TYPE'] = $arParams['MENU_TYPE'];

$this->IncludeComponentTemplate();

unset($GLOBALS['arParams']);
?>