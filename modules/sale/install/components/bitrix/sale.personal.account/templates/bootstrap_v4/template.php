<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

if (!empty($arResult['ERRORS']))
{
	$component = $this->__component;
	foreach($arResult['ERRORS'] as $code => $error)
	{
		if ($code !== $component::E_NOT_AUTHORIZED)
			ShowError($error);
	}

	if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS'][$component::E_NOT_AUTHORIZED]))
	{
		?>
		<div class="ro mb-3">
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<div class="alert alert-danger"><?=$arResult['ERRORS'][$component::E_NOT_AUTHORIZED]?></div>
			</div>
			<? $authListGetParams = array(); ?>
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<?$APPLICATION->AuthForm('', false, false, 'N', false);?>
			</div>
		</div>
		<?
	}
}
else
{
	?>
	<div class="row mb-3">
		<div class="col mb-3">
			<div class="sale-personal-account-wallet-container">
				<div class="sale-personal-account-wallet-title">
					<?=Bitrix\Main\Localization\Loc::getMessage('SPA_BILL_AT')?>
					<?=$arResult["DATE"];?>
				</div>
				<div class="sale-personal-account-wallet-list-container">
					<div class="sale-personal-account-wallet-list">
						<?
							foreach($arResult["ACCOUNT_LIST"] as $accountValue)
							{
								?>
								<div class="sale-personal-account-wallet-list-item">
									<div class="sale-personal-account-wallet-sum"><?=$accountValue['SUM']?></div>
									<div class="sale-personal-account-wallet-currency">
										<div class="sale-personal-account-wallet-currency-item"><?=$accountValue['CURRENCY']?></div>
										<div class="sale-personal-account-wallet-currency-item"><?=$accountValue["CURRENCY_FULL_NAME"]?></div>
									</div>
								</div>
								<?
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?
}