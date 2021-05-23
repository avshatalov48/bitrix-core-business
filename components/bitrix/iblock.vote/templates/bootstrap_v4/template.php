<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

CJSCore::Init(array("ajax"));

//Let's determine what value to display: rating or average ?
if ($arParams['DISPLAY_AS_RATING'] === 'vote_avg')
{
	if (
		!empty($arResult['PROPERTIES']['vote_count']['VALUE'])
		&& is_numeric($arResult['PROPERTIES']['vote_sum']['VALUE'])
		&& is_numeric($arResult['PROPERTIES']['vote_count']['VALUE'])
	)
	{
		$DISPLAY_VALUE = round($arResult['PROPERTIES']['vote_sum']['VALUE'] / $arResult['PROPERTIES']['vote_count']['VALUE'], 2);
	}
	else
	{
		$DISPLAY_VALUE = 0;
	}
}
else
{
	$DISPLAY_VALUE = $arResult["PROPERTIES"]["rating"]["VALUE"];
}
$voteContainerId = 'vote_'.$arResult["ID"];
?>
<div class="bx-rating text-primary" id="<?echo $voteContainerId?>">
	<?
	$onclick = "JCFlatVote.do_vote(this, '".$voteContainerId."', ".$arResult["AJAX_PARAMS"].")";
	foreach ($arResult["VOTE_NAMES"] as $i => $name)
	{
		if ($DISPLAY_VALUE && round($DISPLAY_VALUE) > $i)
			$ratingIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15"><path fill="#EEAD36" class="bx-rating-icon" fill-rule="evenodd" d="M8.00880417,12.3428359 L3.90614964,14.499729 C3.75949626,14.5768293 3.57810811,14.5204453 3.50100786,14.3737919 C3.47030614,14.3153938 3.45971174,14.2485034 3.47086479,14.1834761 L4.25440208,9.61510311 L0.935284853,6.37976343 C0.816639774,6.26411306 0.814212082,6.07417907 0.929862451,5.95553399 C0.975915034,5.9082889 1.03625776,5.87754275 1.10154887,5.86805539 L5.68845607,5.20153876 L7.73978333,1.04509575 C7.81311002,0.896519643 7.99299778,0.835517941 8.14157389,0.908844632 C8.20073767,0.938043705 8.24862593,0.985931963 8.277825,1.04509575 L10.3291523,5.20153876 L14.9160595,5.86805539 C15.0800229,5.89188068 15.1936274,6.04411354 15.1698021,6.20807701 C15.1603147,6.27336812 15.1295686,6.33371084 15.0823235,6.37976343 L11.7632062,9.61510311 L12.5467435,14.1834761 C12.5747518,14.346777 12.4650755,14.5018638 12.3017746,14.5298721 C12.2367473,14.5410251 12.1698568,14.5304307 12.1114587,14.499729 L8.00880417,12.3428359 Z"/></svg>';
		else
			$ratingIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"><path fill="#EEAD36" class="bx-rating-icon" fill-rule="evenodd" d="M10.0344724,9.32416986 L12.893474,6.53733206 L8.942431,5.9632119 L7.17547083,2.38295955 L5.40851067,5.9632119 L1.45746762,6.53733206 L4.31646923,9.32416986 L3.6415505,13.259248 L7.17547083,11.4013561 L10.7093912,13.259248 L10.0344724,9.32416986 Z M7.17547083,12.3428359 L3.1325987,14.4682996 C2.96965051,14.5539665 2.76810812,14.4913177 2.68244118,14.3283695 C2.64832816,14.2634826 2.6365566,14.1891599 2.64894888,14.1169074 L3.42106875,9.61510311 L0.150316491,6.42690762 C0.0184886296,6.29840722 0.0157911941,6.08736946 0.1442916,5.9555416 C0.195461136,5.90304705 0.262508607,5.86888466 0.335054285,5.85834316 L4.85512274,5.20153876 L6.8765588,1.10566193 C6.9580329,0.94057737 7.15790818,0.872797703 7.32299274,0.954271802 C7.38873028,0.986715215 7.44193945,1.03992439 7.47438286,1.10566193 L9.49581893,5.20153876 L14.0158874,5.85834316 C14.198069,5.8848157 14.3242962,6.05396331 14.2978236,6.23614494 C14.2872821,6.30869062 14.2531197,6.37573809 14.2006252,6.42690762 L10.9298729,9.61510311 L11.7019928,14.1169074 C11.7331131,14.2983529 11.6112505,14.4706715 11.429805,14.5017919 C11.3575525,14.5141841 11.2832298,14.5024126 11.218343,14.4682996 L7.17547083,12.3428359 Z"/></svg>';

		$itemContainerId = $voteContainerId.'_'.$i;
		?><span
			class="bx-rating-icon-container"
			id="<?echo $itemContainerId?>"
			title="<?echo $name?>"
			<?if (!$arResult["VOTED"] && $arParams["READ_ONLY"]!=="Y"):?>
				onmouseover="JCFlatVote.trace_vote(this, true);"
				onmouseout="JCFlatVote.trace_vote(this, false)"
				onclick="<?echo htmlspecialcharsbx($onclick);?>"
			<?endif;?>
		><?echo $ratingIcon?></span><?
	}
	if ($arParams["SHOW_RATING"] == "Y"):?>
		(<?echo $DISPLAY_VALUE?>)
	<?endif;
?>
</div>