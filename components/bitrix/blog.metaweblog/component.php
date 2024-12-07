<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
	
$DATA = file_get_contents("php://input");

if($DATA <> '')
{
	$objXML = new CDataXML();
	$objXML->LoadString($DATA);
	$arResult = $objXML->GetArray();

	if(!empty($arResult))
	{
		$params = $arResult["methodCall"]["#"]["params"][0]["#"]["param"];
		$arMethod = explode(".", $arResult["methodCall"]["#"]["methodName"][0]["#"]);
		$methodName = $arMethod[1];
		$methodClass = $arMethod[0];

		if(mb_strtoupper($methodClass) == mb_strtoupper("blogger") || mb_strtoupper($methodClass) == mb_strtoupper("metaWeblog"))
		{
			switch (mb_strtoupper($methodName))
			{
				case mb_strtoupper("getUsersBlogs"):
					$result = CBlogMetaWeblog::GetUsersBlogs($params, Array("PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"]));
					break;
				case mb_strtoupper("getCategories"):
					$result = CBlogMetaWeblog::GetCategories($params);
					break;
				case mb_strtoupper("getRecentPosts"):
					$result = CBlogMetaWeblog::GetRecentPosts($params, Array("PATH_TO_POST" => $arParams["PATH_TO_POST"]));
					break;
				case mb_strtoupper("newMediaObject"):
					$result = CBlogMetaWeblog::NewMediaObject($params);
					break;
				case mb_strtoupper("newPost"):
					$result = CBlogMetaWeblog::NewPost($params);
					break;
				case mb_strtoupper("editPost"):
					$result = CBlogMetaWeblog::EditPost($params);
					break;
				case mb_strtoupper("getPost"):
					$result = CBlogMetaWeblog::GetPost($params, Array("PATH_TO_POST" => $arParams["PATH_TO_POST"]));
					break;
				case mb_strtoupper("deletePost"):
					$result = CBlogMetaWeblog::DeletePost($params);
					break;
				case mb_strtoupper("getUserInfo"):
					$result = CBlogMetaWeblog::GetUserInfo($params);
					break;
				default:
					$result = '<fault>
						<value>
							<struct>
								<member>
									<name>faultCode</name>
									<value><int>1</int></value>
								</member>
								<member>
									<name>faultString</name>
									<value><string>Unknown method name.</string></value>
								</member>
							</struct>
						</value>
					</fault>';
			}
		}
		else
		{
				$result = '<fault>
					<value>
						<struct>
							<member>
								<name>faultCode</name>
								<value><int>2</int></value>
							</member>
							<member>
								<name>faultString</name>
								<value><string>Unknown method class.</string></value>
							</member>
						</struct>
					</value>
				</fault>';
		}
	}
	else
		$result = '<fault>
				<value>
					<struct>
						<member>
							<name>faultCode</name>
							<value><int>2</int></value>
						</member>
						<member>
							<name>faultString</name>
							<value><string>Empty request.</string></value>
						</member>
					</struct>
				</value>
			</fault>';

}
else
	$result = '<fault>
			<value>
				<struct>
					<member>
						<name>faultCode</name>
						<value><int>3</int></value>
					</member>
					<member>
						<name>faultString</name>
						<value><string>Empty request.</string></value>
					</member>
				</struct>
			</value>
		</fault>';

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
if(!$bDesignMode)
{
	$content = '<?xml version="1.0" encoding="'.LANG_CHARSET.'"?>
	<methodResponse>'.$result.'</methodResponse>';

	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	header("Content-type: text/xml; charset=".LANG_CHARSET);
	header("Content-Length: ".mb_strlen($content));
	echo $content;
	die();
}
?>