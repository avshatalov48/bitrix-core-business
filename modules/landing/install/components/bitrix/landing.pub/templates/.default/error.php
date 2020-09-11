<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Main\Localization\Loc;
\Bitrix\Main\UI\Extension::load(["ui.fonts.opensans", "ui.buttons"]);

foreach ($arResult['ERRORS'] as $errorCode => $error)
{
	break;
}

// try recognize link to group
if (
	$errorCode == 'SITE_NOT_ALLOWED' &&
	$arParams['TYPE'] == 'GROUP' &&
	isset($arResult['REAL_LANDING'])
)
{
	$landing = $arResult['REAL_LANDING'];
	/** @var $landing \Bitrix\Landing\Landing */
	\CBitrixComponent::includeComponentClass('bitrix:landing.socialnetwork.group_redirect');
	$groupId = \LandingSocialnetworkGroupRedirectComponent::getGroupIdBySiteId(
		$landing->getSiteId()
	);
	if ($groupId)
	{
		$groupPath = Connector\SocialNetwork::getTabUrl(
			$groupId,
			$landing->getPublicUrl(false, false)
		);
	}
}
\Bitrix\Landing\Manager::setPageView(
	'MainTag',
	'style="height: 100vh; background: #fff;"'
);
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
				<a href="#" id="landing-access-request" class="ui-btn ui-btn-primary"><?= Loc::getMessage('LANDING_TPL_ERROR_NOT_ALLOWED_ASK');?></a>
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
	<?elseif ($errorCode == 'SITE_NOT_ALLOWED' && $arParams['TYPE'] == 'GROUP'):?>
		<div class="landing-error-kb landing-error-kb-group">
			<div class="landing-error-kb-inner">
				<div class="landing-error-kb-title"><?= $error;?></div>
				<div class="landing-error-kb-img">
					<div class="landing-error-kb-img-inner"></div>
				</div>
				<div class="landing-error-kb-desc">
					<?= Loc::getMessage('LANDING_TPL_ERROR_NOT_ALLOWED_NOTE_GROUP', [
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
		<div class="landing-error-site-desc"><?= Loc::getMessage('LANDING_TPL_ERROR_NOT_FOUND_NOTE');?></div>
	</div>

<?endif;?>
