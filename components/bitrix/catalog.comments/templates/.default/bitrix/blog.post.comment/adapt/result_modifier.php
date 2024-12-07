<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
if (!function_exists("__MPF_ImageResizeHandler"))
{
	function __MPF_ImageResizeHandler(&$arCustomFile)
	{
		$arResizeParams = array("width" => 400, "height" => 400);

		if ((!is_array($arCustomFile)) || !isset($arCustomFile['fileID']))
			return false;

		$fileID = $arCustomFile['fileID'];

		$arFile = CFile::MakeFileArray($fileID);
		if (CFile::CheckImageFile($arFile) === null)
		{
			$aImgThumb = CFile::ResizeImageGet(
				$fileID,
				array("width" => 90, "height" => 90),
				BX_RESIZE_IMAGE_EXACT,
				true
			);
			$arCustomFile['img_thumb_src'] = $aImgThumb['src'];

			if (!empty($arResizeParams))
			{
				$aImgSource = CFile::ResizeImageGet(
					$fileID,
					array("width" => $arResizeParams["width"], "height" => $arResizeParams["height"]),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$arCustomFile['img_source_src'] = $aImgSource['src'];
				$arCustomFile['img_source_width'] = $aImgSource['width'];
				$arCustomFile['img_source_height'] = $aImgSource['height'];
			}
		}

	}
}

if (!empty($arParams["UPLOAD_FILE_PARAMS"]))
{
	$bNull = null;
	__MPF_ImageResizeHandler($bNull, $arParams["UPLOAD_FILE_PARAMS"]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
{
	AddEventHandler('main',  "main.file.input.upload", '__MPF_ImageResizeHandler');
}

$arParams['PARENT_PARAMS'] ??= null;
$arParams['is_ajax_post'] ??= null;

$arResult['NEED_NAV'] ??= null;
$arResult['MESSAGE'] ??= '';
$arResult['ERROR_MESSAGE'] ??= '';
$arResult['FATAL_MESSAGE'] ??= '';
$arResult['imageUploadFrame'] ??= null;
$arResult['is_ajax_post'] ??= null;
$arResult['COMMENT_ERROR'] ??= '';
$arResult['RATING'] ??= null;
