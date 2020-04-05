<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$containerId = 'sender-start-container';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Config.Limits.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'mess' => array(
				'mailDailyLimit' => Loc::getMessage('SENDER_LIMIT_MAIL_DAILY_DESC'),
				'mailDailyLimitTitle' => Loc::getMessage('SENDER_LIMIT_MAIL_DAILY_DESC_TITLE'),
				'close' => Loc::getMessage('SENDER_LIMIT_BTN_CLOSE'),
			)
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-config-limits-wrap">

	<?foreach ($arResult['LIST'] as $item):?>
		<div class="sender-config-limits-box">
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

</div>