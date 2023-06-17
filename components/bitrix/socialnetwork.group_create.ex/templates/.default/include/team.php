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

$ownerId = (int)(
	!empty($arResult['POST'])
	&& !empty($arResult['POST']['OWNER_ID'])
		? $arResult['POST']['OWNER_ID']
		: $arResult['currentUserId']
);

$ownerSelectorValue = (
	$ownerId > 0
		? [
			[ 'user', $ownerId ]
		]
		: []
);

$scrumMasterId = (int)(
	!empty($arResult['POST'])
	&& !empty($arResult['POST']['SCRUM_MASTER_ID'])
		? $arResult['POST']['SCRUM_MASTER_ID']
		: 0
);

$scrumMasterSelectorValue = (
	$scrumMasterId > 0
		? [
			[ 'user', $scrumMasterId ]
		]
		: []
);

$moderatorsIdList = (
	!empty($arResult['POST'])
	&& !empty($arResult['POST']['MODERATOR_IDS'])
	&& is_array($arResult['POST']['MODERATOR_IDS'])
		? array_map(static function($value) { return (int)$value; }, $arResult['POST']['MODERATOR_IDS'])
		: []
);

$moderatorsSelectorValue = (
	!empty($moderatorsIdList)
		? array_map(static function($value) { return [ 'user', (int)$value ]; }, $moderatorsIdList)
		: []
);

$usersIdList = (
	!empty($arResult['POST'])
	&& !empty($arResult['POST']['USER_CODES'])
	&& is_array($arResult['POST']['USER_CODES'])
		? array_map(static function($value) {

			return (int)( preg_match('/^U(\d+)$/', $value, $matches) ? $matches[1] : 0);


		}, $arResult['POST']['USER_CODES'])
		: []
);

$usersSelectorValue = (
	!empty($arResult['POST'])
	&& !empty($arResult['POST']['USER_CODES'])
	&& is_array($arResult['POST']['USER_CODES'])
		? array_map(static function($value) {

			if (preg_match('/^U(\d+)$/', $value, $matches))
			{
				return [ 'user', (int)$matches[1] ];
			}

			if (preg_match('/^DR(\d+)$/', $value, $matches))
			{
				return [ 'department', (int)$matches[1] ];
			}

			return [ 'user', 0 ];

		}, $arResult['POST']['USER_CODES'])
		: []
);

$usersSelectorName = Random::getString(6);
$enableSelectDepartment = (isset($arResult['GROUP_PROPERTIES']['UF_SG_DEPT']) && !$arResult['bExtranet']);

?>
<script>
	BX.message({
		SONET_GCE_T_ADD_OWNER: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_ADD_OWNER2')) ?>',
		SONET_GCE_T_ADD_USER: '<?= CUtil::JSEscape(
			$arResult['intranetInstalled']
				? Loc::getMessage('SONET_GCE_T_ADD_EMPLOYEE')
				: Loc::getMessage('SONET_GCE_T_ADD_USER')
		) ?>',
		SONET_GCE_T_ADD_USER_MORE: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_DEST_LINK_2')) ?>',
	});

	BX.ready(function() {
		new BX.Socialnetwork.WorkgroupFormTeamManager({
			groupId: <?= (int) $arResult['GROUP_ID'] ?>,
			extranetInstalled: <?= ($arResult['bExtranetInstalled'] ? 'true' : 'false') ?>,
			allowExtranet: <?= (
				$arResult['bExtranetInstalled']
				&& ($arResult['POST']['IS_EXTRANET_GROUP'] ?? '') === 'Y'
					? 'true'
					: 'false'
			) ?>,
			isCurrentUserAdmin: <?= ($arResult['isCurrentUserAdmin'] ? 'true' : 'false') ?>,
			ownerOptions: {
				selectorId: 'group_create_owner_<?= Random::getString(6) ?>',
				value: <?= CUtil::phpToJSObject(array_values($ownerSelectorValue)) ?>,
			},
			scrumMasterOptions: {
				selectorId: 'group_create_scrum_master_<?= Random::getString(6) ?>',
				value: <?= CUtil::phpToJSObject(array_values($scrumMasterSelectorValue)) ?>,
			},
			moderatorsOptions: {
				selectorId: 'group_create_moderators_<?= Random::getString(6) ?>',
				value: <?= CUtil::phpToJSObject(array_values($moderatorsSelectorValue)) ?>,
			},
			usersOptions: {
				selectorId: '<?= $usersSelectorName ?>',
				value: <?= CUtil::phpToJSObject(array_values($usersSelectorValue)) ?>,
				enableSelectDepartment: <?= ($enableSelectDepartment ? 'true' : 'false') ?>,
			},
		});
	});
</script>
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
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="GROUP_OWNER_selector"></div>
				<div id="OWNER_CODE_container">
					<input type="hidden" name="OWNER_CODE" value="<?= 'U' . $ownerId ?>">
				</div>
			</div>
			<?php

			require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/team_scrum.php');

			?>
			<div class="socialnetwork-group-create-ex__content-block --space-bottom">
				<div class="socialnetwork-group-create-ex__content-block"><?php
					?><div id="GROUP_MODERATORS_PROJECT_switch" class="socialnetwork-group-create-ex__text --s --margin-bottom">
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>" data-role="socialnetwork-group-create-ex__expandable" for="expandable-moderator-block"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS') ?></div>
						<div class="ui-ctl-label-text socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>" data-role="socialnetwork-group-create-ex__expandable" for="expandable-moderator-block"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS_PROJECT') ?></div>
						<div class="socialnetwork-group-create-ex__text --s socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_DEST_TITLE_MODERATORS_SCRUM_PROJECT') ?></div>
					</div>
				</div>
				<div id="expandable-moderator-block" class="socialnetwork-group-create-ex__content-expandable">
					<div class="socialnetwork-group-create-ex__content-expandable--wrapper">
						<div class="socialnetwork-group-create-ex__content-block">
							<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="GROUP_MODERATORS_selector"></div>
							<div id="MODERATOR_CODES_container"><?php
								foreach ($moderatorsIdList as $moderatorId)
								{
									?>
									<input type="hidden" name="MODERATOR_CODES[]" value="<?= 'U' . $moderatorId ?>">
									<?php
								}
							?></div>
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
		<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" id="GROUP_USERS_selector"></div>
		<div id="USER_CODES_container"><?php
			foreach ($usersIdList as $userId)
			{
				?>
				<input type="hidden" name="USER_CODES[]" value="<?= 'U' . $userId ?>">
				<?php
			}
		?></div>
		<input type="hidden" name="NEW_INVITE_FORM" value="Y">
		<?php

		if ($enableSelectDepartment)
		{
			?><div class="social-group-create-options-add-dept-hint" id="GROUP_ADD_DEPT_HINT_block">
				<div class="socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT') ?></div>
				<div class="socialnetwork-group-create-ex__create--switch-project <?= ($isProject ? '--project' : '') ?>"><?= Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT_PROJECT') ?></div>
			</div><?php
		}
	}
	?>
</div>
<?php
