<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$sum = Loc::getMessage(
	'SALE_HANDLERS_PAY_SYSTEM_SKB_DESCRIPTION',
	[
		'#SUM#' => SaleFormatCurrency(round($params['SUM'], 2), $params['CURRENCY']),
	]
);
?>
<div class="mb-4">
	<p><?= $sum ?></p>
	<div class="mb-4" id="qr-code-hint" style="display: none;">
		<?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SKB_QR_CODE_HINT'); ?>
	</div>
	<div class="mb-4">
		<img style="width: 350px" src="data:image/png;base64,<?= $params['QR_CODE_IMAGE'] ?>"/>
	</div>
	<div id="button-container" style="display: none;">
		<div class="d-flex align-items-center mb-3">
			<div class="col-auto pl-0">
				<a class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" href="<?= $params['URL'] ?>"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SKB_BUTTON_PAID') ?></a>
			</div>
			<div class="col pr-0"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SKB_REDIRECT'); ?></div>
		</div>
	</div>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SKB_WARNING_RETURN') ?></div>
</div>

<script>
	BX.ready(function(){
		var isMobile = BX.browser.IsMobile(),
			url = '<?= CUtil::JSEscape($params['URL']) ?>';

		if (isMobile)
		{
			BX('button-container').style.display = 'block';
		}
		else
		{
			BX('qr-code-hint').style.display = 'block';
		}

		if (isMobile && url)
		{
			window.location.href = url;
		}
	});
</script>