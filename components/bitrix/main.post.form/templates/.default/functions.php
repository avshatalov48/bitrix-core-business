<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
function __main_post_form_replace_template($arResult = false, $arParams = false)
{
	static $control_id = false;

	if ($arResult === false && $arParams === false)
		return $control_id;

	if (array_key_exists("PARAMS", $arParams) && $arParams["PARAMS"]["arUserField"]["USER_TYPE_ID"] == "webdav_element")
	{
		if ($arParams['EDIT'] == 'Y')
			$control_id = $arResult['UID'];
	}
	if (array_key_exists("PARAMS", $arParams) && $arParams["PARAMS"]["arUserField"]["USER_TYPE_ID"] == "disk_file")
	{
		if ($arParams['EDIT'] == 'Y')
			$control_id = $arResult['UID'];
	}
	else if ($arParams["arUserField"]["USER_TYPE_ID"] == "file")
	{
		$control_id = $GLOBALS["APPLICATION"]->IncludeComponent(
			'bitrix:main.file.input',
			'drag_n_drop',
			array(
				'CONTROL_ID' => \Bitrix\Main\UI\FileInputUtility::instance()->getUserFieldCid($arParams['arUserField']),
				'INPUT_NAME' => $arParams["arUserField"]["FIELD_NAME"],
				'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
				'INPUT_VALUE' => $arResult["VALUE"],
				'MAX_FILE_SIZE' => intval($arParams['arUserField']['SETTINGS']['MAX_ALLOWED_SIZE']),
				'MULTIPLE' => $arParams['arUserField']['MULTIPLE'],
				'MODULE_ID' => 'uf',
				'ALLOW_UPLOAD' => 'A'
			),
			null,
			array("HIDE_ICONS" => true)
		);
	}
	return true;
}
function __main_post_form_image_resize(&$arCustomFile, $arParams = null)
{
	static $arResizeParams = array();

	if ($arParams !== null)
	{
		if (is_array($arParams) && array_key_exists("width", $arParams) && array_key_exists("height", $arParams))
		{
			$arResizeParams = $arParams;
		}
		elseif(intval($arParams) > 0)
		{
			$arResizeParams = array("width" => intval($arParams), "height" => intval($arParams));
		}
	}

	if ((!is_array($arCustomFile)) || !isset($arCustomFile['fileID']))
		return false;

	if (array_key_exists("ID", $arCustomFile))
	{
		$arFile = $arCustomFile;
		$fileID = $arCustomFile['ID'];
	}
	else
	{
		$fileID = $arCustomFile['fileID'];
		$arFile = CFile::MakeFileArray($fileID);
		$arFile1 = CFile::GetByID($fileID)->fetch();
		if (is_array($arFile) && is_array($arFile1))
		{
			$arCustomFile = array_merge($arFile, $arFile1, $arCustomFile);
		}
	}

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
?>