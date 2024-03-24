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

if(toUpper(LANG_CHARSET) != "UTF-8")
	$DATA = $APPLICATION->ConvertCharset($DATA, "UTF-8", LANG_CHARSET);

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

		if(ToUpper($methodClass) == ToUpper("blogger") || ToUpper($methodClass) == ToUpper("metaWeblog"))
		{
			switch (ToUpper($methodName)) 
			{
				case ToUpper("getUsersBlogs"):
					$result = CBlogMetaWeblog::GetUsersBlogs($params, Array("PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"]));
					break;
				case ToUpper("getCategories"):
					$result = CBlogMetaWeblog::GetCategories($params);
					break;
				case ToUpper("getRecentPosts"):
					$result = CBlogMetaWeblog::GetRecentPosts($params, Array("PATH_TO_POST" => $arParams["PATH_TO_POST"]));
					break;
				case ToUpper("newMediaObject"):
					$result = CBlogMetaWeblog::NewMediaObject($params);
					break;
				case ToUpper("newPost"):
					$result = CBlogMetaWeblog::NewPost($params);
					break;
				case ToUpper("editPost"):
					$result = CBlogMetaWeblog::EditPost($params);
					break;
				case ToUpper("getPost"):
					$result = CBlogMetaWeblog::GetPost($params, Array("PATH_TO_POST" => $arParams["PATH_TO_POST"]));
					break;
				case ToUpper("deletePost"):
					$result = CBlogMetaWeblog::DeletePost($params);
					break;
				case ToUpper("getUserInfo"):
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