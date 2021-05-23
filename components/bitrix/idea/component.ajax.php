<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arAjaxResult = array();

//Moderator actions
if($arResult["IDEA_MODERATOR"] && check_bitrix_sessid())
{
	switch ($_REQUEST["ACTION"])
	{
		case "SET_STATUS":
			$IdeaId = intval($_REQUEST["IDEA_ID"]);
			$bStatusChange = CIdeaManagment::getInstance()->Idea($IdeaId)->SetStatus($_REQUEST["STATUS_ID"]);
			if($bStatusChange)
			{
				$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
				//Clear Cache
				BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
				BXClearCache(True, '/'.SITE_ID.'/idea/tags/');
				BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
				BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
				BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$IdeaId."/");
				BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$IdeaId."/");
			}
			$arAjaxResult["SUCCESS"] = $bStatusChange?"Y":"N";
		break;

		case "GET_STATUS_LIST":
			$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
			$arAjaxResult = array(
				"SUCCESS" => !empty($arStatusList)?'Y':'N',
				"STATUSES" => $arStatusList
			);
		break;
	}
}

//Common actions
if(empty($arAjaxResult))
{
	switch ($_REQUEST["ACTION"])
	{
		case "GET_LIFE_SEARCH":
			unset($_GET["ACTION"], $_GET["AJAX"]); //clear page navigation parameters
			$this->IncludeComponentTemplate($componentPage);
			$arAjaxResult = array(
				"CONTENT" => $APPLICATION->GetViewContent("idea_body"),
				"SUCCESS" => "Y",
			);
		break;

		case "SUBSCRIBE":
			$IdeaId = intval($_REQUEST["IDEA_ID"]);

			$arAjaxResult = array(
				"CONTENT" => "",
				"SUCCESS" => "N",
			);

			if(check_bitrix_sessid() && $USER->IsAuthorized() && $IdeaId>0 && CIdeaManagment::getInstance()->Notification()->getEmailNotify()->add($IdeaId))
			{
				$arAjaxResult["SUCCESS"] = "Y";
				$arAjaxResult["CONTENT"] = GetMessage("IDEA_POST_UNSUBSCRIBE");
			}
		break;

		case "UNSUBSCRIBE":
			$IdeaId = intval($_REQUEST["IDEA_ID"]);

			$arAjaxResult = array(
				"CONTENT" => "",
				"SUCCESS" => "N",
			);

			if(check_bitrix_sessid() && $USER->IsAuthorized() && $IdeaId>0)
			{
				$bNotify = CIdeaManagment::getInstance()->Notification()->getEmailNotify()->delete($IdeaId);
				if($bNotify)
				{
					$arAjaxResult["SUCCESS"] = "Y";
					$arAjaxResult["CONTENT"] = GetMessage("IDEA_POST_SUBSCRIBE");
				}
			}
		break;
	}
}

//Return JSON
$APPLICATION->RestartBuffer();
echo CUtil::PhpToJSObject($arAjaxResult);
die();
?>