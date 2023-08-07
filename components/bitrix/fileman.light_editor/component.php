<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("fileman"))
	return ShowError(GetMessage("EC_FILEMAN_MODULE_NOT_INSTALLED"));

$toolbarConfig = array(
	'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat',
	'CreateLink', 'DeleteLink', 'Image', 'Video',
	'BackColor', 'ForeColor',
	'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
	//'=|=',
	'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
	'StyleList', 'HeaderList',
	'FontList', 'FontSizeList',
);

if (is_array($arParams['TOOLBAR_CONFIG'] ?? null))
{
	$toolbarConfig = $arParams['TOOLBAR_CONFIG'];
}

if (($arParams['VIDEO_ALLOW_VIDEO'] ?? null) != 'N')
{
	$videoSettings = array(
		'maxWidth' => $arParams['VIDEO_MAX_WIDTH'] ?? null,
		'maxHeight' => $arParams['VIDEO_MAX_HEIGHT'] ?? null,
		'WMode' => $arParams['VIDEO_WMODE'] ?? null,
		'windowless' => ($arParams['VIDEO_WINDOWLESS'] ?? null) != 'N',
		'bufferLength' => intval($arParams['VIDEO_BUFFER'] ?? null),
		'skin' => $arParams['VIDEO_SKIN'] ?? null,
		'logo' => $arParams['VIDEO_LOGO'] ?? null,
	);
}
else
{
	$ind = array_search('Video', $toolbarConfig);
	if ($ind !== false)
	{
		unset($toolbarConfig[$ind]);
		$toolbarConfig = array_values($toolbarConfig);
	}
	$videoSettings = false;
}

$LHE = new CLightHTMLEditor;
$LHE->Show(array(
	'id' => $arParams['ID'] ?? null,
	'content' => $arParams['~CONTENT'] ?? null,
	'inputName' => $arParams['INPUT_NAME'] ?? null,
	'inputId' => $arParams['INPUT_ID'] ?? null,
	'width' => $arParams['WIDTH'] ?? null,
	'height' => $arParams['HEIGHT'] ?? null,
	'bUseFileDialogs' => ($arParams['USE_FILE_DIALOGS'] ?? null) == 'Y',
	'jsObjName' => $arParams['JS_OBJ_NAME'] ?? null,
	'toolbarConfig' => $toolbarConfig,
	'videoSettings' => $videoSettings,
	'bResizable' => ($arParams['RESIZABLE'] ?? null) == 'Y',
	'bAutoResize' => ($arParams['AUTO_RESIZE'] ?? null) != 'N'
));
?>