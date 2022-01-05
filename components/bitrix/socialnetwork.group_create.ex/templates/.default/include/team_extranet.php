<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var array $userLists */

Loc::loadMessages(__FILE__);

?>
<script>
	BX.message({
		SONET_GCE_T_EMAILS_DESCR: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_EMAILS_DESCR')) ?>',
		SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE : '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE')) ?>',
		SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD : '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD')) ?>',
	});
</script>
<?php

if (
	$arResult['bExtranetInstalled']
	&& Loader::includeModule('intranet')
	&& Option::get('extranet', 'extranet_site') !== ''
	&& (
		empty($arResult['TAB'])
		|| (
			$arResult['TAB'] === 'invite'
			&& $arResult['POST']['IS_EXTRANET_GROUP'] === 'Y'
		)
	)
)
{
	$classList = [
		'socialnetwork-group-create-ex__content-block'
	];
	if ($arResult['POST']['IS_EXTRANET_GROUP'] !== 'Y')
	{
		$classList[] = '--hidden';
	}
	?>

	<div id="INVITE_EXTRANET_block_container" class="<?= implode(' ', $classList) ?>" style="margin-top: 30px">
		<div class="ui-ctl-label-text --bold"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_EXTERNAL2') ?></div>
		<div id="INVITE_EXTRANET_block">
			<div class="socialnetwork-group-create-ex__text ui-ctl-label-text"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_EXTRANET') ?></div>
			<?php

			$selectorName = Random::getString(6);

			?>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" data-employees-selector-id="<?= htmlspecialcharsbx($selectorName) ?>">
				<?php

				$APPLICATION->IncludeComponent(
					'bitrix:main.user.selector',
					'',
					[
						'ID' => $selectorName,
						'INPUT_NAME' => 'USER_CODES[]',
						'LIST' => $userLists,
						'USE_SYMBOLIC_ID' => true,
						'BUTTON_SELECT_CAPTION' => Loc::getMessage('SONET_GCE_T_ADD_EXTRANET'),
						'BUTTON_SELECT_CAPTION_MORE' => Loc::getMessage('SONET_GCE_T_DEST_LINK_2'),
						'API_VERSION' => 3,
						'SELECTOR_OPTIONS' => [
							'contextCode' => 'U',
							'context' => $arResult['destinationContextUsers'],
							'departmentSelectDisable' => 'Y',
							'siteDepartmentId' => 'EX',
							'userSearchArea' => 'E',
							'allowSearchSelf' => 'N',
						]
					]
				);

				?>
			</div>
			<?php
				$isAddAction = (
					isset($arResult['POST']['EXTRANET_INVITE_ACTION'])
					&& $arResult['POST']['EXTRANET_INVITE_ACTION'] === 'add'
				)
			?>
			<div class="socialnetwork-group-create-ex__extranet-users">
				<div class="socialnetwork-group-create-ex__extranet-toggler">
					<div id="sonet_group_create_popup_action_title_invite" class="socialnetwork-group-create-ex__extranet-toggler--item --active"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE') ?></div>
					<div id="sonet_group_create_popup_action_title_add" class="socialnetwork-group-create-ex__extranet-toggler--item"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD') ?></div>
				</div>
				<?php
					$style = ($isAddAction ? 'display: none;' : 'display: block;');
				?><div id="sonet_group_create_popup_action_block_invite" style="<?= $style ?>;" class="socialnetwork-group-create-ex__content-block --space-bottom"><?php

					$value = (
					(string)$arResult['POST']['EMAILS'] !== ''
						? htmlspecialcharsbx($arResult['POST']['EMAILS'])
						: Loc::getMessage('SONET_GCE_T_EMAILS_DESCR')
					);
					?>
						<label for="EMAILS" class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_EMAIL_SHORT') ?></label>
						<div class="ui-ctl ui-ctl-textarea">
							<textarea rows="5" type="text" name="EMAILS" id="EMAILS" class="ui-ctl-element"><?= $value ?></textarea>
						</div>
				</div><?php

				$style = ($isAddAction ? 'display: block;' : 'display: none;');

				?>
				<div id="sonet_group_create_popup_action_block_add" style="<?= $style ?>;">
					<div class="socialnetwork-group-create-ex__content-block --space-bottom">
						<label for="ADD_EMAIL" class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_ADD_EMAIL_TITLE') ?></label>
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
							<input type="text" name="ADD_EMAIL" id="ADD_EMAIL" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['ADD_EMAIL']) ?>">
						</div>
					</div>
					<div class="socialnetwork-group-create-ex__content-block --space-bottom">
						<label for="ADD_NAME" class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_ADD_NAME_TITLE') ?></label>
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
							<input type="text" name="ADD_NAME" id="ADD_NAME" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['ADD_NAME']) ?>">
						</div>
					</div>
					<div class="socialnetwork-group-create-ex__content-block --space-bottom">
						<label for="ADD_LAST_NAME" class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_ADD_LAST_NAME_TITLE') ?></label>
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
							<input type="text" name="ADD_LAST_NAME" id="ADD_LAST_NAME" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['ADD_LAST_NAME']) ?>">
						</div>
					</div>
					<div class="socialnetwork-group-create-ex__content-block">
						<?php
							$labelText = (
							$arResult['bitrix24Installed']
								? Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_ADD_WO_CONFIRMATION_TITLE')
								: Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_ADD_SEND_PASSWORD_TITLE')
							);
						?>
						<label class="ui-ctl ui-ctl-checkbox">
						<input type="checkbox" name="ADD_SEND_PASSWORD" id="ADD_SEND_PASSWORD" value="Y" class="ui-ctl-element"><?php
						?><div class="ui-ctl-label-text" for="ADD_SEND_PASSWORD"><?= $labelText ?></div>
						</label>
					</div>
				</div>
				<div id="sonet_group_create_popup_action_block_invite_2" class="socialnetwork-group-create-ex__content-block --space-bottom">
					<label for="MESSAGE_TEXT" class="socialnetwork-group-create-ex__text --s --margin-bottom"><?= Loc::getMessage('SONET_GCE_T_DEST_EXTRANET_INVITE_MESSAGE_TITLE') ?></label>
					<?php
						$disabled = ($arResult['messageTextDisabled'] ? 'disabled readonly' : '');
					?>
					<div class="ui-ctl ui-ctl-textarea">
						<textarea rows="5" type="text" name="MESSAGE_TEXT" id="MESSAGE_TEXT" class="ui-ctl-element"<?= $disabled ?>><?= $arResult["inviteMessageText"] ?></textarea>
					</div>
				</div>
				<input type="hidden" id="EXTRANET_INVITE_ACTION" name="EXTRANET_INVITE_ACTION" value="invite">
			</div>
		</div>
	</div>
	<?php
}
