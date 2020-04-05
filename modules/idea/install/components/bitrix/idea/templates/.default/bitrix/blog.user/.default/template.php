<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
$TitleName = trim($arResult["User"]["ALIAS"]);
if($TitleName == "")
{
	$TitleName = CUser::FormatName($arParams["NAME_TEMPLATE"],
		array("NAME"		=> $arResult["User"]["NAME"],
			"LAST_NAME"	 => $arResult["User"]["LAST_NAME"],
			"SECOND_NAME"   => $arResult["User"]["SECOND_NAME"],
			"LOGIN"		 => $arResult["User"]["LOGIN"]), true);
}
if($arParams["SET_NAV_CHAIN"] == "Y")
{
	$APPLICATION->AddChainItem(GetMessage("IDEA_USER_INFO_NAV_TITLE", array("#NAME#" => $TitleName)));
	$APPLICATION->SetTitle(GetMessage("IDEA_USER_INFO_NAV_TITLE", array("#NAME#" => $TitleName)));
}
?>
<a href='<?=$arResult["USER_IDEA_LINK"]?>'><?=GetMessage("IDEA_USER_INFO_LINK_TITLE")?></a>

<h4 class="bx-idea-user-desc-contact"><?=htmlspecialcharsback($TitleName)?></h4>
<hr style="background: #E5E5E5; border:none; height:1px; line-height:1px; " />
	<?=$arResult["User"]["AVATAR_IMG"]?>
	<table width="100%" cellspacing="2" cellpadding="3"><?
			foreach ($arResult["DISPLAY_FIELDS"]['FIELDS_MAIN_DATA'] as $fieldName=>$Title):
					if (StrLen($arResult["User"][$fieldName]) > 0):
							?><tr valign="top">
									<td width="40%"><?=$Title?>:</td>
									<td width="60%"><?=$arResult["User"][$fieldName]?></td>
							</tr><?
					endif;
			endforeach;
?>
	</table>

<h4 class="bx-idea-user-desc-contact"><?=GetMessage("GD_SONET_USER_DESC_CONTACT_TITLE") ?></h4>
<hr style="background: #E5E5E5; border:none; height:1px; line-height:1px; " />
	<table width="100%" cellspacing="2" cellpadding="3"><?
	foreach ($arResult["DISPLAY_FIELDS"]['FIELDS_CONTACT_DATA'] as $fieldName=>$Title):
		if (StrLen($arResult["User"][$fieldName]) > 0):
				?><tr valign="top">
						<td width="40%"><?=$Title?>:</td>
						<td width="60%"><?=$arResult["User"][$fieldName]?></td>
				</tr><?
		endif;
	endforeach;
	?></table>
<h4 class="bx-idea-user-desc-contact"><?=GetMessage("GD_SONET_USER_DESC_PERSONAL_TITLE") ?></h4>
<hr style="background: #E5E5E5; border:none; height:1px; line-height:1px; "/>
	<table width="100%" cellspacing="2" cellpadding="3"><?
			foreach ($arResult["DISPLAY_FIELDS"]['FIELDS_PERSONAL_DATA'] as $fieldName=>$Title):
					if (StrLen($arResult["User"][$fieldName]) > 0):
							?><tr valign="top">
									<td width="40%"><?=$Title?>:</td>
									<td width="60%"><?=$arResult["User"][$fieldName]?></td>
							</tr><?
					endif;
			endforeach;
	?></table>