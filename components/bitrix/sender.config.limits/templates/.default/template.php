<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$containerId = 'sender-start-container';
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
Bitrix\Main\UI\Extension::load(
	[
		'ui.buttons',
		'ui',
		'ui.notification',
	]
);
$sendingStartTime = strtotime($arResult['SENDING_START']);
$sendingEndTime = strtotime($arResult['SENDING_END']);
?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-config-limits-wrap">

	<?php $counter = 0;?>
	<?php $active = false;?>
	<?php $defaultTab = null;?>
	<?foreach ($arResult['LIST'] as $item):?>
		<?php $active = false;?>
		<div class="sender-config-limits-box sender-type-tab" data-tab="<?=$item['CODE']?>">

			<?php
			if (0 == $counter++)
			{
				$active = true;
				$defaultTab = $item['CODE'];
			}

			$menuItems[] = [
				'NAME' => Loc::getMessage('SENDER_LIMIT_LEFT_MENU_' . mb_strtoupper($item['CODE'])  .'_LIMIT'),
				'ATTRIBUTES' => [
					'onclick' => "BX.Sender.Config.Limits.changeLeftMenuOption('".$item['CODE']."')",
				],
				'ACTIVE' => $active
			];
			?>

			<h4 class="sender-config-limits-title">
				<?=htmlspecialcharsbx($item['NAME'])?>

				<?if ($item['HELP_URI']):?>
					<span class="sender-config-limits-info">
						<?=(htmlspecialcharsbx($item['HELP_CAPTION']) ?:
								Loc::getMessage('SENDER_LIMIT_HELP', array(
									'%link_start%' => '<a href="' . htmlspecialcharsbx($item['HELP_URI']) . '" class="sender-config-limits-setup-link">',
									'%link_end%' => '</a>'
							))
						)?>
					</span>
				<?endif;?>
			</h4>
			<?foreach ($item['LIMITS'] as $limit):?>

				<div class="sender-config-limits-block">
					<?if ($limit['CAPTION']):?>
					<div class="sender-config-limits-subtitle">
						<?=htmlspecialcharsbx($limit['CAPTION'])?>
					</div>
					<?endif;?>

					<?if ($limit['TEXT_VIEW']):?>
						<span class="sender-config-limits-info-number"><?=htmlspecialcharsbx($limit['LIMIT'])?></span>
						<span class="sender-config-limits-info-name"><?=htmlspecialcharsbx($limit['UNIT_NAME'])?></span>
					<?else:?>

						<div class="sender-config-limits-head" style="margin-top: 5px;">
							<span class="sender-config-limits-head-left">
								<?=Loc::getMessage('SENDER_LIMIT')?>
							</span>
							<span class="sender-config-limits-head-right">
								<?=htmlspecialcharsbx($limit['UNIT_NAME'])?>
							</span>
						</div>
						<div
							<?if ($limit['PERCENTAGE']):?>
								data-role="percentage-context"
							<?endif;?>
							data-name="<?=htmlspecialcharsbx($limit['NAME'])?>"
							class="sender-config-limits-main"
						>
							<div class="sender-config-limits-available-box">
								<span class="sender-config-limits-available-left">
									<?=Loc::getMessage('SENDER_LIMIT_AVAILABLE')?>
								</span>
								<span data-role="percentage-available" class="sender-config-limits-available-right">
									<?=htmlspecialcharsbx($limit['AVAILABLE'])?>
								</span>
							</div>
							<div class="sender-config-limits-progress-line">
								<div
									class="sender-config-limits-progress-bar"
									style="width: <?=$limit['CURRENT_PERCENTAGE']?>%"
								></div>
								<?if ($limit['PERCENTAGE']):?>
									<div
										data-role="percentage-view"
										class="sender-config-limits-progress-slider"
										style="left: <?=htmlspecialcharsbx($limit['PERCENTAGE'])?>%;"
									>
										<div class="sender-config-limits-progress-popup">
											<span class="sender-config-limits-progress-percentage">
												<span data-role="percentage-text" class="sender-config-limits-progress-name">
													<?=htmlspecialcharsbx($limit['PERCENTAGE'])?>
												</span>
												%
											</span>
											<div data-role="percentage-input" class="sender-config-limits-progress-input-box">
												<input
													class="sender-config-limits-progress-input"
													type="number" min="10" max="100"
													style="width: 40px;"
													value="<?=htmlspecialcharsbx($limit['PERCENTAGE'])?>"
												>%
											</div>
											<?if ($arParams['CAN_EDIT']):?>
												<span data-role="percentage-edit" class="sender-config-limits-progress-button">
													<span class="sender-config-limits-progress-button-item"></span>
												</span>
												<div class="sender-config-limits-progress-triangle"></div>
											<?endif;?>
										</div>
									</div>
								<?endif;?>
							</div>

							<div class="sender-config-limits-bottom">
								<div class="sender-config-limits-bottom-left">
									<?=htmlspecialcharsbx($limit['CURRENT'])?>
								</div>
								<div class="sender-config-limits-bottom-right">
									<?=Loc::getMessage('SENDER_LIMIT_OF')?>
									<span data-role="percentage-limit">
										<?=htmlspecialcharsbx($limit['LIMIT'])?>
									</span>

									<?if ($limit['SETUP_URI']):?>
										<a href="<?=htmlspecialcharsbx($limit['SETUP_URI'])?>" class="sender-config-limits-setup-link">
											<?=htmlspecialcharsbx($limit['SETUP_CAPTION'] ?: Loc::getMessage('SENDER_LIMIT_SETUP'))?>
										</a>
									<?endif;?>
								</div>
							</div>

						</div>
					<?endif;?>
				</div>

			<?endforeach;?>
		</div>
	<?endforeach;?>

	<div class="sender-type-tab" data-tab="others">

		<h4 class="sender-config-limits-title">
			<?=htmlspecialcharsbx(Loc::getMessage('SENDER_SENDING_TIME_TITLE'))?>
		</h4>
		<div class="sender-config-limits-block">
			<div class="sender-config-limits-bottom">
				<div class="sender-config-limits-bottom-left">
					<label for="sender-sending-time-option">
						<input type="checkbox"
							<?php if ($arResult['SENDING_TIME']): ?>
								checked="checked"
							<?php endif;?>
							   class="sender-sending-time-option" id="sender-sending-time-option" />
						<?=Loc::getMessage('SENDER_SENDING_TIME_OPTION')?>
					</label>
				</div>
			</div>
			<div class="sender-sending-time-configuration-block">
				<div class="sender-config-limits-bottom-without-space sender-sending-time-view-block" >
					<div>
						<span class="sender-config-sending-time-caption">
							<span class="sender-sending-start-caption"><?=
								(new \DateTime())
									->setTimestamp($sendingStartTime)
									->format(Context::getCurrent()
										->getCulture()
										->getShortTimeFormat());
								?></span> - <span class="sender-sending-end-caption">
								<?= (new \DateTime())
									->setTimestamp($sendingEndTime)
									->format(Context::getCurrent()
										->getCulture()
										->getShortTimeFormat());
								?></span>
						</span>
						<a href="#/" class = 'sender-sending-time-edit-btn'>
							<?=Loc::getMessage('SENDER_LIMIT_SETUP')?>
						</a>
					</div>
				</div>
				<div class="sender-config-limits-bottom-without-space sender-sending-time-edit-block" style="display: none">
						<select class="bx-sender-form-control bx-sender-message-editor-field-select sender-sending-start">
							<?php for ($hour = 0; $hour < 24; $hour++):?>
								<?php foreach ([0, 30] as $minute):?>
									<?php $time = strtotime(sprintf("%02d:%02d", $hour, $minute)); ?>
									<?php
									$formatted = (new \DateTime())
										->setTimestamp($time)
										->format(Context::getCurrent()
											->getCulture()
											->getShortTimeFormat()
										);
									?>
									<option value='<?=$formatted?>' <?= ($time === $sendingStartTime ? "selected" : "")?>>
										<?=$formatted?>
									</option>
								<?php endforeach;?>
							<?php endfor;?>
						</select> -
						<select class="bx-sender-form-control bx-sender-message-editor-field-select sender-sending-end">
							<?php for ($hour = 0; $hour < 24; $hour++):?>
							<?php foreach ([0, 30] as $minute):?>
									<?php $time = strtotime(sprintf("%02d:%02d", $hour, $minute)); ?>
									<?php
									$formatted = (new \DateTime())
										->setTimestamp($time)
										->format(Context::getCurrent()
											->getCulture()
											->getShortTimeFormat()
										);
									?>
								<option value='<?=$formatted?>' <?= ($time === $sendingEndTime ? "selected" : "")?>>
								<?=$formatted?>
								</option>
							<?php endforeach;?>
							<?php endfor;?>
						</select>
						<button class="ui-btn ui-btn-success ui-btn-md sender-save-time-limit-configuration"><?=
							Loc::getMessage('SENDER_LIMIT_SAVE') ?></button>
					</div>
			</div>
		</div>

		<h4 class="sender-config-limits-title">
			<?=htmlspecialcharsbx(Loc::getMessage('SENDER_TRACK_MAIL_NAME'))?>

			<?if (Loc::getMessage('SENDER_TRACK_MAIL_HELP')):?>
				<span class="sender-config-limits-info">
							<?=(htmlspecialcharsbx($item['HELP_CAPTION']) ?:
								Loc::getMessage('SENDER_TRACK_MAIL_HELP', array(
									'%link_start%' => '<a href="javascript:top.BX.Helper.show(\'redirect=detail&code=13170876\')" class="sender-config-limits-setup-link">',
									'%link_end%' => '</a>'
								))
							)?>
						</span>
			<?endif;?>
		</h4>

		<div class="sender-config-limits-block">
			<div class="sender-config-limits-bottom">
				<div class="sender-config-limits-bottom-left">
					<label for="sender-track-mail-option">
						<input type="checkbox"
							<?php if ($arResult['CAN_TRACK_MAIL']): ?>
								checked="checked"
							<?php endif;?>
							class="sender-track-mail-option" id="sender-track-mail-option" />
						<?=Loc::getMessage('SENDER_TRACK_MAIL_OPTION')?>
					</label>
				</div>
			</div>
		</div>
		<?php if (!\Bitrix\Sender\Integration\Bitrix24\Service::isCloudRegionMayTrackMails()): ?>
			<h4 class="sender-config-limits-title">
				<?=htmlspecialcharsbx(Loc::getMessage('SENDER_MAIL_CONSENT_NAME'))?>

				<?php if (Loc::getMessage('SENDER_MAIL_CONSENT_HELP')):?>
					<span class="sender-config-limits-info">
								<?=(htmlspecialcharsbx($item['HELP_CAPTION']) ?:
									Loc::getMessage('SENDER_MAIL_CONSENT_HELP', array(
										'%link_start%' => '<a href="javascript:top.BX.Helper.show(\'redirect=detail&code=13170876\')" class="sender-config-limits-setup-link">',
										'%link_end%' => '</a>'
									))
								)?>
							</span>
				<?php endif;?>
			</h4>

			<div class="sender-config-limits-block">
				<div class="sender-config-limits-bottom">
					<div class="sender-config-limits-bottom-left">
						<label for="sender-mail-consent-option">
							<input type="checkbox"
								<?php if ($arResult['USE_MAIL_CONSENT']): ?>
									checked="checked"
								<?php endif;?>
									class="sender-mail-consent-option" id="sender-mail-consent-option" />
							<?=Loc::getMessage('SENDER_MAIL_CONSENT_OPTION')?>
						</label>
					</div>
				</div>
			</div>
		<?php endif;?>
	</div>
