<?php
use Bitrix\Main\Localization\Loc;

/**
 * @var $params array
 */

Loc::loadMessages(__FILE__);
?>
<div class="mb-4">
	<p><?= Loc::getMessage(
		'SALE_HANDLERS_PAY_SYSTEM_ALFABANK_DESCRIPTION',
		[
			'#SUM#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY'])
		]
	); ?></p>
	<form action="<?= $params['URL']; ?>" method="GET">
		<?php
		if (isset($params['FORM_PARAMS']))
		{
			foreach ($params['FORM_PARAMS'] as $param => $value)
			{
				?><input type="hidden" name="<?= $param; ?>" value="<?= $value; ?>"><?php
			}
		}
		?>
		<div class="d-flex align-items-center mb-3">
			<div class="col-auto pl-0">
				<input class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" value="<?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_ALFABANK_BUTTON_PAID'); ?>" type="submit">
			</div>
			<div class="col pr-0"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_ALFABANK_REDIRECT'); ?></div>
		</div>
	</form>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_ALFABANK_WARNING_RETURN'); ?></div>
</div>