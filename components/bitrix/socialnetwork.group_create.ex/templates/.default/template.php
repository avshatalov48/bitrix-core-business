<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
	'ui.alerts',
	'ui.icons.b24',
	'ui.forms',
	'ui.hint',
	'ui.entity-selector',
	'socialnetwork.common',
	'intranet_theme_picker',
]);

if (empty($arResult['TAB']))
{
	$bodyClassList = explode(' ', $APPLICATION->getPageProperty('BodyClass'));
	$bodyClassList[] = 'no-background';
	$bodyClassList[] = 'no-padding';
	$bodyClassList[] = 'workgroup-form-master';
	$APPLICATION->setPageProperty('BodyClass', implode(' ', $bodyClassList));
}

if (($arResult['NEED_AUTH'] ?? '') === 'Y')
{
	$APPLICATION->AuthForm("");
}
elseif (($arResult["FatalError"] ?? '') <> '')
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?php
}
else
{
	$jsCoreExtensionList = [ 'popup', 'fx', 'avatar_editor' ];
	if ($arResult["intranetInstalled"])
	{
		$jsCoreExtensionList = array_merge($jsCoreExtensionList, [ 'ui_date', 'date' ]);
	}

	CJSCore::Init($jsCoreExtensionList);
	UI\Extension::load("ui.selector");

	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/main.post.form/templates/.default/style.css");
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.blog.post.edit/templates/.default/style.css");

	?>
		<div
			id="sonet_group_create_error_block"
			class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(($arResult["ErrorMessage"] ?? '') <> '' ? "" : " sonet-ui-form-error-block-invisible")?>"
		><?=$arResult["ErrorMessage"] ?? ''?>
		</div>
	<?php

	if ($arResult["ShowForm"] === "Input")
	{
		$bodyClassList = explode(' ', $APPLICATION->getPageProperty('BodyClass'));
		$bodyClassList[] = 'social-group-create-body';
		$APPLICATION->setPageProperty('BodyClass', implode(' ', $bodyClassList));

		if (
			$arResult["IS_IFRAME"]
			&& $arResult["CALLBACK"] === "REFRESH"
		)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.onCustomEvent('onSonetIframeCallbackRefresh');
			</script><?php
			die();
		}

		if (
			$arResult["IS_IFRAME"]
			&& $arResult["CALLBACK"] === "GROUP"
		)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.onCustomEvent('onSonetIframeCallbackGroup', [<?= (int)$_GET["GROUP_ID"] ?>]);
			</script><?php
			die();
		}

		?><script><?php

			if (
				$arResult["IS_IFRAME"]
				&& $arResult["CALLBACK"] === "EDIT"
			)
			{
				// this situation is impossible now but this code may be needed in the future
				?>
				(function() {
					var iframePopup = window.top.BX.SonetIFramePopup;
					if (iframePopup)
					{
						BX.adjust(iframePopup.title, {text: BX.message("SONET_GROUP_TITLE_EDIT").replace('#GROUP_NAME#', BX.message("SONET_GROUP_TITLE"))});
					}
				})();
				<?php
			}
			?>
			top.BXExtranetMailList = [];

			BX.message({
				SONET_GROUP_TITLE_EDIT : '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_TITLE_EDIT')) ?>',
				SONET_GCE_T_AJAX_ERROR:  '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_AJAX_ERROR')) ?>',
				SONET_GCE_T_STRING_FIELD_ERROR:  '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_STRING_FIELD_ERROR')) ?>'
				<?php
				if (isset($arResult['POST']['NAME']) && (string)$arResult['POST']['NAME'] !== '')
				{
					?>
					, SONET_GROUP_TITLE : '<?= CUtil::JSEscape($arResult["POST"]["NAME"]) ?>'
					<?php
				}
				?>
			});

			BX.ready(
				function()
				{
					new BX.Socialnetwork.WorkgroupForm({
						componentName: '<?= $this->getComponent()->getName() ?>',
						signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>',
						selectedProjectType: '<?= (!empty($arResult['selectedProjectType']) ? CUtil::jsEscape($arResult['selectedProjectType']) : '') ?>',
						selectedConfidentialityType: '<?= (!empty($arResult['selectedConfidentialityType']) ? CUtil::jsEscape($arResult['selectedConfidentialityType']) : '') ?>',
						groupId: <?= (int)$arParams["GROUP_ID"] ?>,
						isScrumProject: <?= ($arResult['isScrumProject'] ? 'true' : 'false') ?>,
						config: <?= CUtil::phpToJSObject($arResult['ClientConfig']) ?>,
						avatarUploaderId: '<?= $arResult['AVATAR_UPLOADER_CID'] ?? '' ?>',
						themePickerData: <?= CUtil::phpToJSObject($arResult['themePickerData']) ?>,
						projectOptions: <?= CUtil::phpToJSObject($arParams['PROJECT_OPTIONS']) ?>,
						projectTypes: <?= CUtil::phpToJSObject($arResult['ProjectTypes']) ?>,
						confidentialityTypes: <?= CUtil::phpToJSObject($arResult['ConfidentialityTypes']) ?>,
						expandableSettingsNodeId: 'sonet_group_create_settings_expandable',
						stepsCount: <?= ($arResult['USE_PRESETS'] === 'Y' && $arParams['GROUP_ID'] <= 0 ? 4 : 1) ?>,
						focus: '<?= CUtil::JSEscape(\Bitrix\Main\Context::getCurrent()->getRequest()->get('focus')) ?>',
						culture: <?= CUtil::phpToJSObject($arResult['culture']) ?>
					});
				}
			);
		</script><?php

		if (
			is_array($arResult["ErrorFields"])
			&& count($arResult["ErrorFields"]) > 0
		)
		{
			$bHasUserFieldError = false;
			foreach ($arResult["GROUP_PROPERTIES"] as $FIELD_NAME => $arUserField)
			{
				if (in_array($FIELD_NAME, $arResult["ErrorFields"]))
				{
					$bHasUserFieldError = true;
					break;
				}
			}
		}

		$uri = new Bitrix\Main\Web\Uri(POST_FORM_ACTION_URI);
		if (!empty($arResult["typeCode"]))
		{
			$uri->deleteParams([ 'b24statAction', 'b24statType' ]);
			$uri->addParams([
				'b24statType' => $arResult['typeCode'],
			]);
		}
		$actionUrl = $uri->getUri();

		$isProject = (($arResult['POST']['PROJECT'] ?? '') === 'Y');
		$isScrumProject = $arResult['isScrumProject'];

		?>
		<form method="post" name="sonet_group_create_popup_form" id="sonet_group_create_popup_form" action="<?=$actionUrl?>" enctype="multipart/form-data"><?php
			?><input type="hidden" name="ajax_request" value="Y"><?php
			?><input type="hidden" name="save" value="Y"><?php
			?><?=bitrix_sessid_post()?><?php

			$classList = [
				'socialnetwork-group-create-ex',
				'socialnetwork-group-create-ex__scope',
			];

			switch ((string) ($arResult['TAB'] ?? ''))
			{
				case '':
					$classList[] = 'socialnetwork-group-create-ex__create';
					break;
				case 'edit':
					$classList[] = 'socialnetwork-group-create-ex__edit';
					break;
				case 'invite':
					$classList[] = 'socialnetwork-group-create-ex__invite';
					break;
				default:
			}
			?>
			<div class="<?= implode(' ', $classList) ?>">
				<div class="socialnetwork-group-create-ex__wrapper"><?php

					$classList = [
						'socialnetwork-group-create-ex__background-gif',
						'--active',
					];
					if ($isScrumProject)
					{
						$classList[] = '--scrum';
					}
					elseif ($isProject)
					{
						$classList[] = '--project';
					}
					else
					{
						$classList[] = '--group';
					}


					?>
					<div class="<?= implode(' ', $classList) ?>"></div>
					<div class="socialnetwork-group-create-ex__head">
						<div class="socialnetwork-group-create-ex__head--logo"></div>
						<div class="socialnetwork-group-create-ex__head--container">
							<div class="socialnetwork-group-create-ex__head--title"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_WIZARD_TITLE')) ?></div>
							<div class="socialnetwork-group-create-ex__head--sub-title"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_WIZARD_DESCRIPTION')) ?></div>
						</div>
					</div>
					<div class="socialnetwork-group-create-ex__content">
						<?php
						require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/breadcrumbs.php');

						$commonBodyClassList = [ 'socialnetwork-group-create-ex__content-body' ];

						$bodyClassList = [
							'--step-1',
						];

						if (
							empty($arResult['TAB'])
							&& count($arResult['ProjectTypes']) > 1
						)
						{
							$bodyClassList[] = '--active';
						}

						?>
						<div class="<?= implode(' ', array_merge($commonBodyClassList, $bodyClassList)) ?>">
							<?php
							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/typepreset_selector.php');
							?>
						</div>
						<?php

						$bodyClassList = [
							'--step-2',
						];

						if (
							($arResult['TAB'] ?? '') === 'edit'
							|| (
								empty($arResult['TAB'])
								&& count($arResult['ProjectTypes']) <= 1
							)
						)
						{
							$bodyClassList[] = '--active';
						}

						?>
						<div class="<?= implode(' ', array_merge($commonBodyClassList, $bodyClassList)) ?>">
							<?php
							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/fields.php');
							?>
						</div>
						<?php
						$bodyClassList = [
							'--step-3',
						];
						?>
						<div class="<?= implode(' ', array_merge($commonBodyClassList, $bodyClassList)) ?>">
							<?php
							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/confidentiality.php');
							?>
						</div>
						<?php

						$bodyClassList = [
							'--step-4',
						];

						if (
							($arResult['TAB'] ?? '') === 'edit'
							|| ($arResult['TAB'] ?? '') === 'invite'
						)
						{
							$bodyClassList[] = '--active';
						}

						?>
						<div class="<?= implode(' ', array_merge($commonBodyClassList, $bodyClassList)) ?>">
							<?php
							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/team.php');
							?>
						</div>
					</div>
				</div>
				<div class="socialnetwork-group-create-ex__background"></div>
			</div>
			<?php

			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/buttons.php');

			?>
			<input type="hidden" name="SONET_USER_ID" value="<?= $arResult['currentUserId'] ?>">
			<input type="hidden" name="SONET_GROUP_ID" id="SONET_GROUP_ID" value="<?= (int)$arResult["GROUP_ID"] ?>">
			<input
				type="hidden"
				name="TAB"
				id="TAB"
				value="<?= htmlspecialcharsbx(CUtil::JSEscape($arResult['TAB'] ?? '')) ?>"
			>
		</form>
		<?php
	}
	else
	{
		?><?= ($arParams["GROUP_ID"] > 0 ? Loc::getMessage('SONET_GCE_T_SUCCESS_EDIT') : Loc::getMessage('SONET_GCE_T_SUCCESS_CREATE')) ?><?php
		?><br><br>
		<a href="<?= $arResult["Urls"]["NewGroup"] ?>"><?= $arResult["POST"]["NAME"] ?></a><?php
	}
}
