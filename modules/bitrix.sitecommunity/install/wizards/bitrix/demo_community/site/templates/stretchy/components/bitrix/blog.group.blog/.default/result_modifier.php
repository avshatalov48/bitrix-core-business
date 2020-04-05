<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(count($arResult["BLOG"])>0)
{

	foreach($arResult["BLOG"] as $i => $arBlog)
	{
		$imageFile = false;

		if (intval($arBlog["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule("socialnetwork"))
		{
			$arGroup = CSocNetGroup::GetByID($arBlog["SOCNET_GROUP_ID"]);
			if (intval($arGroup["IMAGE_ID"]) > 0)
				$imageFile = CFile::GetFileArray($arGroup["IMAGE_ID"]);
		}
		elseif(intval($arBlog["OWNER_ID"]) > 0)
		{
			$dbUser = CUser::GetByID($arBlog["OWNER_ID"]);
			$arUser = $dbUser->Fetch();
			if (intval($arUser["PERSONAL_PHOTO"]) > 0)
				$imageFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
		}

		if ($imageFile)
		{
			$arFileTmp = CFile::ResizeImageGet(
					$imageFile,
					array("width" => 75, "height" => 75),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false
			);
			
			$arResult["BLOG"][$i]["AVATAR"] = $arFileTmp;
		}
		else
			$arResult["BLOG"][$i]["AVATAR"] = false;
			
	}
}
?>	