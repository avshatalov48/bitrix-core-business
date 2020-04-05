<?
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Bitrix\Main\Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");

Loc::loadMessages(__FILE__);

$documentRoot = Application::getDocumentRoot();
$sum = round($params['SUM'], 2);
?>

<div class="paysystem-yandex mb-4" id="paysystem-yandex">
	<form id="paysystem-yandex-form">
		<p class="mb-2"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION');?></p>
		<p class="mb-2"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM')." ".SaleFormatCurrency($params['SUM'], $params['CURRENCY']);?></p>
		<?if (isset($params['FIELDS'])):?>
			<fieldset class="form-group">
				<?foreach ($params['FIELDS'] as $field):?>
					<?if (in_array($field, $params['PHONE_FIELDS'])):?>
						<label for="<?=$field?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?>:</label>
						<input name="<?=$field;?>" type="text" style="max-width: 300px;" id="<?=$field;?>" class="form-control js-paysystem-yandex-input-phone" value="" autocomplete="off" placeholder="">
					<?else:?>
						<label for="<?=$field;?>"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?></label>
						<input name="<?=$field;?>" type="text" style="max-width: 300px;" id="<?=$field;?>" class="form-control" placeholder="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_'.ToUpper($params['PAYMENT_METHOD']).'_'.ToUpper($field));?>">
					<?endif;?>
				<?endforeach;?>
			</fieldset>
		<?endif;?>
		<input class="btn btn-primary pl-4 pr-4" name="BuyButton" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_BUTTON_NEXT')?>" type="submit">
	</form>
</div>

<?
$phoneCountryCode = null;
if (Bitrix\Main\Loader::includeModule('bitrix24'))
{
	$zone = \CBitrix24::getPortalZone();
	$zone = strtolower($zone);
	if (in_array($zone, array('kz', 'by', 'ua')))
	{
		$phoneCountryCode = $zone;
	}
}

$messages = Loc::loadLanguageFile(__FILE__);
?>

<script>
	<?
	include_once $documentRoot.'/bitrix/js/sale/masked.js';
	include_once 'script.js';
	?>

	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	BX.ready(function(){
		BX.PaymentPhoneForm = new BX.Sale.Yandexcheckout.PaymentPhoneForm(<?=CUtil::PhpToJSObject([
			'form' => 'paysystem-yandex-form',
			'phoneFormatDataUrl' => '/bitrix/js/sale/phone_mask',
			'phoneCountryCode' => $phoneCountryCode,
		])?>);

		BX.Sale.Yandexcheckout.init({
			formId: 'paysystem-yandex-form',
			paysystemBlockId: 'paysystem-yandex',
			ajaxUrl: '/bitrix/tools/sale_ps_ajax.php',
			paymentId: '<?=CUtil::JSEscape($params['PAYMENT_ID'])?>',
			paySystemId: '<?=CUtil::JSEscape($params['PAYSYSTEM_ID'])?>'
		});
	});
</script>