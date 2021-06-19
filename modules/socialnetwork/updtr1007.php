<?
// convert forum

if (CModule::IncludeModule("forum"))
{
	$arLogComments = array();
	$dbLog = CSocNetLog::GetList(array("LOG_DATE" => "ASC"), array("EVENT_ID" => "forum"), false, false, array("ID", "ENTITY_TYPE", "ENTITY_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "SOURCE_ID", "PARAMS", "USER_ID"));
	while($arLog = $dbLog->Fetch())
	{
		if ($arLog["PARAMS"] == "type=M")
			$arLogComments[] = $arLog;
	}

	$CacheTopicLogTmpID = array();
	foreach($arLogComments as $arLogComment)
	{
		if (intval($arLogComment["SOURCE_ID"]) > 0)
		{
			$log_tmp_id = false;

			$arForumMessage = CForumMessage::GetByID($arLogComment["SOURCE_ID"]);
			if ($arForumMessage)
			{
				if (array_key_exists($arForumMessage["TOPIC_ID"], $CacheTopicLogTmpID))
					$log_tmp_id = $CacheTopicLogTmpID[$arForumMessage["TOPIC_ID"]];
				else
				{
					$dbForumMessage = CForumMessage::GetList(
									array("ID" => "ASC"),
									array("TOPIC_ID" => $arForumMessage["TOPIC_ID"]),
									false,
									1
								);
								
					if ($arForumMessageFirst = $dbForumMessage->Fetch())
					{
						$dbLog = CSocNetLog::GetList(
								array("ID" => "DESC"),
								array(
									"EVENT_ID"	=> "forum",
									"SOURCE_ID"	=> $arForumMessageFirst["ID"]
								),
								false,
								array("nTopCount" => 1),
								array("ID", "TMP_ID")
							);
						if ($arLog = $dbLog->Fetch())
						{
							$log_tmp_id = $arLog["TMP_ID"];
							if (intval($log_tmp_id) > 0)
								CSocNetLog::Update($arLog["ID"], array("PARAMS"=>""));
							$CacheTopicLogTmpID[$arForumMessage["TOPIC_ID"]] = $log_tmp_id;
						}
					}
				}
			}

			if (intval($log_tmp_id) > 0)
			{
				$arFields = array(
					"ENTITY_TYPE" 				=> $arLogComment["ENTITY_TYPE"],
					"ENTITY_ID" 				=> $arLogComment["ENTITY_ID"],
					"EVENT_ID" 					=> "forum",
					"LOG_DATE" 					=> $arLogComment["LOG_DATE"],
					"MESSAGE" 					=> $arLogComment["MESSAGE"],
					"TEXT_MESSAGE" 				=> $arLogComment["TEXT_MESSAGE"],
					"URL" 						=> $arLogComment["URL"],
					"MODULE_ID" 				=> false,
					"SOURCE_ID"					=> $arLogComment["SOURCE_ID"],
					"LOG_ID"					=> $log_tmp_id,
					"USER_ID"					=> $arLogComment["USER_ID"]
				);
				CSocNetLogComments::Add($arFields, false, false, false);
				CSocNetLog::Delete($arLogComment["ID"]);
			}
		}
	}
}

// convert blog
if (CModule::IncludeModule("blog"))
{
	$arLogComments = array();
	$dbLog = CSocNetLog::GetList(array("LOG_DATE" => "ASC"), array("EVENT_ID" => "blog_comment"), false, false, array("ID", "ENTITY_TYPE", "ENTITY_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "SOURCE_ID", "USER_ID"));
	while($arLog = $dbLog->Fetch())
		$arLogComments[] = $arLog;

	foreach($arLogComments as $arLogComment)
	{
		if (intval($arLogComment["SOURCE_ID"]) > 0)
		{
			$log_tmp_id = false;
			$arBlogComment = CBlogComment::GetByID($arLogComment["SOURCE_ID"]);
			if ($arBlogComment)
			{
				$dbLog = CSocNetLog::GetList(
						array("ID" => "DESC"),
						array(
							"EVENT_ID"	=> "blog_post",
							"SOURCE_ID"	=> $arBlogComment["POST_ID"]
						),
						false,
						array("nTopCount" => 1),
						array("ID", "TMP_ID")
					);
				if ($arLog = $dbLog->Fetch())
					$log_tmp_id = $arLog["TMP_ID"];
			}

			if (intval($log_tmp_id) > 0)
			{
				$arFields = array(
					"ENTITY_TYPE" 				=> $arLogComment["ENTITY_TYPE"],
					"ENTITY_ID" 				=> $arLogComment["ENTITY_ID"],
					"EVENT_ID" 					=> "blog_comment",
					"LOG_DATE" 					=> $arLogComment["LOG_DATE"],
					"MESSAGE" 					=> $arLogComment["MESSAGE"],
					"TEXT_MESSAGE" 				=> $arLogComment["TEXT_MESSAGE"],
					"URL" 						=> $arLogComment["URL"],
					"MODULE_ID" 				=> false,
					"SOURCE_ID"					=> $arLogComment["SOURCE_ID"],
					"LOG_ID"					=> $log_tmp_id,
					"USER_ID"					=> $arLogComment["USER_ID"]
				);		
				CSocNetLogComments::Add($arFields, false, false, false);
				CSocNetLog::Delete($arLogComment["ID"]);
			}
		}
	}
}

$dbLog = CSocNetLog::GetList(array("LOG_DATE" => "ASC"), array("COMMENTS_COUNT" => false), false, false, array("ID", "ENTITY_TYPE", "ENTITY_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "SOURCE_ID", "USER_ID"));
while($arLog = $dbLog->Fetch())
	CSocNetLog::Update($arLog["ID"], array("LOG_UPDATE" => $arLog["LOG_DATE"]));
	
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
	{
		CSocNetEventUserView::Add(
						array(
							"ENTITY_TYPE" => "N",
							"ENTITY_ID" => 0,
							"EVENT_ID" => "news",
							"USER_ID" => 0,
							"USER_ANONYMOUS" => "N"
						)
		);
		
		CSocNetEventUserView::Add(
						array(
							"ENTITY_TYPE" => "N",
							"ENTITY_ID" => 0,
							"EVENT_ID" => "news_comment",
							"USER_ID" => 0,
							"USER_ANONYMOUS" => "N"
						)
		);
	}
}

$GLOBALS["DB"]->Query("UPDATE b_sonet_log SET LOG_UPDATE = ".$GLOBALS["DB"]->IsNull("(SELECT MAX(LOG_DATE) FROM b_sonet_log_comment LC WHERE LC.LOG_ID=b_sonet_log.TMP_ID)", CDatabase::CurrentTimeFunction()), false, $err_mess.__LINE__);
$GLOBALS["DB"]->Query("UPDATE b_sonet_log SET LOG_UPDATE = LOG_DATE WHERE NOT EXISTS (SELECT LC.ID FROM b_sonet_log_comment LC WHERE LC.LOG_ID = b_sonet_log.TMP_ID)", false, $err_mess.__LINE__);
?>