<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if($arResult["CanUserComment"])
{
	/* @deprecated */
	$arResult["Smiles"] = CBlogSmile::GetSmilesList();
	$arResult["SmilesCount"] = count($arResult["Smiles"]);

	$cache = new CPHPCache;
	$cache_id = "blog_form_comments".serialize($arParams["COMMENT_PROPERTY"]);
	$cache_path = "/blog/form/comments";

	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$Vars = $cache->GetVars();
		$arResult["COMMENT_PROPERTIES"] = $Vars["comment_props"];
		$cache->Output();
	}
	else
	{

		if ($arParams["CACHE_TIME"] > 0)
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

		$arResult["COMMENT_PROPERTIES"] = array("SHOW" => "N");
		if (!empty($arParams["COMMENT_PROPERTY"]))
		{
			$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", 0, LANGUAGE_ID);

			if (count($arParams["COMMENT_PROPERTY"]) > 0)
			{
				foreach ($arPostFields as $FIELD_NAME => $arPostField)
				{
					if (!in_array($FIELD_NAME, $arParams["COMMENT_PROPERTY"]))
						continue;
					$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
					$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
					$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
					$arResult["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
				}
			}
			if (!empty($arResult["COMMENT_PROPERTIES"]["DATA"]))
				$arResult["COMMENT_PROPERTIES"]["SHOW"] = "Y";
		}

		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array("comment_props" => $arResult["COMMENT_PROPERTIES"]));
	}
	CJSCore::Init(array('socnetlogdest'));
	$lastAuthors = Array();
	if($arParams["FROM_LOG"] != "Y")
	{
		$lastAuthors["U".$arPost["AUTHOR_ID"]] = "U".$arPost["AUTHOR_ID"];
		if (
			isset($arResult["CommentsResult"])
			&& is_array($arResult["CommentsResult"])
		)
			foreach($arResult["CommentsResult"] as $v)
				$lastAuthors["U".$v["AUTHOR_ID"]] = "U".$v["AUTHOR_ID"];
	}

	$arLastDestinations = CSocNetLogDestination::GetDestinationSort(array(
		"DEST_CONTEXT" => "MENTION",
		"CODE_TYPE" => 'U'
	));

	$arResult["FEED_DESTINATION"]['LAST'] = array(
		'USERS' => array()
	);
	CSocNetLogDestination::fillLastDestination($arLastDestinations, $arResult["FEED_DESTINATION"]['LAST']);

	if(count($lastAuthors) >= 5)
	{
		$arResult["FEED_DESTINATION"]['LAST']['USERS'] = $lastAuthors;
	}
	else
	{
		$arResult["FEED_DESTINATION"]['LAST']['USERS'] = array_merge($arResult["FEED_DESTINATION"]['LAST']['USERS'], $lastAuthors);
	}

	$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
	$arResult["FEED_DESTINATION"]['DEPARTMENT'] = $arStructure['department'];
	$arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION'] = $arStructure['department_relation'];

	if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
	{
		$arResult["FEED_DESTINATION"]['EXTRANET_USER'] = 'Y';
		$arResult["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetExtranetUser();
	}
	else
	{
		$arDestUser = Array();
		foreach ($arResult["FEED_DESTINATION"]['LAST']['USERS'] as $value)
			$arDestUser[] = str_replace('U', '', $value);

		$arResult["FEED_DESTINATION"]['EXTRANET_USER'] = 'N';
		$arResult["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $arDestUser));
	}
}
?>