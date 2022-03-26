<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
function __main_post_form_replace_template($arResult = false, $arParams = false)
{
	static $control_id = false;

	if ($arResult === false && $arParams === false)
	{
		$result = $control_id;
		$control_id = false;
		return $result;
	}

	if (array_key_exists("PARAMS", $arParams) && $arParams["PARAMS"]["arUserField"]["USER_TYPE_ID"] == "webdav_element")
	{
		if ($arParams['EDIT'] == 'Y')
			$control_id = $arResult['UID'];
	}
	else if (array_key_exists("PARAMS", $arParams) && $arParams["PARAMS"]["arUserField"]["USER_TYPE_ID"] == "disk_file")
	{
		if ($arParams['EDIT'] == 'Y')
			$control_id = $arResult['UID'];
	}
	else if (isset($arResult['CONTROL_UID'])) // if it is a main.file.input
	{
		$control_id = $arResult['CONTROL_UID'];
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