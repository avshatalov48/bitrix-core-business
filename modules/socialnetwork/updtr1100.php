<?
if (CModule::IncludeModule("blog"))
{
	$arComments = array();
	$dbLogComment = CSocNetLogComments::GetList(array("LOG_DATE" => "ASC"), array("EVENT_ID" => "blog_comment_micro", "SOURCE_ID" => false), false, false, array("ID", "LOG_SOURCE_ID", "USER_ID", "TEXT_MESSAGE", "LOG_DATE"));
	while($arLogComment = $dbLogComment->Fetch())
	{
		$arPost = CBlogPost::GetByID($arLogComment["LOG_SOURCE_ID"]);
		if ($arPost)
		{
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);

			$arFieldsComment = Array(
				"POST_ID" => $arPost["ID"],
				"BLOG_ID" => $arBlog["ID"],
				"POST_TEXT" => $arLogComment["TEXT_MESSAGE"],
				"DATE_CREATE" => $arLogComment["LOG_DATE"],
				"AUTHOR_ID" => $arLogComment["USER_ID"],
				"PARENT_ID" => false
			);
					
			$commentId = CBlogComment::Add($arFieldsComment);
			$arComments[$arLogComment["ID"]] = $commentId;
		}
	}

	foreach($arComments as $log_comment_id => $blog_comment_id)
		CSocNetLogComments::Update($log_comment_id, array("SOURCE_ID" => $blog_comment_id));

}
?>