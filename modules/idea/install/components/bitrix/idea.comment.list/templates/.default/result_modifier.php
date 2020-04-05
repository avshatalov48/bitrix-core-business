<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 */
$arResult["POST_AUTHORS"] = $arResult["AUTHOR_AVATAR"] = array();
$arUserGroups = array();
$arUserAvatars = array();
$arSizes = array('width'=>20, 'height'=>20);

if(is_array($arResult["CommentsResult"][0]))
{
	foreach($arResult["CommentsResult"][0] as $key=>$arComment)
	{
		//Collecting
		if(!array_key_exists($arComment["AUTHOR_ID"], $arUserGroups))
			$arUserGroups[$arComment["AUTHOR_ID"]] = CUser::GetUserGroup($arComment["AUTHOR_ID"]);
		if(!array_key_exists($arComment["arUser"]["ID"], $arResult["AUTHOR_AVATAR"]))
		{
			if($arComment["arUser"]["PERSONAL_PHOTO"]>0)
				$arUserAvatars[$arComment["arUser"]["ID"]] = CFile::ResizeImageGet(
					$arComment["arUser"]["PERSONAL_PHOTO"],
					$arSizes,
					BX_RESIZE_IMAGE_EXACT
				);
			else
				$arUserAvatars[$arComment["arUser"]["ID"]]["src"] = $this->__folder.'/images/default_avatar.png';
		}

		//message status\type
		if(strlen($arComment["urlToShow"])>0)
			$arResult["CommentsResult"][0][$key]["COMMENT_STATUS"] = "hidden";
		elseif($arResult["Post"]["AUTHOR_ID"] == $arComment["AUTHOR_ID"])
			$arResult["CommentsResult"][0][$key]["COMMENT_STATUS"] = "owner";
		elseif(array_intersect($arParams["POST_BIND_USER"], $arUserGroups[$arComment["AUTHOR_ID"]]))
			$arResult["CommentsResult"][0][$key]["COMMENT_STATUS"] = "official";
		else
			$arResult["CommentsResult"][0][$key]["COMMENT_STATUS"] = "common";

		//avatar
		$arResult["CommentsResult"][0][$key]["AUTHOR_AVATAR"] = $arUserAvatars[$arComment["arUser"]["ID"]]["src"];
	}
}
?>