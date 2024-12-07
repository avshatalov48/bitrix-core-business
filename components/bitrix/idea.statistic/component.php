<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule('idea'))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}

if($arParams["BLOG_URL"] == '')
	return;

$arResult = array();

$obCache = new CPHPCache;
$life_time = 60*60*24; //1 day
$cache_id = 'idea_statistic_list_'.$arParams["BLOG_URL"];
$cache_path = '/'.SITE_ID.'/idea/statistic_list/';

if($obCache->StartDataCache($life_time, $cache_id, $cache_path))
{
		$arResult = CIdeaManagment::getInstance()->Idea()->GetStatusList();

		if($arCurBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
		{
			$dbPosts = CBlogPost::GetList(
				array(),
				array(
					"BLOG_ID" => $arCurBlog["ID"],
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
				),
				false,
				false,
				array("ID", "UF_STATUS")
			);

			while ($arPost = $dbPosts->Fetch())
			{
				$ufStatus = (int) $arPost["UF_STATUS"];
				if ($ufStatus && !isset($arResult[$ufStatus]))
				{
					continue;
				}

				$arResult[$ufStatus]["CNT"]++;
			}
		}

		$obCache->EndDataCache($arResult);
}
else
	$arResult = $obCache->GetVars();

$this->IncludeComponentTemplate();
?>