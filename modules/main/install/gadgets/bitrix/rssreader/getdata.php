<?php

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/getdata.php");

if (!check_bitrix_sessid() || !isset($_REQUEST['id']))
{
	CMain::FinalActions();
}

include_once(__DIR__.'/include.php');

$id = $_REQUEST['id'];
$idAttr = preg_replace('/[^a-z0-9\\-_]/i', '_', $id);

$arGadgetParams = BXGadget::getGadgetSettings($id, $_REQUEST['params'] ?? []);

$arGadgetParams["CNT"] = intval($arGadgetParams["CNT"] ?? 0);
if($arGadgetParams["CNT"] > 50)
{
	$arGadgetParams["CNT"] = 0;
}

$cache = new CPageCache();
if(
	isset($arGadgetParams["CACHE_TIME"])
	&& $arGadgetParams["CACHE_TIME"] > 0
	&& !$cache->StartDataCache($arGadgetParams["CACHE_TIME"], 'c'.$arGadgetParams["RSS_URL"].'-'.$arGadgetParams["CNT"], "gdrss")
)
{
	CMain::FinalActions();
}

if (empty($arGadgetParams["RSS_URL"]))
{
	?><div class="gdrsserror"><?=GetMessage("GD_RSS_READER_NEW_RSS")?></div><?php

	$cache->EndDataCache();
	CMain::FinalActions();
}

session_write_close();

$rss = gdGetRss($arGadgetParams["RSS_URL"], 0, isset($arGadgetParams["IS_HTML"]) && $arGadgetParams["IS_HTML"] == "Y");
if($rss)
{
	$rss->title = strip_tags($rss->title);

	?><script>
	function ShowHide<?=$idAttr?>(id)
	{
		var d = document.getElementById(id);
		if(d.style.display == 'none')
			d.style.display = 'block';
		else
			d.style.display = 'none';
	}
	</script>
	<div class="gdrsstitle"><?php
	if(isset($arGadgetParams["SHOW_URL"]) && $arGadgetParams["SHOW_URL"]=="Y" && preg_match("'^(http://|https://|ftp://)'i", $rss->link))
	{
		?><a href="<?=htmlspecialcharsbx($rss->link)?>"><?=htmlspecialcharsEx($rss->title)?></a><?php
	}
	else
	{
		?><?=htmlspecialcharsEx($rss->title)?><?php
	}
	?></div>
	<div class="gdrssitems"><?php
	$cnt = 0;

	if (isset($arGadgetParams["IS_HTML"]) && $arGadgetParams["IS_HTML"] == "Y")
	{
		$sanitizer = new CBXSanitizer();
		$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_HIGH);
	}

	foreach($rss->items as $item)
	{
		$cnt++;
		if (
			isset($arGadgetParams["CNT"])
			&& $arGadgetParams["CNT"] > 0
			&& $arGadgetParams["CNT"] < $cnt
		)
		{
			break;
		}

		$item["DESCRIPTION"] = (isset($arGadgetParams["IS_HTML"]) && $arGadgetParams["IS_HTML"] == "Y" ? $sanitizer->SanitizeHtml($item["DESCRIPTION"]) : strip_tags($item["DESCRIPTION"]));
		$item["TITLE"] = strip_tags($item["TITLE"] ?? '');

		?><div class="gdrssitem">
			<div class="gdrssitemtitle">&raquo; <a href="javascript:void(0)" onclick="ShowHide<?=$idAttr?>('z<?=$cnt.md5($item["TITLE"])?><?=$idAttr?>')"><?=htmlspecialcharsEx($item["TITLE"])?></a></div>
			<div class="gdrssitemdetail" id="z<?=$cnt.md5($item["TITLE"])?><?=$idAttr?>" style="display:none">
				<div class="gdrssitemdate"><?=htmlspecialcharsEx($item["PUBDATE"])?></div>
				<div class="gdrssitemdesc"><?=$item["DESCRIPTION"]?> <?php if(isset($arGadgetParams["SHOW_URL"]) && $arGadgetParams["SHOW_URL"]=="Y" && preg_match("'^(http://|https://|ftp://)'i", $item["LINK"])):?><a href="<?=htmlspecialcharsbx($item["LINK"])?>"><?echo GetMessage("GD_RSS_READER_RSS_MORE")?></a><?endif?></div>
			</div>
		</div><?php
	}
	?></div><?php
}
else
{
	?><div class="gdrsserror"><?=GetMessage("GD_RSS_READER_RSS_ERROR")?></div><?php
}

$cache->EndDataCache();
CMain::FinalActions();

