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
/** @var int $groupId */
/** @var int $userId */
/** @var bool $spaceNotFoundOrCantSee */

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load([
	'ui.common',
	'ui.fonts.opensans',
	'ui.design-tokens',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.icon-set.actions',
	'ui.icon-set.crm',
	'socialnetwork.common',
	'socialnetwork.logo',
	'socialnetwork.membership-request-panel',
]);

$messages = Loc::loadLanguageFile(__FILE__);

$canInvite = false;
$spaceNotFoundOrCantSee = $arResult['spaceNotFoundOrCantSee'] ?? false;
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

$request = Context::getCurrent()->getRequest();

$isFrame = $request->get('IFRAME') === 'Y';
$isFilesNavigation = (
	$request->isAjaxRequest()
	&& $componentParams['PAGE_ID'] === 'files'
);
$isFrame = $isFilesNavigation || $isFrame;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
?>

<?php if ($isFrame): ?>

	<?php require_once __DIR__ . '/content.php'; ?>

<?php else: ?>

	<?php

	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');

	$APPLICATION->SetPageProperty(
		'BodyClass', ($bodyClass ? $bodyClass . ' ' : '')
		. 'no-all-paddings no-background sn-spaces__body'
	);
	?>

	<div
		class="sn-spaces sn-spaces__scope <?=
			$arResult['IS_LIST_DEPLOYED'] === 'collapsed'
				? '--list-collapsed-mode'
				: ''
		?>"
		id="sn-spaces__content"
	>
		<div class="sn-spaces__list" id="sn-spaces-list">
			<?php
			$APPLICATION->includeComponent(
				'bitrix:socialnetwork.spaces.list',
				'',
				$listComponentParams,
			);
			?>
		</div>

		<div id="sn-spaces-content" class="sn-spaces-content"></div>

	</div>

	<script>
		let space = null;
		Object.defineProperty(BX.Socialnetwork.Spaces, 'space', {
			enumerable: false,
			get: () => space
		});

		BX.ready(function() {
			BX.message(<?= Json::encode($messages) ?>);

			space = new BX.Socialnetwork.Spaces.Space({
				pageId: '<?= $componentParams['PAGE_ID'] ?>',
				pageView: '<?= $arResult['pageView'] ?>',
				contentUrl: '<?= CUtil::JSescape($uri->getUri()) ?>',
				userId: '<?= (int) $userId ?>',
				groupId: '<?= (int) $groupId ?>',
			});

			space.renderContentTo(document.getElementById('sn-spaces-content'));
		});
	</script>

<?php endif; ?>
