<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var array $componentParams */
/** @var array $menuComponentParams */
/** @var array $toolbarComponentParams */
/** @var array $contentComponentParams */
/** @var bool $includeToolbar */
/** @var int $groupId */
/** @var int $userId */
/** @var bool $canInvite */
/** @var bool $spaceNotFoundOrCantSee */
/** @var \Bitrix\Main\Web\Uri $uri */

use Bitrix\Main\Localization\Loc;

CJSCore::Init();

$siteTemplateId = (defined('SITE_TEMPLATE_ID') ? SITE_TEMPLATE_ID  : 'def');

$themePicker = new \Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker(
	$siteTemplateId,
	false,
	$userId,
);

$bodyClass = 'no-all-paddings no-background sn-spaces__body sn-spaces__body-frame';
$bodyClass .= ' template-' . $siteTemplateId;
if ($arResult['SHOW_BITRIX24_THEME'] === 'Y')
{
	$bodyClass .= ' bitrix24-' . $themePicker->getCurrentBaseThemeId() . '-theme';
}

?>

<!DOCTYPE html>

<html
	xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID ?>"
	lang="<?=LANGUAGE_ID ?>"
>

<head>
	<?php $APPLICATION->ShowHead(); ?>
	<title><?php $APPLICATION->ShowTitle(); ?></title>

	<?php if ($arResult['SHOW_BITRIX24_THEME'] === 'Y'): ?>
		<?php $themePicker->showHeadAssets(); ?>
	<?php endif; ?>

</head>

<body class="<?= $bodyClass ?>">

<?php if ($arResult['SHOW_BITRIX24_THEME'] === 'Y'): ?>
	<?php $themePicker->showBodyAssets(); ?>
<?php endif; ?>

<script>
	const needProcessOverlay = (popup) => {
		return (
			popup
			&& popup.overlay
			&& popup.overlay.element
			&& top.BX.Socialnetwork.Spaces.space
		);
	};
	BX.Event.EventEmitter.subscribe('BX.Main.Popup:onShow', (event) => {
		const [popup] = event.getCompatData();
		if (needProcessOverlay(popup))
		{
			top.BX.Socialnetwork.Spaces.space.showOverlay(
				popup.getId(),
				popup.overlay.element
			);
		}
	});
	BX.Event.EventEmitter.subscribe('BX.Main.Popup:onClose', (event) => {
		const [popup] = event.getCompatData();
		if (needProcessOverlay(popup))
		{
			top.BX.Socialnetwork.Spaces.space.hideOverlay(popup.getId());
		}
	});
	BX.Event.EventEmitter.subscribe('BX.Main.Popup:onDestroy', (event) => {
		const [popup] = event.getCompatData();
		if (needProcessOverlay(popup))
		{
			top.BX.Socialnetwork.Spaces.space.hideOverlay(popup.getId());
		}
	});

	let simulatedClick = false;
	BX.Event.bind(window.document, 'click', () => {
		if (simulatedClick)
		{
			simulatedClick = false;
			return;
		}
		simulatedClick = true;
		top.document.body.click();
	});
	BX.Event.bind(top.document, 'click', () => {
		if (simulatedClick)
		{
			simulatedClick = false;
			return;
		}
		simulatedClick = true;
		window.document.body.click();
	});
</script>

<div
	id="sn-spaces__content"
	class="sn-spaces-frame sn-spaces__scope"
>
	<?php if ($spaceNotFoundOrCantSee): ?>
		<div class="sn-spaces-not-found-error">
			<div class="sn-spaces-not-found-error-image"></div>
			<div class="sn-spaces-not-found-error-title">
				<?= Loc::getMessage('SN_SPACES_NOT_FOUND_ERROR_TITLE') ?>
			</div>
			<div class="sn-spaces-not-found-error-text">
				<?= Loc::getMessage('SN_SPACES_NOT_FOUND_ERROR_TEXT') ?>
			</div>
		</div>
	<?php return; endif; ?>

	<div class="sn-spaces__navigation">
		<?php
		$APPLICATION->includeComponent(
			'bitrix:socialnetwork.spaces.menu',
			'',
			$menuComponentParams,
		);
		?>
	</div>

	<div class="sn-spaces__wrapper">

		<?php if ($canInvite): ?>
			<div
				id="sn-spaces-membership-request-panel"
				class="sn-spaces__membership-request-panel"
			></div>
		<?php endif; ?>

		<?php if ($includeToolbar === true):?>
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

		<script>

			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onInit', (event) => {
				const data = event.getCompatData();

				const bindElement = data[1];
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

			BX.ready(function()
			{
				if (top.BX.Socialnetwork.Spaces.space)
				{
					top.BX.Socialnetwork.Spaces.space.setParams({
						pageId: '<?= $componentParams['PAGE_ID'] ?>',
						pageView: '<?= $arResult['pageView'] ?>',
						contentUrl: '<?= CUtil::JSescape($uri->getUri()) ?>',
						userId: '<?= (int) $userId ?>',
						groupId: '<?= (int) $groupId ?>',
					});
				}

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

	</div>
</div>

</body>

</html>
