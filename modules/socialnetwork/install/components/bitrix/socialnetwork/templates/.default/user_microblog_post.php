<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(strlen($arResult["VARIABLES"]["post_id"]) > 0)
{
	CModule::IncludeModule("blog");
	$postID = trim($arResult["VARIABLES"]["post_id"]);
	if(!is_numeric($postID) || strlen(IntVal($postID)) != strlen($postID))
	{
		$postID = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($postID));
		$arFilter = array("CODE" => $postID);
	}
	else
		$arFilter = array("ID" => IntVal($postID));

	if(strlen($arResult["PATH_TO_USER_BLOG_POST"]) <= 0)	
		$arResult["PATH_TO_USER_BLOG_POST"] = "/company/personal/user/#user_id#/blog/#post_id#/";

	$dbPost = CBlogPost::GetList(array(), $arFilter, false, false, array("ID", "AUTHOR_ID"));
	if($arPost = $dbPost->Fetch())
	{
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_BLOG_POST"], array("post_id"=>$arPost["ID"], "user_id" => $arPost["AUTHOR_ID"])));
		die();
	}
}
?>