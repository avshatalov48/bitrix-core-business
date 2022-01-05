<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $isProject */
/** @var boolean $isScrumProject */

Loc::loadMessages(__FILE__);

?>
<div class="socialnetwork-group-create-ex__content-title"><?= Loc::getMessage('SONET_GCE_T_TEAM_TITLE') ?></div>
<div class="socialnetwork-group-create-ex__content-block --space-bottom">
	<?php

	if (
		empty($arResult['TAB'])
		|| $arResult['TAB'] === 'edit'
	)
	{
		?>
		<div class="socialnetwork-group-create-ex__create--switch-notinviteonly">
			<div class="socialnetwork-group-create-ex__content-block --space-bottom" id="GROUP_OWNER_block" style="margin-bottom: 15px">
				<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_OWNER')?></div>
				<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_OWNER_PROJECT')?></div>
				<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-scrum <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_SCRUM_OWNER')?></div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<?php

					$selectorName = 'group_create_owner_' . Random::getString(6);

					$APPLICATION->IncludeComponent(
						'bitrix:main.user.selector',
						'',
						[
							'ID' => $selectorName,
							'INPUT_NAME' => 'OWNER_CODE',
							'LIST' => (
							!empty($arResult['POST'])
							&& !empty($arResult['POST']['OWNER_ID'])
								? [ 'U' . $arResult['POST']['OWNER_ID'] ]
								: [ 'U' . $arResult['currentUserId'] ]
							),
							'USE_SYMBOLIC_ID' => true,
							'BUTTON_SELECT_CAPTION' => Loc::getMessage('SONET_GCE_T_ADD_OWNER2'),
							'API_VERSION' => 3,
							'SELECTOR_OPTIONS' => [
								'userSearchArea' => ($arResult['bExtranetInstalled'] ? 'I' : false),
								'contextCode' => 'U',
								'context' => $arResult['destinationContextOwner'],
							],
						]
					);
					?>
				</div>
			</div>
			<?php

			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/team_scrum.php');

			?>
			<div class="socialnetwork-group-create-ex__content-block --space-bottom">
				<div class="socialnetwork-group-create-ex__content-block"><?php
					?><div id="GROUP_MODERATORS_PROJECT_switch" class="socialnetwork-group-create-ex__text --s --margin-bottom">
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>" data-role="socialnetwork-group-create-ex__expandable" for="expandable-moderator-block" style="margin-bottom: 30px"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS') ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>" data-role="socialnetwork-group-create-ex__expandable" for="expandable-moderator-block"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS_PROJECT') ?></div>
						<div class="socialnetwork-group-create-ex__text --s socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS_SCRUM_PROJECT') ?></div>
					</div>
				</div>
				<div id="expandable-moderator-block" class="socialnetwork-group-create-ex__content-expandable">
					<div class="socialnetwork-group-create-ex__content-expandable--wrapper">
						<div class="socialnetwork-group-create-ex__content-block">
							<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
								<?php

								$selectorName = 'group_create_moderators_' . Random::getString(6);

								$moderatorsList = [];
								if (
									!empty($arResult['POST'])
									&& !empty($arResult['POST']['MODERATOR_IDS'])
									&& is_array($arResult['POST']['MODERATOR_IDS'])
								)
								{
									foreach ($arResult['POST']['MODERATOR_IDS'] as $moderatorId)
									{
										$moderatorsList['U' . $moderatorId] = 'users';
									}
								}

								$APPLICATION->IncludeComponent(
									'bitrix:main.user.selector',
									'',
									[
										'ID' => $selectorName,
										'INPUT_NAME' => 'MODERATOR_CODES[]',
										'LIST' => $moderatorsList,
										'USE_SYMBOLIC_ID' => true,
										'BUTTON_SELECT_CAPTION' => (
											$arResult['intranetInstalled']
												? Loc::getMessage('SONET_GCE_T_ADD_EMPLOYEE')
												: Loc::getMessage('SONET_GCE_T_ADD_USER')
										),
										'BUTTON_SELECT_CAPTION_MORE' => Loc::getMessage('SONET_GCE_T_DEST_LINK_2'),
										'API_VERSION' => 3,
										'SELECTOR_OPTIONS' => [
											'contextCode' => 'U',
											'context' => $arResult['destinationContextModerators'],
										],
									]
								);
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	if (
		empty($arResult['TAB'])
		|| $arResult['TAB'] === 'invite'
	)
	{
		?>
		<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TEAM_TITLE_USER')?></div>
		<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TEAM_TITLE_USER_PROJECT')?></div>
		<div class="socialnetwork-group-create-ex__text --s --margin-bottom socialnetwork-group-create-ex__create--switch-scrum --margin-bottom <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_TEAM_TITLE_USER_SCRUM')?></div>
		<?php

		$selectorName = Random::getString(6);
		$enableSelectDepartment = (isset($arResult['GROUP_PROPERTIES']['UF_SG_DEPT']) && !$arResult['bExtranet']);

		$usersList = [];
		if (
			!empty($arResult['POST'])
			&& !empty($arResult['POST']['USER_CODES'])
			&& is_array($arResult['POST']['USER_CODES'])
		)
		{
			foreach($arResult['POST']['USER_CODES'] as $userCode)
			{
				$userLists[$userCode] = 'users';
			}
		}

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
					'OPEN_DIALOG_WHEN_INIT' => ($arResult['POST']['IS_EXTRANET_GROUP'] !== 'Y' && $arResult['TAB'] === 'invite'),
					'FIRE_CLICK_EVENT' => (
						$arResult['POST']['IS_EXTRANET_GROUP'] !== 'Y'
						&& $arResult['TAB'] === 'invite'
							? 'Y'
							: 'N'
					),
					'BUTTON_SELECT_CAPTION' => (
						$arResult['intranetInstalled']
							? Loc::getMessage('SONET_GCE_T_ADD_EMPLOYEE')
							: Loc::getMessage('SONET_GCE_T_ADD_USER')
					),
					'BUTTON_SELECT_CAPTION_MORE' => Loc::getMessage('SONET_GCE_T_DEST_LINK_2'),
					'API_VERSION' => 3,
					'SELECTOR_OPTIONS' => [
						'contextCode' => '',
						'context' => $arResult['destinationContextUsers'],
						'departmentSelectDisable' => ($enableSelectDepartment ? 'N' : 'Y'),
						'siteDepartmentId' => (isset($arResult['siteDepartmentID']) && (int)$arResult['siteDepartmentID'] > 0 ? (int)$arResult['siteDepartmentID'] : ''),
						'userSearchArea' => ($arResult['bExtranetInstalled'] ? 'I' : 'N'),
						'allowSearchSelf' => ($arResult['isCurrentUserAdmin'] ? 'Y' : 'N'),
					]
				]
			);

			?><input type="hidden" name="NEW_INVITE_FORM" value="Y">
		</div>
		<?php

		if ($enableSelectDepartment)
		{
			?><div class="social-group-create-options-add-dept-hint" id="GROUP_ADD_DEPT_HINT_block">
				<div class="socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT') ?></div>
				<div class="socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT_PROJECT') ?></div>
			</div><?php
		}

		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/team_extranet.php');
	}
	?>
</div>
<?php
