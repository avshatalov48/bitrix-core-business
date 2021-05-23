<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
	$arParams["TITLE_TEMPLATE"] = (empty($arParams["TITLE_TEMPLATE"]) ? "#TOPIC#" : $arParams["TITLE_TEMPLATE"]);
/********************************************************************
				/Input params
********************************************************************/
if ($arParams["MODE"] == "link"):
	$arResult["rss_link"] = array_reverse($arResult["rss_link"]);
	foreach($arResult["rss_link"] as $key => $val):
		?><noindex><a rel="nofollow" href="<?=$val["link"]?>" title="<?=($arParams["MODE_DATA"] == "topic" ? GetMessage("F_RSS_POST") : GetMessage("F_RSS"))?><?=$val["name"]?><?
			?>" class="forum-rss-<?=$key?>" target="_self"><span class="empty"></span></a></noindex><?
		if(method_exists($APPLICATION, 'addheadstring'))
		{
			$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="'.($arParams["MODE_DATA"] == "topic" ? 
				GetMessage("F_RSS_POST") : GetMessage("F_RSS")).$val["name"].'" href="'.$val["link"].'" />');
		}
	endforeach;
	return "";
endif;

if ($arParams["DESIGN_MODE"] != "Y")
{
	$APPLICATION->RestartBuffer();
}
?><?
if ($arParams["TYPE"] == "rss1"):
?><<??>?xml version="1.0" encoding="<?=$arResult["CHARSET"]?>"?<??>>
<rss version=".92">
	<channel>
		<title><?=$arResult["TITLE"]?></title>
		<link>http://<?=$arResult["SERVER_NAME"]?></link>
		<description><?=$arResult["DESCRIPTION"]?></description>
		<language><?=$arResult["LANGUAGE_ID"]?></language>
		<docs>http://backend.userland.com/rss092</docs>
		<pubDate><?=$arResult["NOW"]?></pubDate>
<?
		foreach ($arResult["DATA"] as $fid => $forum):
			foreach ($forum["TOPICS"] as $tid => $topic):
				foreach ($topic["MESSAGES"] as $mid => $message):
?>
		<item>
			<title><?=$topic["TITLE"]?> <?=GetMessage("F_ON_FORUM")?> <?=$forum["NAME"]?></title>
			<description><![CDATA[<?=$message["TEMPLATE"]?><?
			
			if (empty($message["TEMPLATE"])):
			?><b><a href="<?=$message["URL"]?>"><?=$topic["TITLE"]?></a></b> <?
				if (!empty($topic["DESCRIPTION"])):
				?><i><?=$topic["DESCRIPTION"]?></i> <?
				endif;
			?><?=GetMessage("F_IN_FORUM")?> <a href="<?=$forum["URL"]?>"><?=$forum["~NAME"]?></a>. <br />
			<?=$message["POST_MESSAGE"]?> <br />
			<?
			foreach ($message["FILES"] as $arFile): 
				?><?=$arFile["HTML"]?><br /><?
			endforeach;
			?><i><?=$message["POST_DATE_FORMATED"]?>, <?
				?><?=$message["AUTHOR_NAME"]?><?
			?>.</i><?
			endif;
				?>]]></description>
			<link>http://<?=$arResult["SERVER_NAME"].$message["POST_LINK"]?></link>
		</item>
			<?
				endforeach;
			endforeach;
		endforeach;
?>
	</channel>
</rss>
<?
elseif ($arParams["TYPE"] == "rss2"):
?><<??>?xml version="1.0" encoding="<?=$arResult["CHARSET"]?>"?<??>>
<rss version="2.0">
	<channel>
		<title><?=$arResult["TITLE"]?></title>
		<link>http://<?=$arResult["SERVER_NAME"]?></link>
		<description><?=$arResult["DESCRIPTION"]?></description>
		<language><?=$arResult["LANGUAGE_ID"]?></language>
		<docs>http://backend.userland.com/rss2</docs>
		<pubDate><?=$arResult["NOW"]?></pubDate>
<?
		foreach ($arResult["DATA"] as $fid => $forum):
			foreach ($forum["TOPICS"] as $tid => $topic):
				foreach ($topic["MESSAGES"] as $mid => $message):
