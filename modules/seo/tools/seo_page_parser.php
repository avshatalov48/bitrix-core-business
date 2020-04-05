<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if (!$USER->CanDoOperation('seo_tools'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule('seo');

CUtil::JSPostUnescape();

Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$arCallbacks = array('set_stats' => 'window.BXSetStats', 'set_keywords_stats' => 'window.BXUpdateKeywordsStats');

if (
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& check_bitrix_sessid()
	&& $_REQUEST['url'] && substr($_REQUEST['url'], 0, 1) == '/'
	&& $_REQUEST['site']
	&& $_REQUEST['callback']
	&& array_key_exists($_REQUEST['callback'], $arCallbacks)
)
{
	$bGetFullInfo = $_REQUEST['first'] == 'Y';
	$obChecker = new CSeoPageChecker($_REQUEST['site'], $_REQUEST['url'], true, $bGetFullInfo);

	if (!$obChecker->bError)
	{
		if ($_REQUEST['keywords'])
		{
			$arKeywords = explode(',', $_REQUEST['keywords']);
			foreach ($arKeywords as $k => $v) $arKeywords[$k] = trim($v);
			$arKeywords = array_unique($arKeywords);
			TrimArr($arKeywords);

			$arPageResult = $obChecker->CheckKeyword($arKeywords);

			$arResult = array();

			foreach ($arKeywords as $key => $value)
			{
				$arWordData = array_values($arPageResult[$key]);
				$arWordData = $arWordData[0];
				if (is_array($arWordData))
					$arWordData['CONTRAST'] = number_format($arWordData['CONTRAST'], 2);

				$arResult[] = array(
					$value, $arWordData
				);
			}
		}
		else
		{
			$arResult = array();
		}

		if ($bGetFullInfo)
		{
			$extended = $obChecker->GetExtendedData();
			if (strlen($extended['META_DESCRIPTION']) > 0)
				$extended['META_DESCRIPTION'] = array($extended['META_DESCRIPTION']);
			else
				$extended['META_DESCRIPTION'] = array();

			if (strlen($extended['META_KEYWORDS']) > 0)
				$extended['META_KEYWORDS'] = array($extended['META_KEYWORDS']);
			else
				$extended['META_KEYWORDS'] = array();

			$extended['TITLE'] = array($extended['TITLE']);
			$arExt = $extended;
			$arExt['HEADERS'] = array();
			foreach ($extended['HEADERS'] as $header => $val)
			{
				$arExt['HEADERS'][] = $header.': '.$val;
			}
		}

		echo $arCallbacks[$_REQUEST['callback']].'('.CUtil::PhpToJsObject($arResult).($bGetFullInfo ? ', '.CUtil::PhpToJsObject($obChecker->GetStatistics()).', '.CUtil::PhpToJsObject($obChecker->GetErrors()).', '.CUtil::PhpToJsObject($arExt) : '').'); ';
	}

	if ($ex = $APPLICATION->GetException())
	{
		echo "window.BXSetStatsError('".CUtil::JSEscape(trim($ex->GetString()))."'); ";
	}
}
else
{
	echo 'alert(\'Wrong params!\')';
}
?>