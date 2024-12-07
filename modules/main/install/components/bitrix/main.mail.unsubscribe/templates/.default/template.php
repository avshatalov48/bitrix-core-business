<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
]);

?>
<div id="main-mail-unsubscribe-container" class="main-mail-unsubscribe-box">
	<div class="main-mail-unsubscribe-main">
		<?if ($arResult['SITE_NAME']):?>
			<div class="main-mail-unsubscribe-subtitle">
				<?=htmlspecialcharsbx($arResult['SITE_NAME'])?>
			</div>
		<?endif;?>
		<div class="main-mail-unsubscribe-title"><?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_DEFAULT_TITLE')?></div>
		<form method="POST" action="<?=$arResult['FORM_URL']?>">
			<div class="main-mail-unsubscribe-inner">
				<?if($arResult["DATA_SAVED"] == 'Y'):?>
					<div class="main-mail-unsubscribe-content">
						<div class="main-mail-unsubscribe-content-item">
							<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_SUCCESS')?>
						</div>
					</div>
				<?elseif(empty($arResult["ERROR"]) && !empty($arResult['LIST'])):?>
					<div class="main-mail-unsubscribe-content">
						<div class="main-mail-unsubscribe-content-item"><?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_DEFAULT_DISCLAIMER')?></div>
						<?if (count($arResult['LIST']) > 1):?>
							<div class="main-mail-unsubscribe-content-item"><?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_DEFAULT_SELECT')?></div>
						<?endif;?>
					</div>
				<?endif;?>

				<?if(empty($arResult["ERROR"]) && !empty($arResult['LIST'])):?>
					<?if (count($arResult['LIST']) > 1):?>
						<div class="main-mail-unsubscribe-check-list">
							<?foreach($arResult['LIST'] as $item):?>
								<label class="main-mail-unsubscribe-check-list-item">
									<input type="checkbox" name="MAIN_MAIL_UNSUB[]" value="<?=$item['ID']?>" <?=($item['SELECTED'] ? 'checked' : '')?> class="main-mail-unsubscribe-checkbox">
									<span class="main-mail-unsubscribe-name" title="<?=htmlspecialcharsbx($item['DESC'])?>">
										<?=htmlspecialcharsbx($item['NAME'])?>
									</span>
								</label>
							<?endforeach;?>
						</div>
					<?else:?>
						<?foreach($arResult['LIST'] as $item):?>
							<input type="hidden" name="MAIN_MAIL_UNSUB[]" value="<?=$item['ID']?>">
						<?endforeach;?>
					<?endif;?>
				<?elseif(!empty($arResult["ERROR"])):?>
					<div class="main-mail-unsubscribe-content">
						<div class="main-mail-unsubscribe-content-item">
							<?=htmlspecialcharsbx($arResult["ERROR"])?>
						</div>
					</div>
				<?else:?>
					<div class="main-mail-unsubscribe-content">
						<div class="main-mail-unsubscribe-content-item">
							<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_DEFAULT_EMPTY')?>
						</div>
					</div>
				<?endif;?>
			</div>
			<?if(empty($arResult["ERROR"]) && !empty($arResult['LIST'])):?>
				<input type="hidden" value="Y" name="MAIN_MAIL_UNSUB_BUTTON">
				<?=bitrix_sessid_post()?>

				<?if ($arParams['ABUSE']):?>
					<div data-role="spam-block" class="main-mail-unsubscribe-spam">
						<div class="main-mail-unsubscribe-spam-text">
							<textarea name="ABUSE_TEXT" placeholder="<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_ABUSE_PLACEHOLDER')?>"></textarea>
						</div>

						<button name="ABUSE" value="Y" class="ui-btn ui-btn-primary">
							<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_SEND')?>
						</button>
						<span data-role="unsub-block-btn" class="ui-btn ui-btn-light">
							<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_CANCEL')?>
						</span>
					</div>
				<?endif;?>

				<div data-role="unsub-block" class="main-mail-unsubscribe-unsub">
					<button class="ui-btn ui-btn-primary">
						<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_DEFAULT_BUTTON')?>
					</button>

					<?if ($arParams['ABUSE']):?>
						<span data-role="spam-block-btn" class="ui-btn ui-btn-light">
							<?=Loc::getMessage('MAIN_MAIL_UNSUBSCRIBE_TEMPL_SPAM_BUTTON')?>
						</span>
					<?endif;?>
				</div>

				<br><br>
				<?=htmlspecialcharsbx($arResult['WARNING'])?>
			<?endif;?>
		</form>
	</div>
	<script>
		BX.ready(function () {
			BX.Main.Mail.Unsubscriber.init({'containerId': 'main-mail-unsubscribe-container'});
		})
	</script>
</div>