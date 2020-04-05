<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if (!empty($arResult["errorMessage"]))
{
	if (!is_array($arResult["errorMessage"]))
	{
		ShowError($arResult["errorMessage"]);
	}
	else
	{
		foreach ($arResult["errorMessage"] as $errorMessage)
		{
			ShowError($errorMessage);
		}
	}
}
else
{
	if ($arParams['REFRESHED_COMPONENT_MODE'] === 'Y')
	{
		$wrapperId = str_shuffle(substr($arResult['SIGNED_PARAMS'],0,10));
		?>
		<div class="bx-sap row">
			<div class="col" id="bx-sap<?=$wrapperId?>">
				<?
				if ($arParams['SELL_VALUES_FROM_VAR'] != 'Y')
				{
					if ($arParams['SELL_SHOW_FIXED_VALUES'] === 'Y')
					{
						?>
						<div class="row">
							<div class="col sale-accountpay-block">
								<h3><?= Loc::getMessage("SAP_FIXED_PAYMENT") ?></h3>
								<div class="sale-accountpay-fixedpay-container">
									<div class="sale-accountpay-fixedpay-list">
										<?
										foreach ($arParams["SELL_TOTAL"] as $valueChanging)
										{
											?>
											<div class="sale-accountpay-fixedpay-item"><?=CUtil::JSEscape(htmlspecialcharsbx($valueChanging))?></div>
											<?
										}
										?>
									</div>
								</div>
							</div>
						</div>
						<?
					}
					?>
					<div class="row">
						<div class="col sale-accountpay-block form-horizontal">
							<h3><?=Loc::getMessage("SAP_SUM")?></h3>
							<div class="form-group row">

									<?
									$inputElement = "
											<div class='col-2'>
											<input type='text' placeholder='0.00' 
											class='form-control sale-accountpay-input text-right' value='0.00' "
											."name=".CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"]))." "
											.($arParams['SELL_USER_INPUT'] === 'N' ? "disabled" :"").
											"></div>";
									$tempCurrencyRow = trim(str_replace("#", "", $arResult['FORMATED_CURRENCY']));
									$labelWrapper = "<label class='control-label col-form-label col-9'>".$tempCurrencyRow."</label>";
									$currencyRow = str_replace($tempCurrencyRow, $labelWrapper, $arResult['FORMATED_CURRENCY']);
									$currencyRow = str_replace($tempCurrencyRow, $labelWrapper, $arResult['FORMATED_CURRENCY']);
									$currencyRow = str_replace("#", $inputElement, $currencyRow);
									echo $currencyRow;
									?>
								</div>
						</div>
					</div>
				<?
				}
				else
				{
					if ($arParams['SELL_SHOW_RESULT_SUM'] === 'Y')
					{
						?>
						<div class="row mb-3">
							<div class="col">
								<h3><?=Loc::getMessage("SAP_SUM")?></h3>
								<h2><?=SaleFormatCurrency($arResult["SELL_VAR_PRICE_VALUE"], $arParams['SELL_CURRENCY'])?></h2>
							</div>
						</div>
						<?
					}
					?>
					<div class="row">
						<div class="col">
							<input type="hidden" name="<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"]))?>" class="input-lg sale-accountpay-input" value="<?=CUtil::JSEscape(htmlspecialcharsbx($arResult["SELL_VAR_PRICE_VALUE"]))?>">
						</div>
					</div>
					<?
				}
				?>
				<div class="row mb-3">
					<div class="col">
						<h3><?=Loc::getMessage("SAP_TYPE_PAYMENT_TITLE")?></h3>
						<div class="row sale-accountpay-pp">
							<?
							foreach ($arResult['PAYSYSTEMS_LIST'] as $key => $paySystem)
							{
							?>
								<div class="sale-accountpay-pp-company col-lg-2 col-md-3 col-sm-4 col-6 <?= ($key == 0) ? 'bx-selected' :""?>">
									<div class="sale-accountpay-pp-company-graf-container">
										<input type="checkbox" class="sale-accountpay-pp-company-checkbox" name="PAY_SYSTEM_ID" value="<?=$paySystem['ID']?>" <?= ($key == 0) ? "checked='checked'" :""?>>
										<?
										if (isset($paySystem['LOGOTIP']))
										{
										?>
											<div class="sale-accountpay-pp-company-image" style="background-image: url(<?=$paySystem['LOGOTIP']?>);"></div>
										<?
										}
										?>
									</div>
									<div class="sale-accountpay-pp-company-smalltitle"><?=CUtil::JSEscape(htmlspecialcharsbx($paySystem['NAME']))?></div>
								</div>
							<?
							}
							?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<a href="" class="btn btn-primary btn-lg sale-account-pay-button"><?=Loc::getMessage("SAP_BUTTON")?></a>
					</div>
				</div>
			</div>
		</div>
		<?
		$javascriptParams = array(
			"alertMessages" => array("wrongInput" => Loc::getMessage('SAP_ERROR_INPUT')),
			"url" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
			"templateFolder" => CUtil::JSEscape($templateFolder),
			"signedParams" => $arResult['SIGNED_PARAMS'],
			"wrapperId" => $wrapperId
		);
		$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
		?>
		<script>
			var sc = new BX.saleAccountPay(<?=$javascriptParams?>);
		</script>
	<?
	}
	else
	{
		?>
		<h3><?=Loc::getMessage("SAP_BUY_MONEY")?></h3>
		<form method="post" name="buyMoney" action="">
			<?
			foreach($arResult["AMOUNT_TO_SHOW"] as $value)
			{
				?>
				<input type="radio" name="<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"]))?>"
					value="<?=$value["ID"]?>" id="<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"])).$value["ID"]?>">
				<label for="<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"])).$value["ID"]?>"><?=$value["NAME"]?></label>
				<br />
				<?
			}
			?>
			<input type="submit" class="btn btn-primary" name="button" value="<?=GetMessage("SAP_BUTTON")?>">
		</form>
		<?
	}
}

