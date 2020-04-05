<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if(is_array($arResult["POST"]) && !empty($arResult["POST"]))
{
	$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
	foreach($arResult["POST"] as $key=>$arPost)
	{
		//Disable vote (reasons: duplicate, completed status, not published)
		$arResult["POST"][$key]["DISABLE_VOTE"] = false;
		if($arResult["POST"][$key]["IS_DUPLICATE"]
			||ToLower($arStatusList[$arPost["POST_PROPERTIES"]["DATA"][CIdeaManagment::UFStatusField]["VALUE"]]["XML_ID"])=='completed'
			||$arResult["POST"][$key]["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
		)
			$arResult["POST"][$key]["DISABLE_VOTE"] = true;
	}
}
?>