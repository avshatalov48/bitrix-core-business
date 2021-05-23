<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="idea-posts-content-light">
<?
// GetMessage("IDEA_STATUS_NEW"); GetMessage("IDEA_STATUS_PROCESSING"); GetMessage("IDEA_STATUS_COMPLETED");
if(count($arResult["POST"])>0)
{
$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
	$i = 0;
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?foreach($arResult["POST"] as $CurPost)
	{
		if($arParams["MESSAGE_COUNT"] <= $i) break;
		$status = GetMessage("IDEA_STATUS_".ToUpper($arStatusList[$CurPost["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["XML_ID"]));
		if($status == '')
			$status = $arStatusList[$CurPost["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["VALUE"];
		?>
	<?if($i++>0):?><tr><td colspan="3"><div class="idea-light-delimiter"></div></td></tr><?endif;?>
	<tr>
		<td width="1" style="overflow: visible; white-space: nowrap;">
			<?if($arParams["SHOW_RATING"] == "Y"):?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:rating.vote", $arParams['RATING_TEMPLATE'],
				Array(
					"VOTE_AVAILABLE" => $CurPost["DISABLE_VOTE"]?"N":"Y",
					"ENTITY_TYPE_ID" => "BLOG_POST",
					"ENTITY_ID" => $CurPost["ID"],
					"OWNER_ID" => $CurPost["arUser"]["ID"],
					"USER_VOTE" => $arResult["RATING"][$CurPost["ID"]]["USER_VOTE"],
					"USER_HAS_VOTED" => $arResult["RATING"][$CurPost["ID"]]["USER_HAS_VOTED"],
					"TOTAL_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VOTES"],
					"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_POSITIVE_VOTES"],
					"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_NEGATIVE_VOTES"],
					"TOTAL_VALUE" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VALUE"],
				),
				false,
				array("HIDE_ICONS" => "Y")
			);?>
			<?endif;?>
		</td>
		<td width="*">
			<div class="idea-title idea-title-<?=$arParams['RATING_TEMPLATE']?>"><a href="<?=$CurPost["urlToPost"]?>" target="_blank" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></div>
		</td>
		<td style="white-space: nowrap;">
			<div class="bx-idea-condition-description status-color-<?=ToLower($arStatusList[$CurPost["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["XML_ID"]);?>">
				<div><?=$status?></div>
				<div class="idea-owner"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></div>
			</div>
		</td>
	</tr>
	<?}?>
</table>
<?}?>

</div>