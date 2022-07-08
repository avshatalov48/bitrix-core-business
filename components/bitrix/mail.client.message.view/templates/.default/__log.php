<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

foreach ($list as $item)
{
	$datetimeFormat = \Bitrix\Main\Loader::includeModule('intranet') ? \CIntranetUtils::getCurrentDatetimeFormat() : false;
	$datetimeFormatted = \CComponentUtil::getDateTimeFormatted(
		$item['FIELD_DATE']->getTimestamp()+\CTimeZone::getOffset(),
		$datetimeFormat,
		\CTimeZone::getOffset()
	);
	$readDatetimeFormatted = !empty($item['READ_CONFIRMED']) && $item['READ_CONFIRMED']
		? \CComponentUtil::getDateTimeFormatted(
			$item['READ_CONFIRMED']->getTimestamp()+\CTimeZone::getOffset(),
			$datetimeFormat,
			\CTimeZone::getOffset()
		) : null;
	?>
	<div class="mail-msg-view-log-item mail-msg-view-logitem-<?=intval($item['ID']) ?>"
		data-id="<?=intval($item['ID']) ?>" data-log="<?=htmlspecialcharsbx($item['__log']) ?>">
		<span class="mail-msg-view-log-item-icon-<?=($item['__is_outcome'] ? 'outcome' : 'income') ?>"></span>
		<?php $__from = reset($item['__from']); ?>
		<span class="mail-msg-view-log-item-name"><?=htmlspecialcharsbx($__from['name'] ?: $__from['email']) ?></span>
		<span class="mail-msg-view-log-item-description"><?=htmlspecialcharsbx($item['SUBJECT']) ?></span>
		<span class="mail-msg-view-log-item-date mail-msg-view-log-item-date">
			<span class="mail-msg-view-log-item-date-short"><?=$datetimeFormatted ?></span>
			<span class="mail-msg-view-log-item-date-full">
				<?=Loc::getMessage(
					$item['__is_outcome'] ? 'MAIL_MESSAGE_SENT' : 'MAIL_MESSAGE_RECEIVED',
					array('#DATETIME#' => $datetimeFormatted)
				) ?><!--
				--><?php
				if ($item['OPTIONS']['trackable']): ?>,
					<span class="read-confirmed-datetime">
						<?php
						if (!empty($readDatetimeFormatted)): ?>
							<?=Loc::getMessage('MAIL_MESSAGE_READ_CONFIRMED', array('#DATETIME#' => $readDatetimeFormatted)) ?>
						<?php
						else: ?>
							<?=Loc::getMessage('MAIL_MESSAGE_READ_AWAITING') ?>
						<?php
						endif ?>
					</span>
				<?php
				endif ?>
			</span>
		</span>
	</div>
	<div class="mail-msg-view-details mail-msg-view-details-<?=intval($item['ID']) ?>"
		id="mail-msg-view-details-<?=intval($item['ID']) ?>"
		style="display: none; text-align: center; " data-id="<?=intval($item['ID']) ?>" data-empty="1">
		<div class="mail-msg-view-log-item-loading mail-msg-view-border-bottom"></div>
	</div>
	<?php
}
