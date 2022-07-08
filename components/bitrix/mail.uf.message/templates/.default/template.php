<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$uf = $arParams['USER_FIELD'];
$message = $arResult['MESSAGE'];

$datetimeFormat = \Bitrix\Main\Loader::includeModule('intranet') ? \CIntranetUtils::getCurrentDatetimeFormat() : false;
$datetimeFormatted = \CComponentUtil::getDateTimeFormatted(
	$message['FIELD_DATE']->getTimestamp()+\CTimeZone::getOffset(),
	$datetimeFormat,
	\CTimeZone::getOffset()
);
$readDatetimeFormatted = !empty($message['READ_CONFIRMED']) && $message['READ_CONFIRMED']
	? \CComponentUtil::getDateTimeFormatted(
		$message['READ_CONFIRMED']->getTimestamp()+\CTimeZone::getOffset(),
		$datetimeFormat,
		\CTimeZone::getOffset()
	) : null;

\Bitrix\Main\UI\Extension::load('ui.design-tokens');
?>

<div class="mail-uf-message-wrapper">

	<? if ('view' == $arParams['MODE']): ?>
		<? if ($message['__thread_new'] > 0): ?>
			<a class="mail-uf-message-counter" data-slider-ignore-autobinding="true"
				href="<?=htmlspecialcharsbx($message['__href']) ?>"
				onclick="return BXMailUfMessageHelper.openMessage(this.href); ">
				<span class="mail-uf-message-counter-icon"></span>
				+<?=intval($message['__thread_new']) ?>
			</a>
		<? endif ?>
	<? endif ?>

	<div class="mail-uf-message-h"><?=Loc::getMessage('MAIL_UF_MESSAGE_H') ?></div>
	<div class="mail-uf-message-date"><?

		echo Loc::getMessage(
			$message['__is_outcome'] ? 'MAIL_UF_MESSAGE_SENT' : 'MAIL_UF_MESSAGE_RECEIVED',
			array('#DATETIME#' => $datetimeFormatted)
		);

		if ($message['__is_outcome'] && $message['OPTIONS']['trackable'])
		{
			echo ', ', empty($readDatetimeFormatted)
				? Loc::getMessage('MAIL_UF_MESSAGE_READ_AWAITING')
				: Loc::getMessage('MAIL_UF_MESSAGE_READ_CONFIRMED', array('#DATETIME#' => $readDatetimeFormatted));
		}

	?></div>
	<div class="mail-uf-message-separator"></div>
	<div class="mail-uf-message-h"><?=htmlspecialcharsbx($message['SUBJECT']) ?></div>
	<div>
		<? $__from = reset($message['__from']); ?>
		<? if (!empty($__from['name']) && !empty($__from['email']) && $__from['name'] != $__from['email']): ?>
			<span class="mail-uf-message-rcpt"><?=htmlspecialcharsbx($__from['name']) ?></span>
		<? endif ?>
		<?=htmlspecialcharsbx($__from['email'] ?: $__from['name']) ?>
	</div>
	<div>
		<span class="mail-uf-message-rcpt"><?=Loc::getMessage('MAIL_UF_MESSAGE_RCPT') ?>:</span>
		<?=join(
			', ',
			array_map(
				function ($item)
				{
					return $item['email'] ?: $item['name'];
				},
				$message['__to']
			)
		) ?>
	</div>
	<div class="mail-uf-message-separator"></div>
	<div class="mail-uf-message-body"><?=htmlspecialcharsbx(mb_substr($message['BODY'], 0, 255)) ?></div>
	<div>
		<a class="mail-uf-message-body-expand" data-slider-ignore-autobinding="true"
			href="<?=htmlspecialcharsbx($message['__href']) ?>"
			onclick="return BXMailUfMessageHelper.openMessage(this.href); ">
			<?=Loc::getMessage('MAIL_UF_MESSAGE_BODY_EXPAND') ?>
		</a>
	</div>
	<? if ($message['OPTIONS']['attachments'] > 0 || $message['ATTACHMENTS'] > 0): ?>
		<div>
			<a class="mail-uf-message-files" data-slider-ignore-autobinding="true"
				href="<?=htmlspecialcharsbx($message['__href']) ?>"
				onclick="return BXMailUfMessageHelper.openMessage(this.href); ">
				<?=Loc::getMessage(
					'MAIL_UF_MESSAGE_ATTACHES',
					array(
						'#NUM#' => (int) ($message['ATTACHMENTS'] ?: $message['OPTIONS']['attachments'])
					)
				) ?>
			</a>
		</div>
	<? endif ?>
</div>

<? if ('edit' == $arParams['MODE']): ?>
	<input type="hidden" value="<?=intval($message['ID']) ?>"
		name="<?=htmlspecialcharsbx($uf['FIELD_NAME']) ?><? if ('Y' == $uf['MULTIPLE']) echo '[]'; ?>">
<? endif?>

<script type="text/javascript">

(function ()
{
	if (window.BXMailUfMessageHelper)
	{
		return;
	}

	var BXMailUfMessageHelper = {};

	BXMailUfMessageHelper.openMessage = function (href)
	{
		BX.SidePanel.Instance.open(
			href,
			{
				width: 1080,
				loader: 'view-mail-loader'
			}
		);

		return false;
	};

	window.BXMailUfMessageHelper = BXMailUfMessageHelper;
})();

</script>
