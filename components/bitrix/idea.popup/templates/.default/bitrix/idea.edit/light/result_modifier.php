<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 */
// Frame with file input to ajax uploading in WYSIWYG editor dialog
if($arResult["imageUploadFrame"] == "Y")
	include_once("image.php");
$arResult['ID'] = randString(6);
$arResult['FORM_NAME'] = "REPLIER".$arResult['ID'];
$arResult["POST_PROPERTIES"]["UF_SHOW_BLOCK"] = false;
if(is_array($arResult["POST_PROPERTIES"]["DATA"]) && !empty($arResult["POST_PROPERTIES"]["DATA"]))
{
	$IsNew = !array_key_exists("Post", $arResult);
	foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):

		//UF prepare templates
		$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_TEMPLATE"] = $arPostField["USER_TYPE"]["USER_TYPE_ID"];
		if($FIELD_NAME == CIdeaManagment::UFCategroryCodeField)
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_TEMPLATE"] .= '_category';
		elseif(CIdeaManagment::UFOriginalIdField == $FIELD_NAME)
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_TEMPLATE"] .= '_duplicate';

		//UF prepare for display
		$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] = true;
		//Skip binded offical props
		if($FIELD_NAME==CIdeaManagment::UFAnswerIdField)
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] = false;
		//If not admin skip statusUFCategroryCodeField
		if($FIELD_NAME==CIdeaManagment::UFStatusField && !$arResult["IDEA_MODERATOR"])
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] = false;
		//If no admin skip duplicate field
		if($FIELD_NAME==CIdeaManagment::UFOriginalIdField && (!$arResult["IDEA_MODERATOR"] || $IsNew))
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] = false;
		//If not admin and not NEW - skip
		if(!$IsNew && $FIELD_NAME==CIdeaManagment::UFCategroryCodeField && !$arResult["IDEA_MODERATOR"])//existed
			$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] = false;

		if($arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"])
			$arResult["POST_PROPERTIES"]["UF_SHOW_BLOCK"] = true;

	endforeach;
}
?>