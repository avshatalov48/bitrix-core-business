<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery"))
{
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return;
}
elseif (!IsModuleInstalled("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}

$arParams['WIDTH'] = $arParams['WIDTH'] > 0 ? $arParams['WIDTH'] : '300';
$arParams['HEIGHT'] = $arParams['HEIGHT'] > 0 ? $arParams['HEIGHT'] : '300';
$hash = md5(serialize($arParams));

if (isset($_REQUEST['image_rotator']) && $_REQUEST['image_rotator'] == 'get_xml' && $hash == $_REQUEST['h'])
{
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;

	$res = $APPLICATION->IncludeComponent(
		"bitrix:photogallery.detail.list.ex",
		"",
		Array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"BEHAVIOUR" => $arParams["BEHAVIOUR"],
			"USER_ALIAS" => $arParams["USER_ALIAS"],
			"PERMISSION" => $arParams["PERMISSION"],
			"SECTION_ID" => $arParams["SECTION_ID"],
			"INCLUDE_SUBSECTIONS" => 'Y',
			"SECTION_CODE" => $arParams["SECTION_CODE"],
			"PAGE_ELEMENTS" => $arParams["PAGE_ELEMENTS"],
			"ELEMENT_SORT_FIELD" =>$arParams["ELEMENT_SORT_FIELD"],
			"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
			"ELEMENT_SORT_FIELD1"	=> $arParams["ELEMENT_SORT_FIELD1"],
			"ELEMENT_SORT_ORDER1"	=> $arParams["ELEMENT_SORT_ORDER1"],
			"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
			"DRAG_SORT" => "N",
			"DETAIL_URL" => $arParams["DETAIL_URL"],
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"JUST_RETURN_DATA_JS" => "Y"
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);

$APPLICATION->RestartBuffer();
header("Status: 200 OK");
header("content-type:text/xml;charset=utf-8");
echo '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
	<trackList>
<?
if (is_array($res) && count($res) > 0):

$maxChar = 28;
if ($arParams['WIDTH'] != 300)
	$maxChar = intval(($arParams['WIDTH'] - 100) / 8);

foreach($res as $photo):

		if (mb_strlen($photo['description']) > $maxChar)
			$title = trim(mb_substr($photo['description'], 0, $maxChar)).'...';
		else
			$title = $photo['album_name'].' - '.$photo['description'];
		$title = trim($title, ' -');
		$detailUrl = $photo['detail_url'];

		if (!defined("BX_UTF"))
		{
			$title = $GLOBALS["APPLICATION"]->ConvertCharset($title, 'Windows-1251', 'UTF-8');
			$photo['author_name'] = $GLOBALS["APPLICATION"]->ConvertCharset($photo['author_name'], 'Windows-1251', 'UTF-8');
			$detailUrl = $GLOBALS["APPLICATION"]->ConvertCharset($detailUrl, 'Windows-1251', 'UTF-8');
			$photo['src'] = $GLOBALS["APPLICATION"]->ConvertCharset($photo['src'], 'Windows-1251', 'UTF-8');
		}
?>
		<track>
			<title><?= $title?></title>
			<creator><?= htmlspecialcharsbx($photo['author_name'])?></creator>
			<location><?= CHTTP::URN2URI($photo['src'])?></location>
			<info><?= $detailUrl?></info>
		</track>
<?endforeach;
endif;?>
	</trackList>
</playlist>
<?
	die();
}

if (!function_exists('bxRotatorAddFlashvar'))
{
	function bxRotatorAddFlashvar($config, $key, $value)
	{
		$value = str_replace('?', '%3F', $value);
		$value = str_replace('=', '%3D', $value);
		$value = str_replace('&', '%26', $value);
		return trim($config.'&'.$key.'='.$value, ' &');
	}
}

$arParams['SWF'] = '/bitrix/components/bitrix/photogallery.imagerotator/templates/.default/imagerotator';
$arParams['ROTATETIME'] = $arParams['ROTATETIME'] > 0 ? $arParams['ROTATETIME'] : 5;
$arParams['TRANSITION'] = $arParams['TRANSITION'] != '' ? $arParams['TRANSITION'] : 'random';

$arParams['BACKCOLOR'] = $arParams['BACKCOLOR'] != '' ? $arParams['BACKCOLOR'] : 'FFFFFF';
$arParams['FRONTCOLOR'] = $arParams['FRONTCOLOR'] != '' ? $arParams['FRONTCOLOR'] : '000000';
$arParams['LIGHTCOLOR'] = $arParams['LIGHTCOLOR'] != '' ? $arParams['LIGHTCOLOR'] : '000000';
$arParams['SCREENCOLOR'] = $arParams['SCREENCOLOR'] != '' ? $arParams['SCREENCOLOR'] : 'FFFFFF';

$arParams['BACKCOLOR'] = '0x'.trim($arParams['BACKCOLOR'], ' #');
$arParams['FRONTCOLOR'] = '0x'.trim($arParams['FRONTCOLOR'], ' #');
$arParams['LIGHTCOLOR'] = '0x'.trim($arParams['LIGHTCOLOR'], ' #');
$arParams['SCREENCOLOR'] = '0x'.trim($arParams['SCREENCOLOR'], ' #');

$arParams['LOGO'] = $arParams['LOGO'] != '' ? $arParams['LOGO'] : '';

$arParams['OVERSTRETCH'] = $arParams['OVERSTRETCH'] == 'Y' ? 'true' : 'false';
$arParams['SHOWICONS'] = $arParams['SHOWICONS'] == 'Y' ? 'true' : 'false';
$arParams['SHOWNAVIGATION'] = $arParams['SHOWNAVIGATION'] == 'N' ? 'false' : 'true';
$arParams['USEFULLSCREEN'] = $arParams['USEFULLSCREEN'] == 'Y' ? 'true' : 'false';

$arParams['DOM_ID'] = '';

$flashvars = '';
$flashvars = bxRotatorAddFlashvar($flashvars, 'file', $APPLICATION->GetCurPageParam("image_rotator=get_xml&h=".$hash.'&'.bitrix_sessid_get(), array("clear_cache", "bitrix_include_areas", "bitrix_show_mode", "back_url_admin", "bx_photo_ajax", "sessid"), false));$flashvars = bxRotatorAddFlashvar($flashvars, 'width', $arParams['WIDTH']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'height', $arParams['HEIGHT']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'rotatetime', $arParams['ROTATETIME']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'transition', $arParams['TRANSITION']);
// Colors
$flashvars = bxRotatorAddFlashvar($flashvars, 'backcolor', $arParams['BACKCOLOR']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'frontcolor', $arParams['FRONTCOLOR']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'lightcolor', $arParams['LIGHTCOLOR']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'screencolor', $arParams['SCREENCOLOR']);
if ($arParams['LOGO'])
	$flashvars = bxRotatorAddFlashvar($flashvars, 'logo', $arParams['LOGO']);

$flashvars = bxRotatorAddFlashvar($flashvars, 'overstretch', $arParams['OVERSTRETCH']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'showicons', $arParams['SHOWICONS']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'shownavigation', $arParams['SHOWNAVIGATION']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'usefullscreen', $arParams['USEFULLSCREEN']);
$flashvars = bxRotatorAddFlashvar($flashvars, 'shuffle', $arParams['SHUFFLE'] == "Y" ? 'true' : 'false');
$flashvars = bxRotatorAddFlashvar($flashvars, 'repeat', 'true');

$arParams['FLASHVARS'] = $flashvars;
$this->IncludeComponentTemplate();
?>