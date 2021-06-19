<?
$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/install/events.php", $lid);

	$et = new CEventType;

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_BLOG_MESSAGE",
		"NAME" => GetMessage("NEW_BLOG_MESSAGE_NAME"),
		"DESCRIPTION" => GetMessage("NEW_BLOG_MESSAGE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_BLOG_COMMENT",
		"NAME" => GetMessage("NEW_BLOG_COMMENT_NAME"),
		"DESCRIPTION" => GetMessage("NEW_BLOG_COMMENT_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_BLOG_COMMENT2COMMENT",
		"NAME" => GetMessage("NEW_BLOG_COMMENT2COMMENT_NAME"),
		"DESCRIPTION" => GetMessage("NEW_BLOG_COMMENT2COMMENT_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_BLOG_COMMENT_WITHOUT_TITLE",
		"NAME" => GetMessage("NEW_BLOG_COMMENT_WITHOUT_TITLE_NAME"),
		"DESCRIPTION" => GetMessage("NEW_BLOG_COMMENT_WITHOUT_TITLE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE",
		"NAME" => GetMessage("NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE_NAME"),
		"DESCRIPTION" => GetMessage("NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_YOUR_BLOG_TO_USER",
		"NAME" => GetMessage("BLOG_YOUR_BLOG_TO_USER_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_YOUR_BLOG_TO_USER_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_YOU_TO_BLOG",
		"NAME" => GetMessage("BLOG_YOU_TO_BLOG_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_YOU_TO_BLOG_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_BLOG_TO_YOU",
		"NAME" => GetMessage("BLOG_BLOG_TO_YOU_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_BLOG_TO_YOU_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_USER_TO_YOUR_BLOG",
		"NAME" => GetMessage("BLOG_USER_TO_YOUR_BLOG_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_USER_TO_YOUR_BLOG_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_SONET_NEW_POST",
		"NAME" => GetMessage("BLOG_SONET_NEW_POST_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_SONET_NEW_POST_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_SONET_NEW_COMMENT",
		"NAME" => GetMessage("BLOG_SONET_NEW_COMMENT_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_SONET_NEW_COMMENT_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_SONET_POST_SHARE",
		"NAME" => GetMessage("BLOG_SONET_POST_SHARE_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_SONET_POST_SHARE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BLOG_POST_BROADCAST",
		"NAME" => GetMessage("BLOG_POST_BROADCAST_NAME"),
		"DESCRIPTION" => GetMessage("BLOG_POST_BROADCAST_DESC"),
	));

	$arSites = array();
	$sites = CSite::GetList("", "", Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{
		$emess = new CEventMessage;

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "NEW_BLOG_MESSAGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_BLOG_MESSAGE_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_BLOG_MESSAGE_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "NEW_BLOG_COMMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_BLOG_COMMENT_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_BLOG_COMMENT_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "NEW_BLOG_COMMENT2COMMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_BLOG_COMMENT2COMMENT_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_BLOG_COMMENT2COMMENT_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "NEW_BLOG_COMMENT_WITHOUT_TITLE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_BLOG_COMMENT_WITHOUT_TITLE_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_BLOG_COMMENT_WITHOUT_TITLE_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_YOUR_BLOG_TO_USER",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("BLOG_YOUR_BLOG_TO_USER_SUBJECT"),
			"MESSAGE" => GetMessage("BLOG_YOUR_BLOG_TO_USER_MESSAGE"),
			"BODY_TYPE" => "text",
		));		

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_YOU_TO_BLOG",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("BLOG_YOU_TO_BLOG_SUBJECT"),
			"MESSAGE" => GetMessage("BLOG_YOU_TO_BLOG_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_BLOG_TO_YOU",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("BLOG_BLOG_TO_YOU_SUBJECT"),
			"MESSAGE" => GetMessage("BLOG_BLOG_TO_YOU_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_USER_TO_YOUR_BLOG",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("BLOG_USER_TO_YOUR_BLOG_SUBJECT"),
			"MESSAGE" => GetMessage("BLOG_USER_TO_YOUR_BLOG_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_SONET_NEW_POST",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => "#POST_TITLE#",
			"MESSAGE" => "<?EventMessageThemeCompiler::includeComponent(\"bitrix:socialnetwork.blog.post.mail\",\"\",Array(\"EMAIL_TO\" => \"{#EMAIL_TO#}\",\"RECIPIENT_ID\" => \"{#RECIPIENT_ID#}\",\"POST_ID\" => \"{#POST_ID#}\",\"URL\" => \"{#URL#}\"));?>",
			"BODY_TYPE" => "html",
			"SITE_TEMPLATE_ID" => "mail_user"
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_SONET_NEW_COMMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => "Re: #POST_TITLE#",
			"MESSAGE" => "<?EventMessageThemeCompiler::includeComponent(\"bitrix:socialnetwork.blog.post.comment.mail\",\"\",Array(\"COMMENT_ID\" => \"{#COMMENT_ID#}\",\"RECIPIENT_ID\" => \"{#RECIPIENT_ID#}\",\"EMAIL_TO\" => \"{#EMAIL_TO#}\",\"POST_ID\" => \"{#POST_ID#}\",\"URL\" => \"{#URL#}\"));?>",
			"BODY_TYPE" => "html",
			"SITE_TEMPLATE_ID" => "mail_user"
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_SONET_POST_SHARE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => "#POST_TITLE#",
			"MESSAGE" => "<?EventMessageThemeCompiler::includeComponent(\"bitrix:socialnetwork.blog.post_share.mail\",\"\",Array(\"EMAIL_TO\" => \"{#EMAIL_TO#}\",\"RECIPIENT_ID\" => \"{#RECIPIENT_ID#}\",\"POST_ID\" => \"{#POST_ID#}\",\"URL\" => \"{#URL#}\"));?>",
			"BODY_TYPE" => "html",
			"SITE_TEMPLATE_ID" => "mail_user"
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BLOG_POST_BROADCAST",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("BLOG_POST_BROADCAST_SUBJECT"),
			"MESSAGE" => GetMessage("BLOG_POST_BROADCAST_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>
