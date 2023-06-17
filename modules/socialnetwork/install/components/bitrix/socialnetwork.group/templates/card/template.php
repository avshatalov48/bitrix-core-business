<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Helper;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Main\UserField;

UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'socialnetwork.common',
	'ui.icons.b24',
	'ui.info-helper',
	'ui.sidepanel-content',
	'ui.graph.circle',
	'intranet_theme_picker',
]);

CJSCore::init([ 'avatar_editor' ]);

if ((string) ($arResult['FatalError'] ?? '') !== '')
{
	?><span class="errortext"><?= $arResult['FatalError'] ?></span><br /><br /><?php
}
else
{
	if ((string) ($arResult['ErrorMessage'] ?? '') !== '')
	{
		?><span class="errortext"><?= $arResult['ErrorMessage'] ?></span><br /><br /><?php
	}

	?><script>
		BX.ready(function() {
			(new BX.Socialnetwork.WorkgroupCard()).init({
				componentName: '<?= $component->getName() ?>',
				signedParameters: '<?= $component->getSignedParameters() ?>',

				groupId: <?= (int)$arParams['GROUP_ID'] ?>,
				groupType: '<?= CUtil::JSEscape($arResult['groupTypeCode']) ?>',
				isProject: <?= ($arResult['Group']['PROJECT'] === 'Y' ? 'true' : 'false') ?>,
				isScrumProject: <?= ($arResult['isScrumProject'] ? 'true' : 'false') ?>,
				isOpened: <?= ($arResult['Group']['OPENED'] === 'Y' ? 'true' : 'false') ?>,
				currentUserId: <?= ($USER->isAuthorized() ? $USER->getid() : 0) ?>,

				userRole: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["UserRole"])?>',
				userIsMember: <?=($arResult["CurrentUserPerms"]["UserIsMember"] ? 'true' : 'false')?>,
				userIsAutoMember: <?=(isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) && $arResult["CurrentUserPerms"]["UserIsAutoMember"] ? 'true' : 'false') ?>,
				userIsScrumMaster: <?= (isset($arResult['CurrentUserPerms']['UserIsScrumMaster']) && $arResult['CurrentUserPerms']['UserIsScrumMaster'] ? 'true' : 'false') ?>,

				initiatedByType: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["InitiatedByType"])?>',
				initiatedByUserId: '<?= (int) $arResult['CurrentUserPerms']['InitiatedByUserId'] ?>',
				favoritesValue: <?=($arResult["FAVORITES"] ? 'true' : 'false')?>,
				canInitiate: <?=($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				canProcessRequestsIn: <?=(($arResult["CurrentUserPerms"]["UserCanProcessRequestsIn"] ?? null) && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				canModify: <?=($arResult["CurrentUserPerms"]["UserCanModifyGroup"] ? 'true' : 'false')?>,
				canModerate: <?=($arResult["CurrentUserPerms"]["UserCanModerateGroup"] ? 'true' : 'false')?>,
				hideArchiveLinks: <?=($arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				containerNodeId: 'socialnetwork-group-card-box',
				subscribeButtonNodeId: 'group_card_subscribe_button',
				menuButtonNodeId: 'group_card_menu_button',
				sliderMenuNodeId: '<?= CUtil::JSEscape((string) ($arParams['SLIDER_MENU_CONTAINER_ID'] ?? '')) ?>',
				styles: {
					tags: {
						box: 'socialnetwork-group-tag-box',
						item: 'socialnetwork-group-tag'
					},
					users: {
						box: 'socialnetwork-group-user-box',
						item: 'socialnetwork-group-user'
					},
				},
				urls: {
					groupsList: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_GROUPS_LIST"]) ?>'
				},
				editFeaturesAllowed: <?= (Helper\Workgroup::getEditFeaturesAvailability() ? 'true' : 'false') ?>,
				copyFeatureAllowed: <?=(Helper\Workgroup::isGroupCopyFeatureEnabled() ? 'true' : 'false')?>,

				themePickerData: <?= CUtil::phpToJSObject($arResult['themePickerData']) ?>,
				tasksEfficiency: {
					available: <?= ($arResult['TASKS_EFFICIENCY'] !== null ? 'true' : 'false') ?>,
					value: <?= (int)$arResult['TASKS_EFFICIENCY'] ?>,
				}
			})
		});

		BX.message({
			SGCSPathToGroupTag: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_GROUP_TAG"]) ?>',
			SGCSPathToUserProfile: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_USER"]) ?>',
			SGCSWaitTitle: '<?= GetMessageJS("SONET_C6_CARD_WAIT") ?>'
		});
	</script><?php

	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
	$classList = [
		'pagetitle-menu-visible',
		'no-background',
		'socialnetwork-group-card-page',
	];
	$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . implode(' ', $classList));
	include('title_buttons.php');

	$projectType = (
		isset($arResult['Types'][$arResult['groupTypeCode']])
			? $arResult['Types'][$arResult['groupTypeCode']]['NAME']
			: ''
	);

	if ($arResult['IS_IFRAME'])
	{
		Toolbar::deleteFavoriteStar();

		$avatarStyle = (
			!empty($arResult['Group']['IMAGE_ID_FILE']['SRC'])
				? 'style="background:url(\'' . Uri::urnEncode($arResult['Group']['IMAGE_ID_FILE']['SRC']) . '\') no-repeat center center; background-size: cover;"'
				: ''
		);

		$avatarClassList = [];

		if (
			empty($arResult['Group']['IMAGE_ID_FILE']['SRC'])
			&& !empty($arResult['Group']['AVATAR_TYPE'])
		)
		{
			$avatarClassList[] = 'sonet-common-workgroup-avatar';
			$avatarClassList[] = '--' . Helper\Workgroup::getAvatarTypeWebCssClass($arResult['Group']['AVATAR_TYPE']);
		}
		else
		{
			$avatarClassList[] = 'ui-icon';
			$avatarClassList[] = 'ui-icon-common-user-group';
			$avatarClassList[] = 'socialnetwork-group-slider-title-avatar';
		}

		$projectTypeClassName = '';
		if (
			!empty($arResult['groupTypeCode'])
			&& isset($arResult['Types'][$arResult['groupTypeCode']])
		)
		{
			$projectTypeData = $arResult['Types'][$arResult['groupTypeCode']];

			if (
				isset($projectTypeData['EXTERNAL'])
				&& $projectTypeData['EXTERNAL'] === 'Y'
			)
			{
				$projectTypeClassName = '--extranet';
			}
			elseif (
				$projectTypeData['VISIBLE'] === 'N'
				&& $projectTypeData['OPENED'] === 'N'
			)
			{
				$projectTypeClassName = '--secret';
			}
			elseif (
				$projectTypeData['VISIBLE'] === 'Y'
				&& $projectTypeData['OPENED'] === 'N'
			)
			{
				$projectTypeClassName = '--lock';
			}
			else
			{
				$projectTypeClassName = '--open';
			}
		}

		Toolbar::addBeforeTitleHtml('<span class="socialnetwork-group-slider-title-container">' .
			'<span class="' . implode(' ', $avatarClassList) . '"><i ' . $avatarStyle . '></i></span>' .
			'<div class="socialnetwork-group-slider-title-name-box">' .
				'<span class="socialnetwork-group-slider-title-name-container">' .
					'<span class="socialnetwork-group-slider-title-name">' .
						$arResult['Group']['NAME'] .
					'</span>' .
					(
						$arResult['CurrentUserPerms']['UserCanModifyGroup']
							? '<a href="' . htmlspecialcharsbx($arResult['Urls']['Edit'] . (
								mb_strpos($arResult['Urls']['Edit'], '?') !== false
									? '&'
									: '?'
								) . 'tab=edit') . '" class="socialnetwork-group-slider-title-edit-icon"></a>'
							: ''
					) .
				'</span>' . // profile-menu-name
				'<div class="socialnetwork-group-slider-title-type">' .
					'<span class="socialnetwork-group-slider-title-type-name">' .
						'<span class="socialnetwork-group-slider-title-type-name-item ' . $projectTypeClassName . '">' .
							htmlspecialcharsEx($projectType) .
						'</span>' .
					'</span>' .
				'</div>' .
			'</div>' .
		'</span>');
	}

	$this->SetViewTarget('below_pagetitle');

	$aboutTitle = Loc::getMessage('SONET_C6_CARD_MENU_ABOUT');
	if ($arResult['isScrumProject'])
	{
		$aboutTitle = Loc::getMessage('SONET_C6_CARD_MENU_ABOUT_SCRUM');
	}
	elseif ($arResult['Group']['PROJECT'] === 'Y')
	{
		$aboutTitle = Loc::getMessage('SONET_C6_CARD_MENU_ABOUT_PROJECT');
	}

	$menuTabs = [
		[
			'ID' => 'about',
			'TEXT' => $aboutTitle,
			'IS_ACTIVE' => true,
		],
		[
			'ID' => 'members',
			'TEXT' => Loc::getMessage('SONET_C6_CARD_MENU_MEMBERS'),
			'ON_CLICK' => '',
			'URL' => $arResult['Urls']['GroupUsers'],
		]
	];

	if ($arResult['CurrentUserPerms']['UserCanModifyGroup'])
	{
		$menuTabs[] = [
			'ID' => 'rights',
			'TEXT' => Loc::getMessage('SONET_C6_CARD_MENU_ROLES'),
			'ON_CLICK' => '',
			'URL' => $arResult['Urls']['Features'],
		];
	}

	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.buttons",
		"",
		[
			"ID" => 'socialnetwork_group_menu',
			"ITEMS" => $menuTabs,
			"EDIT_MODE" => false,
		]
	);
	$this->EndViewTarget();

	$projectDescription = \Bitrix\Main\Text\Emoji::decode($arResult['Group']['~DESCRIPTION']);
	if ((string)$projectDescription === '')
	{
		$projectDescription = (
			isset($arResult['Types'][$arResult['groupTypeCode']])
				? $arResult['Types'][$arResult['groupTypeCode']]['DESCRIPTION2']
				: ''
		);
	}
	?>
	<div class="socialnetwork-group-slider-wrap" id="socialnetwork-group-card-box">
		<div class="socialnetwork-group-slider-col">
			<div class="ui-slider-section">
				<?php
				$style = (
					!empty($arResult['themePickerData'])
						? 'background-image: url("' . $arResult['themePickerData']['previewImage'] . '"); background-color: ' . ($arResult['themePickerData']['previewColor'] ?? '') . ';'
						: ''
				);
				?>
				<div style="<?= htmlspecialcharsbx($style) ?>" class="socialnetwork-group-slider-theme-box">
					<div class="socialnetwork-group-skeleton-btn"></div>
					<?php
					if (
						$arResult['CurrentUserPerms']['UserCanModifyGroup']
						&& $arResult['IS_IFRAME']
					)
					{
						?><button class="socialnetwork-group-slider-theme-btn"><?= Loc::getMessage('SONET_C6_CARD_CHANGE_THEME') ?></button><?php
					}

					$avatarClassList = [
						'sonet-common-workgroup-avatar',
						'socialnetwork-group-slider-group-logo-box',
					];
					if (empty($arResult['Group']['IMAGE_ID_FILE']['SRC']))
					{
						if (!empty($arResult['Group']['AVATAR_TYPE']))
						{
							$avatarClassList[] = '--' . Helper\Workgroup::getAvatarTypeWebCssClass($arResult['Group']['AVATAR_TYPE']);
						}
					}
					else
					{
						$avatarClassList[] = 'ui-icon';
						$avatarClassList[] = 'ui-icon-common-user-group';
					}

					$avatarStyle = (
						!empty($arResult['Group']['IMAGE_ID_FILE']['SRC'])
							? 'style="background: url(\'' . Uri::urnEncode($arResult['Group']['IMAGE_ID_FILE']['SRC']). '\') no-repeat center center; background-size: cover;"'
							: ''
					);

					?>
					<div class="<?= htmlspecialcharsbx(implode(' ', $avatarClassList)) ?>">
						<?php
						if ($arResult['CurrentUserPerms']['UserCanModifyGroup'])
						{
							?><span class="socialnetwork-group-slider-group-logo-btn"></span><?php
						}
						?>
						<i <?= $avatarStyle ?>></i>
					</div>
				</div>



				<div class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div class="socialnetwork-group-skeleton-line"></div>
				<div class="socialnetwork-group-skeleton-line"></div>
				<div class="socialnetwork-group-skeleton-line"></div>
				<div class="socialnetwork-group-skeleton-line"></div>
				<div class="socialnetwork-group-skeleton-line --line-short"></div>




				<div class="socialnetwork-group-slider-desc-editable">
					<?= htmlspecialcharsEx($projectDescription) ?>
					<?php
					if ($arResult['CurrentUserPerms']['UserCanModifyGroup'])
					{
						$uri = new Uri($arResult['Urls']['Edit']);
						$uri->addParams([
							'focus' => 'description',
						]);

						?><a href="<?= htmlspecialcharsbx($uri->getUri()) ?>" class="socialnetwork-group-slider-desc-editable-btn"></a><?php
					}
					?>
				</div>
			</div>
			<?php
			$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_TITLE');
			if ($arResult['isScrumProject'])
			{
				$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_TITLE_SCRUM');
			}
			elseif ($arResult['Group']['PROJECT'] === 'Y')
			{
				$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_TITLE_PROJECT');
			}
			?>
			<div class="ui-slider-section">
				<div class="socialnetwork-group-skeleton-line --line-title"></div>
				<div class="socialnetwork-group-skeleton-circle --user-main"></div>
				<div style="margin: 0 auto 13px auto; width: 192px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin: 0 auto 28px auto; width: 150px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="max-width: 110px; margin: 0 auto;" class="socialnetwork-group-skeleton-box">
					<div style="margin-right: 16px; width: 45px; height: 45px;" class="socialnetwork-group-skeleton-circle --user-main"></div>
					<div style="width: 45px; height: 45px;" class="socialnetwork-group-skeleton-circle --user-main"></div>
				</div>
				<div style="margin: 0 auto 9px auto; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin: 0 auto 34px auto; width: 158px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin: 0 auto 16px auto; width: 73px;" class="socialnetwork-group-skeleton-line --line-title"></div>
				<div style="margin: 0 auto; width: 158px;" class="socialnetwork-group-skeleton-line"></div>
				<div class="ui-slider-heading-4 socialnetwork-group-slider-title --text-center"><?= $membersTitle ?></div>
				<?php
				$ownerTitle = Loc::getMessage('SONET_C6_CARD_OWNER_SECTION_TITLE');
				if ($arResult['isScrumProject'])
				{
					$ownerTitle = Loc::getMessage('SONET_C6_CARD_OWNER_SECTION_TITLE_SCRUM');
				}
				elseif ($arResult['Group']['PROJECT'] === 'Y')
				{
					$ownerTitle = Loc::getMessage('SONET_C6_CARD_OWNER_SECTION_TITLE_PROJECT');
				}

				$ownerData = $arResult['Owner'];
				$ownerBackgroundStyle = (
					!empty($ownerData['USER_PERSONAL_PHOTO_FILE'])
					&& !empty($ownerData['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])
						? "background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($ownerData['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])) . "'); background-size: cover;"
						: ''
				);

				if (!$arResult['isScrumProject'])
				{
					?>
					<div class="socialnetwork-group-slider-member-box --main-member">
						<div class="socialnetwork-group-slider-member-position"><?= $ownerTitle?></div>
						<?php

						?>
						<a class="ui-icon ui-icon-common-user socialnetwork-group-slider-member-logo" href="<?= htmlspecialcharsbx($ownerData['USER_PROFILE_URL']) ?>">
							<i style="<?= $ownerBackgroundStyle ?>"></i>
						</a>
						<a class="socialnetwork-group-slider-member-name" href="<?= htmlspecialcharsbx($ownerData['USER_PROFILE_URL']) ?>"><?= $ownerData['NAME_FORMATTED'] ?></a>
						<a class="socialnetwork-group-slider-member-position" href="<?= htmlspecialcharsbx($ownerData['USER_PROFILE_URL']) ?>"><?= $ownerData['USER_WORK_POSITION'] ?></a>
					</div>
					<?php
				}
				else
				{
					$scrumMaster = $arResult['ScrumMaster'];

					$backgroundStyle = (
						!empty($scrumMaster['USER_PERSONAL_PHOTO_FILE'])
						&& !empty($scrumMaster['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])
							? "background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($scrumMaster['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])) . "'); background-size: cover;"
							: ''
					);

					?>
					<div class="socialnetwork-group-slider-member-box --sub-member__space-between">
						<div class="socialnetwork-group-slider-member-logo-box">
							<div class="socialnetwork-group-slider-member-position"><?= Loc::getMessage('SONET_C6_CARD_SCRUMMASTER_TITLE') ?></div>
							<a
							 class="ui-icon ui-icon-common-user socialnetwork-group-slider-member-logo"
							 href="<?= htmlspecialcharsbx($scrumMaster['USER_PROFILE_URL']) ?>"
							 title="<?= $scrumMaster['NAME_FORMATTED'] ?>">
								<i style="<?= $backgroundStyle ?>"></i>
							</a>
						</div>
						<div class="socialnetwork-group-slider-member-logo-box">
							<div class="socialnetwork-group-slider-member-position"><?= $ownerTitle ?></div>
							<a
							 class="ui-icon ui-icon-common-user socialnetwork-group-slider-member-logo"
							 href="<?= htmlspecialcharsbx($ownerData['USER_PROFILE_URL']) ?>"
							 title="<?= $ownerData['NAME_FORMATTED'] ?>">
								<i style="<?= $ownerBackgroundStyle ?>"></i>
							</a>
						</div>
					</div>
					<?php
				}

				if (
					is_array($arResult['Moderators']['List'])
					&& !empty($arResult['Moderators']['List'])
				)
				{
					$moderatorsTitle = Loc::getMessage('SONET_C6_CARD_MODERATORS_SECTION_TITLE');
					if ($arResult['isScrumProject'])
					{
						$moderatorsTitle = Loc::getMessage('SONET_C6_CARD_MODERATORS_SECTION_TITLE_SCRUM');
					}
					elseif ($arResult['Group']['PROJECT'] === 'Y')
					{
						$moderatorsTitle = Loc::getMessage('SONET_C6_CARD_MODERATORS_SECTION_TITLE_PROJECT');
					}

					$logoMode = (count($arResult['Moderators']['List']) <= $arParams['USER_LIMIT']);

					$classList = [
						'socialnetwork-group-slider-member-box',
					];
					if ($logoMode)
					{
						$classList[] = '--sub-member';
					}
					else
					{
						$classList[] = '--team-member';
						$classList[] = '--team-moderator';
					}

					?>
					<div class="<?= implode(' ', $classList) ?>">
						<div class="socialnetwork-group-slider-member-position"><?= $moderatorsTitle?></div>
						<?php
						ob_start();

						$className = (
							$logoMode
								? 'socialnetwork-group-slider-member-logo-box'
								: 'socialnetwork-group-slider-member-team-box'
						);
						?>
						<div class="<?= $className ?>">
							<?php

							$counter = 0;
							foreach ($arResult['Moderators']['List'] as $moderator)
							{
								if ($counter >= $arParams['USER_LIMIT'])
								{
									break;
								}

								$backgroundStyle = (
									!empty($moderator['USER_PERSONAL_PHOTO_FILE'])
									&& !empty($moderator['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])
										? "background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($moderator['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])) . "'); background-size: cover;"
										: ''
								);

								?>
								<a
								 href="<?= htmlspecialcharsbx($moderator['USER_PROFILE_URL']) ?>"
								 class="ui-icon ui-icon-common-user socialnetwork-group-slider-member-logo"
								 title="<?= $moderator['NAME_FORMATTED'] ?>">
									<i style="<?= $backgroundStyle ?>"></i>
								</a>
								<?php
								$counter++;
							}

							if (!$logoMode)
							{
								?>
								<div class="socialnetwork-group-slider-member-team-count">
									<span>+</span>
									<?= (count($arResult['Moderators']['List']) - $arParams['USER_LIMIT']) ?>
								</div>
								<?php
							}

							?>
						</div>
						<?php
						if (!$logoMode && $arResult['CurrentUserPerms']['UserCanModifyGroup'])
						{
							$uri = new Uri($arResult['Urls']['Edit']);
							$uri->addParams([
								'focus' => 'addModerator',
							]);

							?><a href="<?= htmlspecialcharsbx($uri->getUri()) ?>" class="socialnetwork-group-slider-member-team-btn"></a><?php
						}

						$usersContent = ob_get_clean();

						if ($logoMode)
						{
							?>
							<div class="socialnetwork-group-slider-member-logo-box"><?= $usersContent ?></div>
							<?php
						}
						else
						{
							?>
							<div class="socialnetwork-group-slider-member-team-wrap"><?= $usersContent ?></div>
							<?php
						}
						?>
					</div><?php
				}

				if (
					is_array($arResult['Members']['List'])
					&& !empty($arResult['Members']['List'])
				)
				{
					$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_SECTION_TITLE');
					if ($arResult['isScrumProject'])
					{
						$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_SECTION_TITLE_SCRUM');
					}
					elseif ($arResult['Group']['PROJECT'] === 'Y')
					{
						$membersTitle = Loc::getMessage('SONET_C6_CARD_MEMBERS_SECTION_TITLE_PROJECT');
					}

					$logoMode = (
						(count($arResult['Members']['List']) <= $arParams['USER_LIMIT'])
						&& false
					);

					$classList = [
						'socialnetwork-group-slider-member-box',
					];
					if ($logoMode)
					{
						$classList[] = '--sub-member';
					}
					else
					{
						$classList[] = '--team-member';
					}

					?>
					<div class="<?= implode(' ', $classList) ?>">
						<div class="socialnetwork-group-slider-member-position"><?= $membersTitle ?></div>
						<?php
						ob_start();

						$className = (
							$logoMode
								? 'socialnetwork-group-slider-member-logo-box'
								: 'socialnetwork-group-slider-member-team-box'
						);
						?>
						<div class="<?= $className ?>">
							<?php

							$counter = 0;
							foreach ($arResult['Members']['List'] as $member)
							{
								if ($counter >= $arParams['USER_LIMIT'])
								{
									break;
								}

								$backgroundStyle = (
									!empty($member['USER_PERSONAL_PHOTO_FILE'])
									&& !empty($member['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])
										? "background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($member['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'])) . "'); background-size: cover;"
										: ''
								);

								?>
								<a
								 href="<?= htmlspecialcharsbx($member['USER_PROFILE_URL']) ?>"
								 class="ui-icon ui-icon-common-user socialnetwork-group-slider-member-logo"
								 title="<?= $member['NAME_FORMATTED'] ?>">
									<i style="<?= $backgroundStyle ?>"></i>
								</a>
								<?php
								$counter++;
							}

							if (count($arResult['Members']['List']) > $arParams['USER_LIMIT'])
							{
								?>
								<div class="socialnetwork-group-slider-member-team-count">
									<span>+</span>
									<?= (count($arResult['Members']['List']) - $arParams['USER_LIMIT']) ?>
								</div>
								<?php
							}

							?>
						</div><?php

						if ($arResult['CurrentUserPerms']['UserCanInitiate'])
						{
							?><a href="<?= htmlspecialcharsbx($arResult['Urls']['Invite']) ?>" class="socialnetwork-group-slider-member-team-btn"></a><?php
						}

						$usersContent = ob_get_clean();

						if ($logoMode)
						{
							?>
							<div class="socialnetwork-group-slider-member-logo-box"><?= $usersContent ?></div>
							<?php
						}
						else
						{
							?>
							<div class="socialnetwork-group-slider-member-team-wrap"><?= $usersContent ?></div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
			{
				?>
				<div class="ui-slider-section">
					<div class="socialnetwork-group-skeleton-box">
						<div class="socialnetwork-group-skeleton-line --line-title"></div>
						<div class="socialnetwork-group-skeleton-line --line-title"></div>
					</div>
					<?php
					$APPLICATION->IncludeComponent(
						"bitrix:intranet.apps",
						'',
						[]
					);
					?>
				</div>
				<?php
			}
			?>
		</div>
		<div class="socialnetwork-group-slider-col">
			<?php
			if (isset($arResult['TASKS_EFFICIENCY']))
			{
				$sectionTitle = Loc::getMessage('SONET_C6_CARD_EFFICIENCY_SECTION_TITLE');
				if ($arResult['Group']['PROJECT'] === 'Y')
				{
					$sectionTitle = Loc::getMessage('SONET_C6_CARD_EFFICIENCY_SECTION_TITLE_PROJECT');
				}
				?>
				<div class="ui-slider-section">
					<div class="socialnetwork-group-skeleton-line --line-title"></div>
					<div class="socialnetwork-group-skeleton-circle"></div>
					<div style="margin-left: auto;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
					<div class="ui-slider-heading-4 socialnetwork-group-slider-title --text-center"><?= $sectionTitle ?></div>
					<div class="socialnetwork-group-slider-efficency"></div>
					<div class="socialnetwork-group-slider-container-help">
						<span data-role="efficiency-helper"><?= Loc::getMessage('SONET_C6_CARD_EFFICIENCY_HELPER_CAPTION') ?></span>
					</div>
				</div>
				<?php
			}
			?>
			<div class="ui-slider-section">
				<div style="margin: 7px 0 50px 0;" class="socialnetwork-group-skeleton-line --line-title"></div>
				<div style="margin-bottom: 9px; width: 184px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin-bottom: 41px; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin-bottom: 9px; width: 184px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin-bottom: 41px; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin-bottom: 9px; width: 184px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin-bottom: 41px; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin-bottom: 9px; width: 184px" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin-bottom: 41px; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div style="margin-bottom: 9px; width: 184px;" class="socialnetwork-group-skeleton-line --line-subtitle"></div>
				<div style="margin-bottom: 41px; width: 216px;" class="socialnetwork-group-skeleton-line"></div>
				<div class="ui-slider-heading-box">
					<div class="ui-slider-heading-main">
						<?php
						$sectionTitle = Loc::getMessage('SONET_C6_CARD_PROPERTIES_SECTION_TITLE');
						if ($arResult['isScrumProject'])
						{
							$sectionTitle = Loc::getMessage('SONET_C6_CARD_PROPERTIES_SECTION_TITLE_SCRUM');
						}
						elseif ($arResult['Group']['PROJECT'] === 'Y')
						{
							$sectionTitle = Loc::getMessage('SONET_C6_CARD_PROPERTIES_SECTION_TITLE_PROJECT');
						}
						?>
						<div class="ui-slider-heading-4 socialnetwork-group-slider-title"><?= $sectionTitle ?></div>
					</div>
					<?php
					if ($arResult['CurrentUserPerms']['UserCanModifyGroup'])
					{
						?>
						<div class="ui-slider-heading-rest">
							<a href="<?= htmlspecialcharsbx($arResult['Urls']['Edit']) ?>" class="ui-slider-link"><?= Loc::getMessage('SONET_C6_CARD_ACTION_LINK_EDIT') ?></a>
						</div>
						<?php
					}
					?>
				</div>
				<div class="socialnetwork-group-slider-settings-wrap">
					<?php

					if (
						!$arResult['isScrumProject']
						&& is_array($arResult['Subjects'])
						&& count($arResult['Subjects']) > 1
					)
					{
						?>
						<div class="socialnetwork-group-slider-settings-box">
							<div class="socialnetwork-group-slider-settings-caption"><?= Loc::getMessage('SONET_C6_CARD_SUBJECT')?></div>
							<div class="socialnetwork-group-slider-settings-text"><?= $arResult['Group']['SUBJECT_NAME'] ?></div>
						</div>
						<?php
					}

					$value = CComponentUtil::getDateTimeFormatted([
						'TIMESTAMP' => MakeTimeStamp($arResult['Group']['DATE_CREATE']),
						'TZ_OFFSET' => CTimeZone::getOffset()
					]);
					?>
					<div class="socialnetwork-group-slider-settings-box">
						<div class="socialnetwork-group-slider-settings-caption"><?= Loc::getMessage('SONET_C6_CREATED')?></div>
						<div class="socialnetwork-group-slider-settings-text"><?= $value ?></div>
					</div>
					<?php

					if ($arResult['Group']['PROJECT'] === 'Y')
					{
						$dateStart = (
							!empty($arResult['Group']['PROJECT_DATE_START'])
								? FormatDateFromDB($arResult['Group']['PROJECT_DATE_START'], $arParams['DATE_FORMAT'], true)
								: ''
						);
						$dateFinish = (
							!empty($arResult['Group']['PROJECT_DATE_FINISH'])
								? FormatDateFromDB($arResult['Group']['PROJECT_DATE_FINISH'], $arParams['DATE_FORMAT'], true)
								: ''
						);

						if ($dateStart !== '' || $dateFinish !== '')
						{
							?>
							<div class="socialnetwork-group-slider-settings-box">
								<div class="socialnetwork-group-slider-settings-caption"><?= Loc::getMessage('SONET_C6_CARD_PROJECT_TERM_SECTION_TITLE')?></div>
								<div class="socialnetwork-group-slider-settings-text"><?= $dateStart ?> - <?= $dateFinish ?></div>
							</div>
							<?php
						}
					}

					if (
						$arResult['isScrumProject']
						&& (string)$arResult['Group']['SCRUM_TASK_RESPONSIBLE'] !== ''
					)
					{
						?>
						<div class="socialnetwork-group-slider-settings-box">
							<div class="socialnetwork-group-slider-settings-caption"><?= Loc::getMessage('SONET_C6_CARD_SCRUM_TASK_RESPONSIBLE_TITLE') ?></div>
							<div class="socialnetwork-group-slider-settings-text"><?= Loc::getMessage('SONET_C6_CARD_SCRUM_TASK_RESPONSIBLE_VALUE_' . $arResult['Group']['SCRUM_TASK_RESPONSIBLE']) ?></div>
						</div>
						<?php
					}

					if (
						is_array($arResult['Group']['KEYWORDS_LIST'])
						&& !empty($arResult['Group']['KEYWORDS_LIST'])
					)
					{
						?>
						<div class="socialnetwork-group-slider-settings-box">
							<div class="socialnetwork-group-slider-settings-caption"><?= Loc::getMessage('SONET_C6_TAGS') ?></div>
							<?php
							foreach($arResult['Group']['KEYWORDS_LIST'] as $keyword)
							{
								$link = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_GROUP_TAG'], [
									'tag' => $keyword
								]);
								?>
								<a href="<?= $link ?>" bx-tag-value="<?=$keyword?>" class="ui-slider-link socialnetwork-group-slider-settings-text">#<?= $keyword ?></a>
								<?php
							}
							?>
						</div>
						<?php
					}

					foreach ($arResult['GROUP_PROPERTIES'] as $field => $userFieldFata)
					{

						if (
							$userFieldFata['VALUE'] === NULL
							|| $userFieldFata['VALUE'] === ''
							|| $userFieldFata['VALUE'] === []
						)
						{
							continue;
						}

						$userFieldRenderedValue = (new UserField\Renderer($userFieldFata, [
							'mode' => UserField\Types\BaseType::MODE_VIEW,
						]))->render();

						?>
						<div class="socialnetwork-group-slider-settings-box">
							<div class="socialnetwork-group-slider-settings-caption"><?= htmlspecialcharsEx($userFieldFata['LIST_COLUMN_LABEL']) ?></div>
							<div class="socialnetwork-group-slider-settings-text"><?= $userFieldRenderedValue ?></div>
						</div>
						<?php
					}

					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
