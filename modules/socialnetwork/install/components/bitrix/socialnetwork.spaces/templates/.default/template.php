<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */
/** @var array $componentParams */
/** @var array $listComponentParams */
/** @var array $menuComponentParams */
/** @var array $toolbarComponentParams */
/** @var array $contentComponentParams */
/** @var bool $includeToolbar */

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.buttons',
	'ui.icon-set.actions',
	'ui.icon-set.crm',
	'socialnetwork.membership-request-panel',
]);

\Bitrix\Main\Loader::includeModule('socialnetwork');

if (!Context::getCurrent()->getRequest()->get('IFRAME'))
{
	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
	$APPLICATION->SetPageProperty(
		'BodyClass', ($bodyClass ? $bodyClass . ' ' : '')
		. 'no-all-paddings no-background sn-spaces__body'
	);
}

$canInvite = false;
$spaceNotFoundOrCantSee = false;
if ($componentParams['PAGE_TYPE'] === 'group')
{
	$permissions = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions(
		['groupId' => $componentParams['GROUP_ID']]
	);

	if (!$permissions['UserCanViewGroup'])
	{
		$spaceNotFoundOrCantSee = true;
	}

	$permissionsMap = [
		'edit' => $permissions['UserCanModifyGroup'],
		'delete' => $permissions['UserCanModifyGroup'],
		'invite' => $permissions['UserCanInitiate'],
		'join' => (
			!$permissions['UserIsMember']
			&& !$permissions['UserRole']
		),
		'leave' => (
			$permissions['UserIsMember']
			&& !$permissions['UserIsAutoMember']
			&& !$permissions['UserIsOwner']
			&& !$permissions['UserIsScrumMaster']
		),
	];

	$canInvite = ($permissionsMap['edit'] || $permissionsMap['invite']);
	if ($canInvite && \Bitrix\Main\Loader::includeModule('pull'))
	{
		\CPullWatch::add(
			$componentParams['USER_ID'],
			\Bitrix\Socialnetwork\Internals\EventService\Push\PullDictionary::PULL_WORKGROUPS_TAG,
			true
		);
	}
}

?>

<div
	class="sn-spaces sn-spaces__scope <?= $arResult['IS_LIST_DEPLOYED'] === 'collapsed' ? '--list-collapsed-mode' : ''?>"
	id="sn-spaces__content"
>
	<div class="sn-spaces__list" id="sn-spaces-list">
		<?php
			if (!Context::getCurrent()->getRequest()->get('IFRAME')):
				$APPLICATION->includeComponent(
					'bitrix:socialnetwork.spaces.list',
					'',
					$listComponentParams,
				);
			endif;
		?>
	</div>

	<?php if ($spaceNotFoundOrCantSee): ?>
		<div class="sn-spaces-not-found-error">
			<div class="sn-spaces-not-found-error-image"></div>
			<div class="sn-spaces-not-found-error-title"><?= Loc::getMessage('SN_SPACES_NOT_FOUND_ERROR_TITLE') ?></div>
			<div class="sn-spaces-not-found-error-text"><?= Loc::getMessage('SN_SPACES_NOT_FOUND_ERROR_TEXT') ?></div>
		</div>
	<?php return; endif; ?>

	<div class="sn-spaces__navigation">
		<?php
			if (!Context::getCurrent()->getRequest()->get('IFRAME')):
				$APPLICATION->includeComponent(
					'bitrix:socialnetwork.spaces.menu',
					'',
					$menuComponentParams,
				);
			endif;
		?>
	</div>
	<div class="sn-spaces__wrapper">
		<script>
			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onInit', (event) => {
				const data = event.getCompatData();
				let bindElement = data[1];
				const params = data[2];

				if (
					!BX.type.isElementNode(params.targetContainer)
					&& BX.type.isElementNode(bindElement)
				)
				{
					const contentContainer = document
						.getElementById('sn-spaces__content')
						.querySelector('.sn-spaces__wrapper')
					;
					if (contentContainer.contains(bindElement))
					{
						params.targetContainer = contentContainer;
					}
				}
			});
		</script>

		<?php if ($canInvite): ?>
			<div id="sn-spaces-membership-request-panel"></div>
		<?php endif; ?>

		<?php
			if (
				!Context::getCurrent()->getRequest()->get('IFRAME')
				&& $includeToolbar === true
			):
		?>
			<div class="sn-spaces__toolbar-space">
				<?php
					$APPLICATION->includeComponent(
						'bitrix:socialnetwork.spaces.toolbar',
						'',
						$toolbarComponentParams,
					);
				?>
			</div>
		<?php endif; ?>

		<div class="sn-spaces__content">
			<?php
				$APPLICATION->includeComponent(
					'bitrix:socialnetwork.spaces.content',
					'',
					$contentComponentParams,
				);
			?>
		</div>
	</div>
</div>

<script>
	BX.ready(function()
	{
		<?php if ($canInvite): ?>
			const membershipRequestPanel = new BX.Socialnetwork.MembershipRequestPanel({
				groupId: '<?= (int) $componentParams['GROUP_ID'] ?>',
				pathToUser: '<?=CUtil::JSescape($arResult['PATH_TO_USER'])?>',
				pathToUsers: '<?=
					CUtil::JSescape(
						CComponentEngine::makePathFromTemplate(
							$arResult['PATH_TO_GROUP_USERS'],
							['group_id' => $componentParams['GROUP_ID']]
						)
					)
				?>',
			});

			membershipRequestPanel.renderTo(
				document.getElementById('sn-spaces-membership-request-panel')
			);
		<?php endif; ?>
	});
</script>
