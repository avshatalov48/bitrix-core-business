<?
// set EUV for logged events
$dbResult = CSocNetLog::GetList(Array("ENTITY_ID" => "ASC"), Array(), array("ENTITY_TYPE", "ENTITY_ID"));
while ($arResult = $dbResult->Fetch())
{
	if ($arResult["ENTITY_TYPE"] == "U" && intval($arResult["ENTITY_ID"]) > 0)
		CSocNetEventUserView::SetUser($arResult["ENTITY_ID"], false, false, true);
	elseif  ($arResult["ENTITY_TYPE"] == "G" && intval($arResult["ENTITY_ID"]) > 0)
		CSocNetEventUserView::SetGroup($arResult["ENTITY_ID"], true);
}

// set EUV for wiki
$dbResult = CSocNetEventUserView::GetList(array("ENTITY_ID" => "DESC"), Array("ENTITY_TYPE" => "G"), array("ENTITY_ID"));
while ($arResult = $dbResult->Fetch())
	CSocNetEventUserView::SetFeature("G", $arResult["ENTITY_ID"], "wiki");

// set EUV for news
if (IsModuleInstalled("intranet"))
{
	$dbResult = CSocNetEventUserView::GetList(
					array("ENTITY_ID" => "ASC"),
					array(
						"ENTITY_TYPE" => "N",
					)
	);
	$arResult = $dbResult->Fetch();
	if (!$arResult)
		CSocNetEventUserView::Add(
						array(
							"ENTITY_TYPE" => "N",
							"ENTITY_ID" => 0,
							"EVENT_ID" => "news",
							"USER_ID" => 0,
							"USER_ANONYMOUS" => "N"
						)
		);
}

$dbResult = CSocNetEventUserView::GetList(array("ENTITY_ID" => "DESC"), Array("ENTITY_TYPE" => "G"), array("ENTITY_ID"));
while ($arResult = $dbResult->Fetch())
	CSocNetEventUserView::SetFeature("G", $arResult["ENTITY_ID"], "wiki");

// set blog_comment and blog_post for blog
$dbResult = CSocNetLogEvents::GetList(
				array("ENTITY_ID" => "DESC"), 
				array(
					"EVENT_ID" => array(
						"blog_post", 
						"blog_comment",
						"blog_post_micro",
					),
				),
				false, 
				false, 
				array("ID")
			);
while ($arResult = $dbResult->Fetch())
	CSocNetLogEvents::Delete($arResult["ID"]);

$dbResult = CSocNetLogEvents::GetList(array("ENTITY_ID" => "DESC"), Array("EVENT_ID" => "blog"));
while ($arResult = $dbResult->Fetch())
{
	$arLogEvent = array(
		"USER_ID" 		=> $arResult["USER_ID"],
		"ENTITY_TYPE" 	=> $arResult["ENTITY_TYPE"],
		"ENTITY_ID" 	=> $arResult["ENTITY_ID"],
		"ENTITY_CB" 	=> $arResult["ENTITY_CB"],
		"ENTITY_MY" 	=> $arResult["ENTITY_MY"],
		"MAIL_EVENT" 	=> $arResult["MAIL_EVENT"],
		"TRANSPORT" 	=> $arResult["TRANSPORT"],
		"VISIBLE" 		=> $arResult["VISIBLE"]
	);
	if (strlen($arResult["SITE_ID"]) > 0)
		$arLogEvent["SITE_ID"] = $arResult["SITE_ID"];
	else
		$arLogEvent["SITE_ID"] = false;
	
	$arLogEventToAdd = array_merge($arLogEvent, array("EVENT_ID" => "blog_post"));
	CSocNetLogEvents::Add($arLogEventToAdd);
	
	$arLogEventToAdd = array_merge($arLogEvent, array("EVENT_ID" => "blog_comment"));
	CSocNetLogEvents::Add($arLogEventToAdd);
	
	$arLogEventToAdd = array_merge($arLogEvent, array("EVENT_ID" => "blog_post_micro"));
	CSocNetLogEvents::Add($arLogEventToAdd);
}

// set system_friends and system_groups for user system
$dbResult = CSocNetLogEvents::GetList(
				array("ENTITY_ID" => "DESC"), 
				array(
					"ENTITY_TYPE" 	=> "U",
					"EVENT_ID" 		=> array(
						"system_friends", 
						"system_groups"
					)
				),
				false, 
				false, 
				array("ID")
			);
while ($arResult = $dbResult->Fetch())
	CSocNetLogEvents::Delete($arResult["ID"]);

$dbResult = CSocNetLogEvents::GetList(
				array("ENTITY_ID" => "DESC"), 
				array(
					"ENTITY_TYPE" 	=> "U",
					"EVENT_ID" 		=> "system"
				)
			);
while ($arResult = $dbResult->Fetch())
{
	$arLogEvent = array(
		"USER_ID" 		=> $arResult["USER_ID"],
		"ENTITY_TYPE" 	=> $arResult["ENTITY_TYPE"],
		"ENTITY_ID" 	=> $arResult["ENTITY_ID"],
		"ENTITY_CB" 	=> $arResult["ENTITY_CB"],
		"ENTITY_MY" 	=> $arResult["ENTITY_MY"],
		"MAIL_EVENT" 	=> $arResult["MAIL_EVENT"],
		"TRANSPORT" 	=> $arResult["TRANSPORT"],
		"VISIBLE" 		=> $arResult["VISIBLE"]
	);
	if (strlen($arResult["SITE_ID"]) > 0)
		$arLogEvent["SITE_ID"] = $arResult["SITE_ID"];
	else
		$arLogEvent["SITE_ID"] = false;
	
	$arLogEventToAdd = array_merge($arLogEvent, array("EVENT_ID" => "system_friends"));
	CSocNetLogEvents::Add($arLogEventToAdd);
	
	$arLogEventToAdd = array_merge($arLogEvent, array("EVENT_ID" => "system_groups"));
	CSocNetLogEvents::Add($arLogEventToAdd);
}
?>