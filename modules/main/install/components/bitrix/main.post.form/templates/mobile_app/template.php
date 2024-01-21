<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Bitrix\Main\UI\Extension::load('mobile.utils');

//\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder."/script.js");
//$arParams["FORM_ID"] =
//$arParams["NAME_TEMPLATE"]
/**
 * @var array $arParams
 * @var CMain $APPLICATION
 */
foreach ($arParams["UPLOADS"] as $v)
{
	if (
		is_array($v)
		&& isset($v["USER_TYPE_ID"])
		&& in_array($v["USER_TYPE_ID"], array("file", "disk_file"))
	)
	{
		$arParams["UPLOADS_CID"][$v["USER_TYPE_ID"].$v["ID"]] = array(
			"ID" => $v["ID"],
			"USER_TYPE_ID" => $v["USER_TYPE_ID"],
			"FIELD_NAME" => $v["FIELD_NAME"],
			"VALUE" => $v["VALUE"],
			"MULTIPLE" => $v["MULTIPLE"],
			"MANDATORY" => $v["MANDATORY"],
			"TAG" => $v["USER_TYPE"]["TAG"]
		);
	}
}

?>
<script>
	BX.message({
		MPFFileWasNotUploaded : '<?=GetMessageJS('MPF_FILE_WAS_NOT_UPLOADED')?>',
		MPFIncorrectResponse : '<?=GetMessageJS('MPF_INCORRECT_RESPONSE')?>',
		MPFPlaceholder : '<?=GetMessageJS("MPF_PLACEHOLDER")?>',
		MPFTakeAPhoto : '<?=GetMessageJS("MPF_PHOTO_CAMERA")?>',
		MPFSelectFromTheGallery : '<?=GetMessageJS("MPF_PHOTO_GALLERY")?>',
		MPFButtonSend : '<?=GetMessageJS("MPF_SEND")?>',
		MPFButtonCancel : '<?=GetMessageJs("MPF_CANCEL")?>',
		MPFPostFormDisk: '<?=GetMessageJS("MOBILE_LOG_POST_FORM_DISK_MSGVER_1")?>',
		MPFPostFormDiskTitle: '<?=GetMessageJS("MOBILE_LOG_POST_FORM_DISK_TITLE")?>',
		MPFPostFormPhotoGallery : '<?=GetMessageJS("MOBILE_LOG_POST_FORM_PHOTO_GALLERY")?>',
		MPFPostFormPhotoCamera : '<?=GetMessageJS("MOBILE_LOG_POST_FORM_PHOTO_CAMERA")?>',
		SITE_DIR : '<?=SITE_DIR?>'
	});
	BX.ready(function() {
		var f = function() {
			BX.MPF.createInstance(<?=CUtil::PhpToJSObject(array(
				"formId" => $arParams["FORM_ID"],
				"text" => array_change_key_case($arParams["TEXT"], CASE_LOWER),
				"CID" => $arParams["UPLOADS_CID"],
				"forumContext" => (!empty($arParams["FORUM_CONTEXT"]) ? $arParams["FORUM_CONTEXT"] : '')
			))?>);
			BX.removeCustomEvent(window, "main.post.form/mobile", f);
		};
		BX.addCustomEvent(window, "main.post.form/mobile", f);
		if (BX["MPF"])
			f();
		else
			BX.loadScript('<?=\CUtil::GetAdditionalFileURL($templateFolder.'/script.js', true)?>');
	});
</script>