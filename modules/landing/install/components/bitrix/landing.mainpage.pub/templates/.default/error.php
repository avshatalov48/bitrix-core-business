<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var array $arParams */

foreach ($arResult['ERRORS'] as $errorCode => $error)
{
	break;
}

$groupPath = '';

// try to recognize link to group
if (
	($errorCode ?? null) == 'SITE_NOT_ALLOWED' &&
	$arParams['TYPE'] == 'GROUP' &&
	isset($arResult['REAL_LANDING'])
)
{
	$landing = $arResult['REAL_LANDING'];
	/** @var $landing \Bitrix\Landing\Landing */
	$groupId = \Bitrix\Landing\Site\Scope\Group::getGroupIdBySiteId($landing->getSiteId());
	if ($groupId)
	{
		$groupPath = Connector\SocialNetwork::getTabUrl(
			$groupId,
			$landing->getPublicUrl(false, false),
			true
		);
	}
}

\Bitrix\Landing\Manager::setPageView(
	'MainTag',
	'style="height: 100vh; background: #fff;"'
);

\Bitrix\Main\UI\Extension::load([
	'ui.fonts.opensans', 'ui.buttons'
]);
?>

<?if ($arParams['TYPE'] == Type::SCOPE_CODE_KNOWLEDGE || $arParams['TYPE'] == Type::SCOPE_CODE_GROUP):?>

	<?if ($errorCode == 'SITE_NOT_FOUND'):?>
		<div class="landing-error-kb">
			<div class="landing-error-kb-inner">
				<div class="landing-error-kb-title"><?= $error;?></div>
				<div class="landing-error-kb-img">
					<div class="landing-error-kb-img-inner"></div>
				</div>
				<div class="landing-error-kb-desc">
					<?= Loc::getMessage('LANDING_TPL_ERROR_NOT_FOUND_NOTE_KNOWLEDGE', [
						'#LINK1#' => '<a href="' . SITE_DIR . 'kb/" target="_top">',
						'#LINK2#' => '</a>',
					]);?>
				</div>
			</div>
		</div>
	<?elseif ($errorCode == 'SITE_NOT_ALLOWED' && $arParams['TYPE'] == Type::SCOPE_CODE_KNOWLEDGE):?>
		<div class="landing-error-kb landing-error-kb-group">
			<div class="landing-error-kb-inner">
				<div class="landing-error-kb-title"><?= $error;?></div>
				<div class="landing-error-kb-img">
					<div class="landing-error-kb-img-inner"></div>
				</div>
				<div class="landing-error-kb-desc"><?= Loc::getMessage('LANDING_TPL_ERROR_NOT_ALLOWED_NOTE_KNOWLEDGE');?></div>
				<?if (!\Bitrix\Landing\Connector\Mobile::isMobileHit()):?>
				<a href="#" id="landing-access-request" class="ui-btn ui-btn-primary"><?= Loc::getMessage('LANDING_TPL_ERROR_NOT_ALLOWED_ASK');?></a>
				<?endif?>
			</div>
		</div>
		<script>
			<?\CJSCore::init(['intranet_notify_dialog']);?>
			BX.ready(function()
			{
				var accessNotifyDialog = null;

				BX.bind(BX('landing-access-request'), 'click', function()
				{
					if (accessNotifyDialog === null)
					{
						accessNotifyDialog = new BX.Intranet.NotifyDialog({
							listUserData: <?= \CUtil::phpToJSObject(array_values($arResult['ADMINS']))?>,
							notificationHandlerUrl: BX.util.add_url_param(
								window.location.href,
								{
									action: 'AskAccess',
									sessid: BX.message('bitrix_sessid'),
									actionType: 'json'
								}
							),
							popupTexts: {
								sendButton: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_ACCESS_ASK_SEND_KNOWLEDGE'));?>',
								title: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_ACCESS_ASK_TITLE_KNOWLEDGE'));?>',
								header: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_ACCESS_ASK_HEADER_KNOWLEDGE'));?>',
								description: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_ACCESS_ASK_DESCRIPTION_KNOWLEDGE'));?>'
							}
						});
					}
					accessNotifyDialog.show();
				});
			});
		</script>
	<?elseif ($errorCode == 'SITE_NOT_ALLOWED' && $arParams['TYPE'] == Type::SCOPE_CODE_GROUP):?>
		<div class="landing-error-kb landing-error-kb-group">
			<div class="landing-error-kb-inner">
				<div class="landing-error-kb-title"><?= $error;?></div>
				<div class="landing-error-kb-img">
					<div class="landing-error-kb-img-inner"></div>
				</div>
				<div class="landing-error-kb-desc">
					<?= Loc::getMessage('LANDING_TPL_ERROR_NOT_ALLOWED_NOTE_2_GROUP', [
						'#LINK1#' => '<a href="' . $groupPath . '" target="_top">',
						'#LINK2#' => '</a>',
					]);?>
				</div>
			</div>
		</div>
	<?endif;?>

<?else: //sites and stores?>

	<!-- ico 'SITE was not found' -->
	<div class="landing-error-site">
		<div class="landing-error-site-img">
			<div class="landing-error-site-img-inner"></div>
		</div>
		<div class="landing-error-site-title"><?= $error;?></div>
		<div class="landing-error-site-desc">
			<?= Loc::getMessage('LANDING_TPL_ERROR_NOT_FOUND_NOTE', [
				'#LINK1#' => '<a href="' . ($arResult['SITE_URL'] ?? '/') . '">',
				'#LINK2#' => '</a>',
			]);?>
		</div>
	</div>

<?endif;?>

<script>
	(function()
	{
		if (window.location.hash.indexOf('#landingId') === 0)
		{
			window.location.href = BX.Uri.addParam(
				window.location.href,
				{
					forceLandingId: window.location.hash.substr(
						'#landingId'.length
					)
				}
			);
			window.location.hash = '';
		}
	})();
</script>