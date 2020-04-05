<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:blog.menu",
	"",
	Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
			"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
			"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
			"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
			"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
			"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
			"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
			"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
			"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
		),
	$component
);
?>
<table width="100%"> 
    <tr>
		<td valign="top" width="85%">
			<?
			$APPLICATION->IncludeComponent(
					"bitrix:blog.blog", 
					"", 
					Array(
							"MESSAGE_COUNT"			=> $arResult["MESSAGE_COUNT"],
							"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
							"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
							"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
							"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
							"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
							"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
							"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
							"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
							"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
							"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
							"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
							"YEAR"					=> $arResult["VARIABLES"]["year"],
							"MONTH"					=> $arResult["VARIABLES"]["month"],
							"DAY"					=> $arResult["VARIABLES"]["day"],
							"CATEGORY_ID"			=> $arResult["VARIABLES"]["category"],
							"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
							"CACHE_TIME"			=> $arResult["CACHE_TIME"],
							"CACHE_TIME_LONG"		=> $arResult["CACHE_TIME_LONG"],
							"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
							"SET_TITLE"				=> $arResult["SET_TITLE"],
							"POST_PROPERTY_LIST"	=> $arParams["POST_PROPERTY_LIST"],
							"NAV_TEMPLATE"			=> $arResult["NAV_TEMPLATE"],
							"DATE_TIME_FORMAT"		=> $arResult["DATE_TIME_FORMAT"],
							"USE_SHARE" 			=> $arParams["USE_SHARE"],
							"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"],
							"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"],
							"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"],
							"SHARE_SHORTEN_URL_LOGIN"	=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
							"SHARE_SHORTEN_URL_KEY"		=> $arParams["SHARE_SHORTEN_URL_KEY"],
						),
					$component 
				);
			?>
		</td>
		<td valign="top" width="15%" align="center">
			<?
			$APPLICATION->IncludeComponent(
					"bitrix:blog.info",
					"",
					Array(
							"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
							"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
							"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
							"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
							"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
							"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
							"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
							"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
							"CATEGORY_ID"	=> $arResult["VARIABLES"]["category"],
							"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
							"CACHE_TIME"	=> $arResult["CACHE_TIME"],
							"BLOG_PROPERTY_LIST" =>  $arParams["BLOG_PROPERTY_LIST"],
						),
					$component 
				);
			?>
			<br />
			<?
			$APPLICATION->IncludeComponent(
					"bitrix:blog.calendar",
					"",
					Array(
							"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
							"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
							"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
							"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
							"YEAR"			=> $arResult["VARIABLES"]["year"],
							"MONTH"			=> $arResult["VARIABLES"]["month"],
							"DAY"			=> $arResult["VARIABLES"]["day"],
							"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
							"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						),
					$component 
				);
			?><br /><br />
			<div align="center"><?
			$APPLICATION->IncludeComponent(
					"bitrix:blog.rss.link",
					"vertical",
					Array(
							"RSS1"				=> "Y",
							"RSS2"				=> "Y",
							"ATOM"				=> "Y",
							"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
							"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
							"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
							"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
							"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
							"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
							"GROUP_ID"			=> $arResult["VARIABLES"]["group_id"],
							"MODE"				=> "B",
						),
					$component 
				);
			?>
			</div>
		</td>
	</tr>
 </table>
