<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/xml.php');

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/probki/styles.css');

if($arGadgetParams["CITY"]!='')
	$url = 'yasoft=barff&region='.mb_substr($arGadgetParams["CITY"], 1).'&ts='.time();
else
	$url = 'ts='.time();

$cache = new CPageCache();
if($arGadgetParams["CACHE_TIME"]>0 && !$cache->StartDataCache($arGadgetParams["CACHE_TIME"], 'c'.$arGadgetParams["CITY"], "gdprobki"))
	return;

$http = new \Bitrix\Main\Web\HttpClient();
$http->setTimeout(10);
$res = $http->get("https://export.yandex.ru/bar/reginfo.xml?".$url);

$res = str_replace("\xE2\x88\x92", "-", $res);
$res = $APPLICATION->ConvertCharset($res, 'UTF-8', SITE_CHARSET);

$xml = new CDataXML();
$xml->LoadString($res);

$node = $xml->SelectNodes('/info/traffic/title');
?>
<h3><?=$node->content?></h3>
<table width="90%"><tr>
<td width="80%" nowrap>
<?$node = $xml->SelectNodes('/info/traffic/region/hint');?>
<span class="gdtrafic"><?=$node->content?></span><br>
<span class="gdtrafinfo">
<?$node = $xml->SelectNodes('/info/traffic/region/length');?>
Протяженность: <?=$node->content?> м<br>
<?$node = $xml->SelectNodes('/info/traffic/region/time');?>
Последнее обновление: <?=$node->content?>

</span>
</td>
<?
$node = $xml->SelectNodes('/info/traffic/region/level');
$t = intval($node->content);
?>
<td nowrap="yes" width="20%"><span class="traf<?=intval(($t+1)/2)?>"><?=$t?></span></td>
</tr>
</table>
<?if($arGadgetParams["SHOW_URL"]=="Y"):?>
<br />
<?$node = $xml->SelectNodes('/info/traffic/region/url');?>
<a href="<?=htmlspecialcharsbx($node->content)?>">Подробнее</a> <a href="<?=htmlspecialcharsbx($node->content)?>"><img width="7" height="7" border="0" src="/bitrix/components/bitrix/desktop/images/arrows.gif" /></a>
<br />
<?endif?>
<?$cache->EndDataCache();?>
