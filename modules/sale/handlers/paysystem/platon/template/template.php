<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="mb-4">
	<p><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PLATON_DESCRIPTION'); ?></p>
	<p><?= Loc::getMessage(
			'SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PLATON_SUM',
			[
				'#SUM#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY']),
			]
		); ?></p>
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<form action="<?= $params['FORM_ACTION_URL'] ?>" method="post" enctype="multipart/form-data">
				<input type="hidden" name="key" value="<?= htmlspecialcharsbx($params['FORM_DATA']['KEY']) ?>">
				<input type="hidden" name="payment" value="<?= $params['FORM_DATA']['PAYMENT'] ?>">
				<input type="hidden" name="data" value="<?= $params['FORM_DATA']['DATA'] ?>">
				<input type="hidden" name="url" value="<?= htmlspecialcharsbx($params['FORM_DATA']['URL']) ?>">
				<input type="hidden" name="req_token" value="<?= $params['FORM_DATA']['REQ_TOKEN'] ?>">
				<input type="hidden" name="sign" value="<?= $params['FORM_DATA']['SIGN'] ?>">
				<input type="hidden" name="order" value="<?= $params['FORM_DATA']['ORDER'] ?>">
				<?php if (isset($params['FORM_DATA']['EMAIL'])): ?>
					<input type="hidden" name="email" value="<?= $params['FORM_DATA']['EMAIL'] ?>">
				<?php endif; ?>
				<input type="hidden" name="ext1" value="<?= $params['FORM_DATA']['EXT_1'] ?>">
				<input type="hidden" name="ext2" value="<?= $params['FORM_DATA']['EXT_2'] ?>">
				<input type="hidden" name="ext3" value="<?= $params['FORM_DATA']['EXT_3'] ?>">
				<input type="submit" value="<?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PLATON_BUTTON_PAY') ?>" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;">
			</form>
		</div>
		<div class="col pr-0"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PLATON_REDIRECT_MESS') ?></div>
	</div>
	<div class="alert alert-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_TEMPLATE_PLATON_WARNING_RETURN') ?></div>
</div>
