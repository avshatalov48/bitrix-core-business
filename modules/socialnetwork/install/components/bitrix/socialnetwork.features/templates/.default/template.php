<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI;

UI\Extension::load("ui.buttons");
UI\Extension::load("ui.alerts");
UI\Extension::load("socialnetwork.common");

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"]) > 0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if (
		strlen($arResult["ErrorMessage"]) > 0
		&& $arResult["ShowForm"] != "Input"
	)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?><script>
			BX.ready(function() {
				BX.BXSF.init({
					iframe: <?=$arResult["IS_IFRAME"] ? 'true' : 'false'?>,
					errorBlockName: 'sonet_features_error_block'
				});
			});
			BX.message({
				SONET_C4_T_ERROR: '<?=GetMessageJS('SONET_C4_T_ERROR')?>'
			});
		</script>

		<div id="sonet_features_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(strlen($arResult["ErrorMessage"]) > 0 ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?

		?><form method="post" name="sonet-features-form" id="sonet-features-form" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<div class="sn-features-wrap"><?

				$hasActiveFeatures = false;

				if (
					$arResult["ENTITY_TYPE"] == "G"
					&& !empty($arResult["Group"])
					&& $arResult["Group"]["CLOSED"] != "Y"
				)
				{
					?><div class="sn-features-row">
						<h4 class="sn-features-title"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? 'SONET_C4_INVITE_TITLE_PROJECT' : 'SONET_C4_INVITE_TITLE')?></h4>
						<div class="sn-features-input-box">
							<div class="sn-features-caption"><?=Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? 'SONET_C4_INVITE_OPERATION_PROJECT' : 'SONET_C4_INVITE_OPERATION')?></div>
							<select name="GROUP_INITIATE_PERMS" id="GROUP_INITIATE_PERMS" class="sn-features-select"><?
								foreach ($arResult["InitiatePermsList"] as $key => $value)
								{
									?><option id="GROUP_INITIATE_PERMS_OPTION_<?=$key?>" value="<?=$key?>"<?=($key == $arResult["Group"]["INITIATE_PERMS"]) ? " selected" : "" ?>><?=$value?></option><?
								}
							?></select>
						</div>
					</div><?
				}

				foreach ($arResult["Features"] as $feature => $arFeature)
				{
					if (
						$arResult["ENTITY_TYPE"] == "G"
						&& !isset($arFeature["note"])
						&& (
							empty($arFeature["Operations"])
							|| (
								isset($arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"])
								&& $arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"]
							)
						)
					)
					{
						?><input type="hidden" name="<?=$feature?>_active" value="<?=($arFeature["Active"] ? "Y" : "") ?>" /><?
					}
					elseif (
						$arFeature["Active"]
						|| $arResult["ENTITY_TYPE"] == "U"
					)
					{
						$hasActiveFeatures = true;

						$featureName = (
								array_key_exists("title", $arResult["arSocNetFeaturesSettings"][$feature])
								&& strlen($arResult["arSocNetFeaturesSettings"][$feature]["title"]) > 0
									? $arResult["arSocNetFeaturesSettings"][$feature]["title"]
									: Loc::getMessage("SONET_FEATURES_".$feature)
						);

						?><div class="sn-features-row">
							<h4 class="sn-features-title"><?=$featureName?></h4><?

							if (
								$arResult["ENTITY_TYPE"] == "U"
								&& !(
									$feature == "blog"
									&& $arParams["PAGE_ID"] != "group_features"
								)
							)
							{
								?><script>

									BX.message({
										sonetF_<?=$feature?>_on: '<?=CUtil::JSEscape(str_replace(
												"#NAME#",
												$featureName,
												Loc::getMessage("SONET_C4_FUNC_TITLE_ON")
										))?>',
										sonetF_<?=$feature?>_off: '<?=CUtil::JSEscape(str_replace(
												"#NAME#",
												$featureName,
												Loc::getMessage("SONET_C4_FUNC_TITLE_OFF")))?>'
									});

								</script>
								<div class="sn-features-input-box">
									<div class="settings-right-enable-label-wrap">
										<label for="<?=$feature?>_active_id" style="width:100%" id="<?=$feature?>_lbl"><?=str_replace(
											"#NAME#",
											$featureName,
											Loc::getMessage("SONET_C4_FUNC_TITLE_".($arFeature["Active"] ? "ON" : "OFF"))
										)?></label>:
									</div>
									<div class="settings-block-enable-checkbox-wrap">
										<input class="settings-right-enable-checkbox" bx-feature="<?=$feature?>" type="checkbox" id="<?=$feature?>_active_id" name="<?=$feature?>_active" value="Y"<?=($arFeature["Active"] ? " checked" : "") ?>>
									</div>
								</div><?
							}
							else
							{
								?><input type="hidden" name="<?=$feature?>_active" value="Y" /><?
							}

							$displayValue = ($arFeature["Active"] ? 'block' : 'none');

							?><div id="<?=$feature?>_body" style="display: <?=$displayValue?>"><?
								if (isset($arFeature["note"]))
								{
									?><div class="settings-blocks-note"><?=htmlspecialcharsbx($arFeature['note'])?></div><?
								}

								if (
									!array_key_exists("hide_operations_settings", $arResult["arSocNetFeaturesSettings"][$feature])
									|| !$arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"]
								)
								{
									foreach ($arFeature["Operations"] as $operation => $perm)
									{
										if (
											$feature == "tasks"
											&& (
												$operation == "modify_folders"
												|| $operation === 'modify_common_views'
											)
											&& ModuleManager::isModuleInstalled('tasks')
										)
										{
											?><input type="hidden" name="<?= $feature ?>_<?= $operation ?>_perm" value="<?=$perm?>"><?
										}
										else
										{
											$title = (
												array_key_exists("operation_titles", $arResult["arSocNetFeaturesSettings"][$feature])
												&& array_key_exists($operation, $arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"])
												&& strlen($arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"][$operation]) > 0
													? $arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"][$operation]
													: Loc::getMessage("SONET_FEATURES_".$feature."_".$operation)
											);

											?><div class="sn-features-input-box">
												<div class="sn-features-caption"><?=$title?></div>
												<select name="<?=$feature?>_<?=$operation?>_perm" class="sn-features-select"><?

													foreach ($arResult["PermsVar"] as $key => $value)
													{
														if (
															!array_key_exists("restricted", $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation])
															|| !in_array($key, $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation]["restricted"][$arResult["ENTITY_TYPE"]])
														)
														{
															?><option value="<?=$key?>"<?=($key == $perm) ? " selected" : "" ?>><?=$value?></option><?
														}
													}

												?></select>
											</div><?
										}
									}
								}

							?></div><?

						?></div><?
					}
				}
			?></div><?

			if ($hasActiveFeatures)
			{
				?><div class="sonet-slider-footer-fixed">
					<input type="hidden" name="ajax_request" value="Y">
					<input type="hidden" name="save" value="Y">
					<input type="hidden" name="SONET_USER_ID" value="<?=$arParams["USER_ID"]?>">
					<input type="hidden" name="SONET_GROUP_ID" value="<?=$arParams["GROUP_ID"]?>">
					<?=bitrix_sessid_post()?>
					<span class="sonet-ui-btn-cont sonet-ui-btn-cont-center"><?
						?><button class="ui-btn ui-btn-success" id="sonet_group_features_form_button_submit"><?=Loc::getMessage("SONET_C4_SUBMIT") ?></button><?
						?><button class="ui-btn ui-btn-light-border" id="sonet_group_features_form_button_cancel"><?=Loc::getMessage("SONET_C4_T_CANCEL") ?></button><?
					?></span><? // class="sonet-ui-btn-cont"
				?></div><? // sonet-slider-footer-fixed
			}
			else
			{
				?><div class="settings-group-main-wrap"><?=GetMessage("SONET_C4_NO_FEATURES");?></div><?
			}
		?></form><?
	}
	else
	{
		if ($arParams["PAGE_ID"] == "group_features")
		{
			echo GetMessage("SONET_C4_GR_SUCCESS");
			?><br><br>
			<a href="<?= $arResult["Urls"]["Group"] ?>"><?= $arResult["Group"]["NAME"]; ?></a><?
		}
		else
		{
			echo GetMessage("SONET_C4_US_SUCCESS");
			?><br><br>
			<a href="<?= $arResult["Urls"]["User"] ?>"><?= $arResult["User"]["NAME_FORMATTED"]; ?></a><?
		}
	}
}
?>