?>
		<item>
			<title><?=str_replace(array("#TOPIC#", "#FORUM#"), array($topic["TITLE"], $forum["NAME"]), $arParams["TITLE_TEMPLATE"])?></title>
			<description><![CDATA[<?=$message["TEMPLATE"]?><?
			if (empty($message["TEMPLATE"])):
			?><b><a href="<?=$message["URL"]?>"><?=$topic["TITLE"]?></a></b> <?
				if (!empty($topic["DESCRIPTION"])):
				?><i><?=$topic["DESCRIPTION"]?></i> <?
				endif;
			?><?=GetMessage("F_IN_FORUM")?> <a href="<?=$forum["URL"]?>"><?=$forum["~NAME"]?></a>. <br />
			<?=$message["POST_MESSAGE"]?> <br />
			<?
			foreach ($message["FILES"] as $arFile): 
				?><?=$arFile["HTML"]?><br /><?
			endforeach;
			?><i><?=$message["POST_DATE_FORMATED"]?>, <?
				?><?=$message["AUTHOR_NAME"]?><?
			?>.</i><?
			endif;
				?>]]></description>
			<link><?=$message["URL"]?></link>
			<guid><?=$message["URL"]?></guid>
			<pubDate><?=$message["POST_DATE"]?></pubDate>
			<category><?=$forum["NAME"]?></category>
		</item>
<?
				endforeach;
			endforeach;
		endforeach;
?>
	</channel>
</rss>
<?
elseif  ($arParams["TYPE"] == "atom"):
?><<??>?xml version="1.0" encoding="<?=$arResult["CHARSET"]?>"?<??>>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="<?=$arResult["LANGUAGE_ID"]?>">
	<title type="text"><?=$arResult["TITLE"]?></title>
	<subtitle type="text"><?=$arResult["DESCRIPTION"]?></subtitle>
	<updated><?=$arResult["NOW"]?></updated>
	<id>tag:<?=htmlspecialcharsbx($arResult["SERVER_NAME"]).",".date("Y-m-d:H:i")?></id>
	<link rel="alternate" type="text/html" href="<?=$arResult["URL"]["ALTERNATE"]?>" />
	<link rel="self" type="application/atom+xml" href="<?=$arResult["URL"]["REAL"]?>" />
	<rights>Copyright (c) http://<?=$arResult["SERVER_NAME"]?></rights>
<?
		foreach ($arResult["DATA"] as $fid => $forum):
			foreach ($forum["TOPICS"] as $tid => $topic):
				foreach ($topic["MESSAGES"] as $mid => $message):
?>
	<entry>
		<title type="html"><?=str_replace(array("#TOPIC#", "#FORUM#"), array($topic["TITLE"], $forum["NAME"]), $arParams["TITLE_TEMPLATE"])?></title>
		<link rel="alternate" type="text/html" title="<?=$topic["TITLE"]?>" href="<?=$message["URL"]?>" />
<?
	if ($arParams["MODE"] == "forum"):
?>
		<link rel="related" type="text/html" title="RSS <?=$topic["TITLE"]?>" href="<?=$message["URL_RSS"]?>" />
<?
	endif;
?>
		<updated><?=$message["POST_DATE"]?></updated>
		<id>urn:uuid:<?=$message["UUID"]?></id>
		<content type="html" xml:lang="<?=$arResult["LANGUAGE_ID"]?>" xml:base="<?=$message["URL"]?>">
			<![CDATA[<?=$message["TEMPLATE"]?><?
			if (empty($message["TEMPLATE"])):
			?><b><a href="<?=$message["URL"]?>"><?=$topic["TITLE"]?></a></b> <?
				if (!empty($topic["DESCRIPTION"])):
				?><i><?=$topic["DESCRIPTION"]?></i> <?
				endif;
			?><?=GetMessage("F_IN_FORUM")?> <a href="<?=$forum["URL"]?>"><?=$forum["~NAME"]?></a>. <br />
			<?=$message["POST_MESSAGE"]?> <br />
			<?
			foreach ($message["FILES"] as $arFile): 
				?><?=$arFile["HTML"]?><br /><?
			endforeach;
			?><i><?=$message["POST_DATE_FORMATED"]?>, <?=$message["AUTHOR_NAME"]?>.</i><?
			endif;
			?>]]>
		</content>
		<author>
			<name><?=$message["AUTHOR_NAME"]?></name>
			<uri><?=$message["AUTHOR_URL"]?></uri>
		</author>
	</entry>
<?
				endforeach;
			endforeach;
		endforeach;
?>
</feed>
<?
endif;
?>