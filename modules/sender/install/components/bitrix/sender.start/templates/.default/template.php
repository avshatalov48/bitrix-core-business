<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$containerId = 'sender-start-container';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Start.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'pathToLetterAdd' => $arResult['PATH_TO_LETTER_ADD'],
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-start-wrap">

	<?if (!empty($arResult['MESSAGES']['MAILING']['LIST'])):?>
	<div class="sender-start-block">
		<div class="sender-start-title"><?=Loc::getMessage('SENDER_START_CREATE_LETTER')?></div>
		<div  class="sender-start-tile">
			<?foreach ($arResult['MESSAGES']['MAILING']['FEATURED_LIST'] as $message):
				$name = htmlspecialcharsbx($message['NAME']);
				$code = htmlspecialcharsbx($message['CODE']);
				$url = htmlspecialcharsbx($message['URL']);
				?>
				<div
					data-role="letter-add"
					data-available="<?=($message['IS_AVAILABLE'] ? 'y' : 'n')?>"
					data-bx-code="<?=$code?>"
					data-bx-url="<?=$url?>"
					class="sender-start-tile-item"
				>
					<a href="<?=$url?>">
						<div class="sender-start-tile-icon sender-start-tile-icon-<?=$code?>"></div>
						<div class="sender-start-tile-name">
							<?if (!$message['IS_AVAILABLE']):?>
								<div class="tariff-lock"></div>
							<?endif;?>
							<?=$name?>
						</div>
					</a>
				</div>
			<?endforeach;?>
		</div>

		<div class="sender-start-block-inner" style="<?=(count($arResult['MESSAGES']['MAILING']['OTHER_LIST']) === 0 ? 'display: none;' : '')?>">
			<a data-role="letter-other" class="sender-start-link sender-start-link-small-grey-bold">
				<?=Loc::getMessage('SENDER_START_ADDITIONAL')?>
			</a>
			<div data-role="letter-other-cont" class="sender-start-tile" style="display: none;">
				<?foreach ($arResult['MESSAGES']['MAILING']['OTHER_LIST'] as $message):
					$name = htmlspecialcharsbx($message['NAME']);
					$code = htmlspecialcharsbx($message['CODE']);
					$url = htmlspecialcharsbx($message['URL']);
					?>
					<div
						data-role="letter-add"
						data-available="<?=($message['IS_AVAILABLE'] ? 'y' : 'n')?>"
						data-bx-code="<?=$code?>"
						data-bx-url="<?=$url?>"
						class="sender-start-tile-item"
					>
						<div class="sender-start-tile-icon sender-start-tile-icon-<?=$code?>"></div>
						<div class="sender-start-tile-name">
							<?if (!$message['IS_AVAILABLE']):?>
								<div class="tariff-lock"></div>
							<?endif;?>
							<?=$name?>
						</div>
					</div>
				<?endforeach;?>
			</div>
		</div>
	</div>
	<?endif;?>

	<?if (!empty($arResult['MESSAGES']['ADS']['LIST'])):?>
	<div class="sender-start-block">
		<div class="sender-start-title"><?=Loc::getMessage('SENDER_START_CREATE_AD')?></div>
		<div class="sender-start-tile sender-start-tile-campaign">
			<?foreach ($arResult['MESSAGES']['ADS']['FEATURED_LIST'] as $message):
				$name = htmlspecialcharsbx($message['NAME']);
				$code = htmlspecialcharsbx($message['CODE']);
				$url = htmlspecialcharsbx($message['URL']);
				?>
				<div
					data-role="letter-add"
					data-available="<?=($message['IS_AVAILABLE'] ? 'y' : 'n')?>"
					data-bx-code="<?=$code?>"
					data-bx-url="<?=$url?>"
					class="sender-start-tile-item"
				>
					<a href="<?=$url?>">
						<div class="sender-start-tile-icon sender-start-tile-icon-<?=$code?>"></div>
						<div class="sender-start-tile-name">
							<?if (!$message['IS_AVAILABLE']):?>
								<div class="tariff-lock"></div>
							<?endif;?>
							<?=$name?>
						</div>
					</a>
				</div>
			<?endforeach;?>
		</div>
	</div>
	<?endif;?>

	<?if (!empty($arResult['MESSAGES']['RC']['LIST'])):?>
	<div class="sender-start-block">
		<div class="sender-start-title"><?=Loc::getMessage('SENDER_START_CREATE_RC')?></div>
		<div class="sender-start-tile sender-start-tile-campaign">
			<?foreach ($arResult['MESSAGES']['RC']['FEATURED_LIST'] as $message):
				$name = htmlspecialcharsbx($message['NAME']);
				$code = htmlspecialcharsbx($message['CODE']);
				$url = htmlspecialcharsbx($message['URL']);
				?>
				<div
					data-role="letter-add"
					data-available="<?=($message['IS_AVAILABLE'] ? 'y' : 'n')?>"
					data-bx-code="<?=$code?>"
					data-bx-url="<?=$url?>"
					class="sender-start-tile-item"
				>
					<a href="<?=$url?>">
						<div class="sender-start-tile-icon sender-start-tile-icon-<?=$code?>"></div>
						<div class="sender-start-tile-name">
							<?if (!$message['IS_AVAILABLE']):?>
								<div class="tariff-lock"></div>
							<?endif;?>
							<?=$name?>
						</div>
					</a>
				</div>
			<?endforeach;?>
		</div>
	</div>
	<?endif;?>

</div>