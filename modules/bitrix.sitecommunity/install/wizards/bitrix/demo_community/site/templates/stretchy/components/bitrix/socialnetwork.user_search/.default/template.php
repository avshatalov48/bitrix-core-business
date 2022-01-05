<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if ($arResult["ERROR_MESSAGE"] == ''):?>
	<?if (count($arResult["SEARCH_RESULT"]) > 0):?>
		<br />
		<?if ($arResult['CURRENT_VIEW'] == "list"):?>
			<div class="content-list user-list">		
			<?foreach ($arResult["SEARCH_RESULT"] as $v):
			?>
				<div class="content-item">
					<div class="content-avatar">
						<? if ($v["IMAGE_FILE"]["SRC"] <> ''):?>
							<a href="<?= $v["URL"] ?>" style="background: transparent url('<?= $v["IMAGE_FILE"]["SRC"] ?>') no-repeat center center;"></a>
						<? else:?>
							<a href="<?= $v["URL"] ?>"></a>
						<? endif;?>
					</div>
					<div class="content-info">
						<div class="content-title"><?
							$APPLICATION->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $v["ID"],
									"HTML_ID" => "user_search_".$v["ID"],
									"NAME" => htmlspecialcharsback($v["NAME"]),
									"LAST_NAME" => htmlspecialcharsback($v["LAST_NAME"]),
									"SECOND_NAME" => htmlspecialcharsback($v["SECOND_NAME"]),
									"LOGIN" => htmlspecialcharsback($v["LOGIN"]),
									"USE_THUMBNAIL_LIST" => "N",
									"PERSONAL_PHOTO_IMG" => $v["IMAGE_IMG"],
									"PERSONAL_PHOTO_FILE" => $v["IMAGE_FILE"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							
						?>
						</div>
						<div class="content-signature">
						<?if ($v["UserFieldsMain"]["SHOW"] == "Y"):?>
							<?foreach ($v["UserFieldsMain"]["DATA"] as $fieldName => $arUserField):?>
							
							<?if($fieldName == 'DATE_REGISTER'):?><div class="content-date"><?= GetMessage("SONET_IN_SITE") ?> <?=FormatDate("Q", MakeTimeStamp($arUserField["VALUE"]))?></div><?endif;?><?if($fieldName == 'PERSONAL_CITY'):?><div class="content-city"><?endif;?>
								<?if ($arUserField["VALUE"] <> '' && $fieldName != 'LAST_LOGIN' && $fieldName != 'DATE_REGISTER'):?>
									<div><?if($fieldName != 'PERSONAL_CITY'):?><?= $arUserField["NAME"] ?>: <?endif;?><?= $arUserField["VALUE"] ?></div>
								<?endif;?>
							<?if($fieldName == 'PERSONAL_CITY'):?></div><?endif;?>
							<?endforeach;?>
						<?endif;?>
						<?if ($v["UserPropertiesMain"]["SHOW"] == "Y"):?>
							<?foreach ($v["UserPropertiesMain"]["DATA"] as $fieldName => $arUserField):?>
							<?=$fieldName?>
								<?if ((is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0) || (!is_array($arUserField["VALUE"]) && $arUserField["VALUE"] <> '')):?>
									<div>
									<?=$arUserField["EDIT_FORM_LABEL"]?>:
									<?
									$arUserField['SETTINGS']['SECTION_URL'] = $arParams["PATH_TO_CONPANY_DEPARTMENT"];
									$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arUserField),
										null,
										array("HIDE_ICONS"=>"Y")
									);
									?>
									</div>
								<?endif;?>
							<?endforeach;?>
						<?endif;?>
						</div>
						
						<div class="content-rating">
						<?if (
							$arParams["SHOW_RATING"] == 'Y' 
							&& array_key_exists("RATING", $arResult) 
							&& array_key_exists("NAME", $arResult["RATING"]) 
							&& array_key_exists("RATING_".$arParams["RATING_ID"], $v)
						):?>
							<span title="<?=CUtil::JSEscape(htmlspecialcharsbx($arResult["RATING"]["NAME"]))?>: <?=$v["RATING_".$arParams["RATING_ID"]]?>"><?=round($v["RATING_".$arParams["RATING_ID"]])?></span><br>
						<?endif;?>
						</div>
					</div>
					<div style="clear:both;"></div>
						<div class="content-action">
						<?if ($GLOBALS["USER"]->IsAuthorized()):?>
							<div class="bx-user-controls">
							<?if ($v["CAN_ADD2FRIENDS"]):?>
								<div class="bx-user-control">
									<ul>
										<li class="bx-icon bx-icon-addfriend"><a href="<?= $v["ADD_TO_FRIENDS_LINK"] ?>"><?= GetMessage("SONET_C241_T_ADD_FR") ?></a></li>
									</ul>
								</div>
							<?endif;?>
							<?if ($v["CAN_MESSAGE"]):?>
								<div class="bx-user-control">
									<ul>
										<li class="bx-icon bx-icon-message"><a href="<?= $v["MESSAGE_LINK"] ?>" onclick="if (BX.IM) { BXIM.openMessenger(<?=$v["ID"]?>); return false; } else {window.open('<?= $v["MESSAGE_LINK"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false;}"><nobr><?= GetMessage("SONET_C241_T_WRITE") ?></nobr></a></li>
									</ul>
								</div>
							<?endif;?>
							</div>
						<?endif;?>
						</div>
				</div>
				<div class="hr"></div>
			<?endforeach;?>
			</div>
		<?endif;?>

		<?if ($arResult["NAV_STRING"] <> ''):?>
			<p><?=$arResult["NAV_STRING"]?></p>
		<?endif;?>
	
	<?else:?>
		<?if (!$arResult["ShowResults"]):?>
			<?= GetMessage("SONET_C241_T_NOT_FILTERED") ?>
		<?else:?>
			<?= GetMessage("SONET_C241_T_NOT_FOUND") ?>
		<?endif;?>
	<?endif;?>
<?else:?>
	<?= ShowError($arResult["ERROR_MESSAGE"]); ?>
<?endif;?>
<?
foreach($_REQUEST as $key => $value)
{
	if (mb_strtolower(mb_substr($key, 0, 4)) == "flt_")
	{
		unset($_REQUEST[$key]);
		$keyTmp = mb_strtoupper(mb_substr($key, 4));
		$_REQUEST[mb_strtolower("FLTX_".$keyTmp)] = $value;
	}
}
?>