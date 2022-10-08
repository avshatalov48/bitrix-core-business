<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

if (is_array($arResult["ACCOUNT_LIST"]))
{
	?>
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
	<?
}