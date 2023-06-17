<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI;

UI\Extension::load([
	'ui.design-tokens',
	'socialnetwork.common',
	'ui.buttons',
	'ui.alerts',
	'ui.info-helper',
]);

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?php
}
else
{
	$isProject = ($arResult['Group']['PROJECT'] === 'Y');

	if (
		!empty($arResult["ErrorMessage"])
		&& $arResult["ShowForm"] != "Input"
	)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?php
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

		<div id="sonet_features_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(!empty($arResult["ErrorMessage"]) ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?php

		$uri = new Bitrix\Main\Web\Uri(POST_FORM_ACTION_URI);
		if (!empty($arResult["groupTypeCode"]))
		{
			$uri->deleteParams(array("b24statAction", "b24statType"));
			$uri->addParams(array(
				"b24statType" => $arResult["groupTypeCode"]
			));
		}
		$actionUrl = $uri->getUri();
		?><form method="post" name="sonet-features-form" id="sonet-features-form" action="<?=$actionUrl?>" enctype="multipart/form-data">
			<div class="sn-features-wrap"><?php

				$hasActiveFeatures = false;

				if (
					$arResult["ENTITY_TYPE"] === "G"
					&& !empty($arResult["Group"])
					&& $arResult["Group"]["CLOSED"] !== "Y"
				)
				{
					?><div class="sn-features-row">
						<h4 class="sn-features-title"><?= ($isProject ? Loc::getMessage('SONET_C4_INVITE_TITLE_PROJECT') : Loc::getMessage('SONET_C4_INVITE_TITLE')) ?></h4>
						<div class="sn-features-input-box">
							<div class="sn-features-caption"><?= ($isProject ? Loc::getMessage('SONET_C4_INVITE_OPERATION_PROJECT') : Loc::getMessage('SONET_C4_INVITE_OPERATION')) ?></div>
							<select name="GROUP_INITIATE_PERMS" id="GROUP_INITIATE_PERMS" class="sn-features-select"><?php
								foreach ($arResult["InitiatePermsList"] as $key => $value)
								{
									?><option id="GROUP_INITIATE_PERMS_OPTION_<?=$key?>" value="<?=$key?>"<?=($key == $arResult["Group"]["INITIATE_PERMS"]) ? " selected" : "" ?>><?=$value?></option><?php
								}
							?></select>
						</div>
					</div><?php

					if (
						(
							!\Bitrix\Main\Loader::includeModule('extranet')
							|| !CExtranet::IsExtranetSite()
						)
						&& !ModuleManager::isModuleInstalled('im')
					)
					{
						?><div class="sn-features-row">
							<div class="sn-features-input-box">
								<div class="sn-features-caption"><?= Loc::getMessage('SONET_C4_SPAM_OPERATION') ?></div>
								<select name="GROUP_SPAM_PERMS" id="GROUP_SPAM_PERMS" class="sn-features-select"><?php
									foreach ($arResult['SpamPermsList'] as $key => $value)
									{
										?><option id="GROUP_SPAM_PERMS_OPTION_<?= $key ?>" value="<?= $key ?>"<?= ($key === $arResult['Group']['SPAM_PERMS'] ? ' selected' : '') ?>><?= $value ?></option><?php
									}
								?></select>
							</div>
						</div><?php
					}
				}

				foreach ($arResult["Features"] as $feature => $arFeature)
				{
					if (
						$arResult["ENTITY_TYPE"] === "G"
						&& !isset($arFeature["note"])
						&& !isset($arFeature["limit"])
						&& (
							empty($arFeature["Operations"])
							|| (
								isset($arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"])
								&& $arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"]
							)
						)
					)
					{
						?><input type="hidden" name="<?=$feature?>_active" value="<?=($arFeature["Active"] ? "Y" : "") ?>" /><?php
					}
					elseif (
						$arFeature["Active"]
						|| $arResult["ENTITY_TYPE"] == "U"
					)
					{
						$hasActiveFeatures = true;
						$featureBlockClass = 'sn-features-row';
						$featureSubTitleText = '';

						if (
							$feature == 'tasks'
							&& $arFeature['limit']
						)
						{
							$APPLICATION->IncludeComponent('bitrix:ui.info.helper', '', []);

							$featureBlockClass .= ' sn-features-lock';
							$featureSubTitleText = Loc::getMessage('SONET_C4_TASK_FEATURE_DISABLED', [
								'#LINK_START#' => '<a href="#" onclick="BX.UI.InfoHelper.show(\'' . $arFeature['limit'] . '\', {isLimit: true, limitAnalyticsLabels: {module: \'socialnetwork\', source: \'features\', feature: \'tasks\'}});">',
								'#LINK_END#' => '</a>',
							]);
						}

						$featureName = (
								array_key_exists("title", $arResult["arSocNetFeaturesSettings"][$feature])
								&& $arResult["arSocNetFeaturesSettings"][$feature]["title"] <> ''
									? $arResult["arSocNetFeaturesSettings"][$feature]["title"]
									: Loc::getMessage("SONET_FEATURES_".$feature)
						);

						?>
						<div class="<?= $featureBlockClass ?>"
							 onclick="BX.UI.InfoHelper.show('<?= $arFeature['limit'] ?>',{isLimit: true,limitAnalyticsLabels: {module: 'socialnetwork',source: 'features',feature: 'tasks',}})"
							 style="cursor:pointer;">
							<div class="sn-features-title-box">
								<h4 class="sn-features-title"><?=$featureName?></h4>
								<span class="sn-features-subtitle"><?=$featureSubTitleText?></span>
							</div><?php
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
								</div><?php
							}
							else
							{
								?><input type="hidden" name="<?=$feature?>_active" value="Y" /><?php
							}

							$displayValue = ($arFeature["Active"] ? 'block' : 'none');
							$onClick = (!empty($arFeature['limit']) ? "onclick=\"BX.UI.InfoHelper.show('" . CUtil::JSescape($arFeature['limit']) . "', {isLimit: true, limitAnalyticsLabels: {module: 'socialnetwork', source: 'features', feature: '{$feature}'}});\"" : '');

							?><div id="<?=$feature?>_body" style="display: <?=$displayValue?>" <?= $onClick ?>><?php
								if (isset($arFeature["note"]))
								{
									?><div class="settings-blocks-note"><?=htmlspecialcharsbx($arFeature['note'])?></div><?php
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
											?><input type="hidden" name="<?= $feature ?>_<?= $operation ?>_perm" value="<?=$perm?>"><?php
										}
										else
										{
											$title = (
												array_key_exists("operation_titles", $arResult["arSocNetFeaturesSettings"][$feature])
												&& array_key_exists($operation, $arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"])
												&& $arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"][$operation] <> ''
													? $arResult["arSocNetFeaturesSettings"][$feature]["operation_titles"][$operation]
													: Loc::getMessage("SONET_FEATURES_".$feature."_".$operation)
											);

											$disabled = (!empty($arFeature['limit']) ? 'disabled' : '');

											?><div class="sn-features-input-box">
												<div class="sn-features-caption"><?=$title?></div>
												<select name="<?=$feature?>_<?=$operation?>_perm" class="sn-features-select" <?= $disabled ?>><?php

													foreach ($arResult["PermsVar"] as $key => $value)
													{
														if (
															!array_key_exists("restricted", $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation])
															|| !in_array($key, $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation]["restricted"][$arResult["ENTITY_TYPE"]])
														)
														{
															?><option value="<?=$key?>"<?=($key == $perm) ? " selected" : "" ?>><?=$value?></option><?php
														}
													}

												?></select>
											</div><?php
										}
									}
								}

							?></div><?php

						?></div><?php
					}
				}
			?></div><?php

			if ($hasActiveFeatures)
			{
				$buttons = [
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-success" id="sonet_group_features_form_button_submit">' . Loc::getMessage('SONET_C4_SUBMIT') . '</button>',
					],
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<button class="ui-btn ui-btn-light-border" id="sonet_group_features_form_button_cancel" bx-url="' . htmlspecialcharsbx($arResult['ENTITY_TYPE'] === 'G' ? $arResult['Urls']['Group'] : $arResult['Urls']['User']) . '">' . Loc::getMessage('SONET_C4_T_CANCEL') . '</button>',
					],
				];

				$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
					'BUTTONS' => $buttons,
				]);

				?><input type="hidden" name="ajax_request" value="Y">
				<input type="hidden" name="save" value="Y">
				<input type="hidden" name="SONET_USER_ID" value="<?=$arParams["USER_ID"]?>">
				<input type="hidden" name="SONET_GROUP_ID" value="<?=$arParams["GROUP_ID"]?>">
				<?=bitrix_sessid_post()?><?php
			}
			else
			{
				?><div class="settings-group-main-wrap"><?=GetMessage("SONET_C4_NO_FEATURES");?></div><?php
			}
		?></form><?php
	}
	else
	{
		if ($arParams["PAGE_ID"] == "group_features")
		{
			echo GetMessage("SONET_C4_GR_SUCCESS");
			?><br><br>
			<a href="<?= $arResult["Urls"]["Group"] ?>"><?= $arResult["Group"]["NAME"]; ?></a><?php
		}
		else
		{
			echo GetMessage("SONET_C4_US_SUCCESS");
			?><br><br>
			<a href="<?= $arResult["Urls"]["User"] ?>"><?= $arResult["User"]["NAME_FORMATTED"]; ?></a><?php
		}
	}
}