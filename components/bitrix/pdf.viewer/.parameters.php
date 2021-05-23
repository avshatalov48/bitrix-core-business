<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	'PARAMETERS' => array(
		'VIEWER_ID' => array(
			"NAME" => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_VIEWER_ID"),
			"DEFAULT" => "",
		),
		'PATH' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_PATH"),
			"DEFAULT" => "",
			"TYPE" => "FILE",
			"FD_TARGET" => "F",
			"FD_EXT" => 'pdf',
			"FD_UPLOAD" => true,
			"FD_USE_MEDIALIB" => false,
		),
		'IFRAME' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_IFRAME"),
			'DEFAULT' => 'N',
			'TYPE' => 'CHECKBOX',
			'REFRESH' => 'Y',
		),
		'TITLE' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_TITLE"),
			"DEFAULT" => "",
			'HIDDEN' => ($arCurrentValues["IFRAME"] == 'Y' ? 'N' : 'Y'),
		),
		'WIDTH' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_WIDTH"),
			'DEFAULT' => 900,
			'COLS' => 10,
			'HIDDEN' => ($arCurrentValues["IFRAME"] != 'Y' ? 'N' : 'Y'),
		),
		'HEIGHT' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_HEIGHT"),
			'DEFAULT' => 600,
			'COLS' => 10,
			'HIDDEN' => ($arCurrentValues["IFRAME"] != 'Y' ? 'N' : 'Y'),
		),
		'PRINT' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_PRINT"),
			'DEFAULT' => 'N',
			'TYPE' => 'CHECKBOX',
			'HIDDEN' => ($arCurrentValues["IFRAME"] == 'Y' ? 'N' : 'Y'),
		),
		'PRINT_URL' => array(
			'NAME' => GetMessage("FILEMAN_PDFVIEWER_PARAMETER_PRINT_URL"),
			'DEFAULT' => '',
			'HIDDEN' => ($arCurrentValues["IFRAME"] != 'Y' ? 'N' : 'Y'),
		),
	)
);