</div>
	<script>
		BX.ready(function () {
			BX.Sender.Config.Limits.init(<?=Json::encode(array(
				'containerId' => $containerId,
				'actionUri' => $arResult['ACTION_URI'],
				'defaultTab' => !$defaultTab ? 'others' : $defaultTab,
				'mess' => array(
					'mailDailyLimit' => Loc::getMessage('SENDER_LIMIT_MAIL_DAILY_DESC'),
					'mailDailyLimitTitle' => Loc::getMessage('SENDER_LIMIT_MAIL_DAILY_DESC_TITLE'),
					'close' => Loc::getMessage('SENDER_LIMIT_BTN_CLOSE'),
					'success' => Loc::getMessage('SENDER_LIMIT_NOTIFICATION_SUCCESS'),
				)
			))?>);
		});
	</script>
<?php


$menuItems[] = [
	'NAME' => Loc::getMessage('SENDER_LIMIT_LEFT_MENU_ADDITIONAL_CONFIGURATION'),
	'ATTRIBUTES' => [
		'onclick' => "BX.Sender.Config.Limits.changeLeftMenuOption('others')",
		'data-role' => 'others',
	],
	'ACTIVE' => !$defaultTab
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrappermenu',
	'',
	[
		'TITLE' => Loc::getMessage('SENDER_LIMIT_TITLE'),
		'ITEMS' => $menuItems,
	],
	$this->getComponent()
);