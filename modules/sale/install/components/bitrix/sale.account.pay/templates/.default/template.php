<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

$this->addExternalCss("/bitrix/css/main/bootstrap.css");

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
		<div class="bx-sap" id="bx-sap<?=$wrapperId?>">
			<div class="container-fluid">
				<?
				if ($arParams['SELL_VALUES_FROM_VAR'] != 'Y')
				{
					if ($arParams['SELL_SHOW_FIXED_VALUES'] === 'Y')
					{
						?>
						<div class="row">
							<div class="col-xs-12 sale-acountpay-block">
								<h3 class="sale-acountpay-title"><?= Loc::getMessage("SAP_FIXED_PAYMENT") ?></h3>
								<div class="sale-acountpay-fixedpay-container">
									<div class="sale-acountpay-fixedpay-list">
										<?
										foreach ($arParams["SELL_TOTAL"] as $valueChanging)
										{
											?>
											<div class="sale-acountpay-fixedpay-item">
												<?=CUtil::JSEscape(htmlspecialcharsbx($valueChanging))?>
											</div>
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
						<div class="col-xs-12 sale-acountpay-block form-horizontal">
							<h3 class="sale-acountpay-title"><?=Loc::getMessage("SAP_SUM")?></h3>
							<div class="" style="max-width: 200px;">
								<div class="form-group" style="margin-bottom: 0;">
									<?
									$inputElement = "
										<div class='col-sm-9'>
											<input type='text'	placeholder='0.00' 
											class='form-control input-lg sale-acountpay-input' value='0.00' "
											."name=".CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"]))." "
											.($arParams['SELL_USER_INPUT'] === 'N' ? "disabled" :"").
											">
										</div>";
									$tempCurrencyRow = trim(str_replace("#", "", $arResult['FORMATED_CURRENCY']));
									$labelWrapper = "<label class='control-label input-lg input-lg col-sm-3'>".$tempCurrencyRow."</label>";
									$currencyRow = str_replace($tempCurrencyRow, $labelWrapper, $arResult['FORMATED_CURRENCY']);
									$currencyRow = str_replace("#", $inputElement, $currencyRow);
									echo $currencyRow;
									?>
								</div>
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
						<div class="row">
							<div class="col-xs-12 sale-acountpay-block form-horizontal">
								<h3 class="sale-acountpay-title"><?=Loc::getMessage("SAP_SUM")?></h3>
								<h2><?=SaleFormatCurrency($arResult["SELL_VAR_PRICE_VALUE"], $arParams['SELL_CURRENCY'])?></h2>
							</div>
						</div>
						<?
					}
					?>
					<div class="row">
						<input type="hidden" name="<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["VAR"]))?>"
							class="form-control input-lg sale-acountpay-input"
							value="<?=CUtil::JSEscape(htmlspecialcharsbx($arResult["SELL_VAR_PRICE_VALUE"]))?>">
					</div>
					<?
				}
				?>
				<div class="row">
					<div class="col-xs-12 sale-acountpay-block">
						<h3 class="sale-acountpay-title"><?=Loc::getMessage("SAP_TYPE_PAYMENT_TITLE")?></h3>
						<div>
							<div class="sale-acountpay-pp row">
								<div class="col-md-7 col-sm-8 col-xs-12 ">
									<?
									foreach ($arResult['PAYSYSTEMS_LIST'] as $key => $paySystem)
									{
										?>
										<div class="sale-acountpay-pp-company col-lg-3 col-sm-4 col-xs-6 <?= ($key == 0) ? 'bx-selected' :""?>">
											<div class="sale-acountpay-pp-company-graf-container">
												<input type="checkbox"
														class="sale-acountpay-pp-company-checkbox"
														name="PAY_SYSTEM_ID"
														value="<?=$paySystem['ID']?>"
														<?= ($key == 0) ? "checked='checked'" :""?>
												>
												<?
												if (isset($paySystem['LOGOTIP']))
												{
													?>
													<div class="sale-acountpay-pp-company-image"
														style="
															background-image: url(<?=$paySystem['LOGOTIP']?>);
															background-image: -webkit-image-set(url(<?=$paySystem['LOGOTIP']?>) 1x, url(<?=$paySystem['LOGOTIP']?>) 2x);">
													</div>
													<?
												}
												?>
											</div>
											<div class="sale-acountpay-pp-company-smalltitle">
												<?=CUtil::JSEscape(htmlspecialcharsbx($paySystem['NAME']))?>
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
				<div class="row">
					<div class="col-xs-12">
						<a href="" class="btn btn-default btn-lg sale-account-pay-button"><?=Loc::getMessage("SAP_BUTTON")?></a>
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
			<input type="submit" name="button" value="<?=GetMessage("SAP_BUTTON")?>">
		</form>
		<?
	}
}

