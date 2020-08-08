<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
include_once(dirname(__FILE__)."/init_vars.php");
include(GetLangFileName(dirname(__FILE__)."/lang/", "/mp3.php"));

$APPLICATION->SetTitle(GetMessage("MP3_BUY_MONEY"));

if (CModule::IncludeModule("sale")):
/////////////////////////////////////////////////////////////////////////////
?>

<?
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "buy" && $USER->IsAuthorized())
{
	$PRICE_ID = intval($PRICE_ID);
	if ($PRICE_ID <= 0)
		$errorMessage .= GetMessage("MP3_ERROR_EMPTY_SUM")."<br>";

	if ($errorMessage == '')
	{
		if (!array_key_exists($PRICE_ID, $arMP3Sums)
			|| array_key_exists($PRICE_ID, $arMP3Sums) && isset($arMP3Sums[$PRICE_ID]["HIDDEN"]) && $arMP3Sums[$PRICE_ID]["HIDDEN"] == "Y")
			$errorMessage .= GetMessage("MP3_ERROR_WRONG_SUM")."<br>";
	}

	if ($errorMessage == '')
	{
		$userPrice = DoubleVal($arMP3Sums[$PRICE_ID]["PRICE"]);
		$userCurrency = Trim($arMP3Sums[$PRICE_ID]["CURRENCY"]);

		$arFields = array(
				"PRODUCT_ID" => $PRICE_ID,
				"PRICE" => $userPrice,
				"CURRENCY" => $userCurrency,
				"QUANTITY" => 1,
				"LID" => SITE_ID,
				"DELAY" => "N",
				"CAN_BUY" => "Y",
				"NAME" => str_replace("#SUM#", SaleFormatCurrency($userPrice, $userCurrency), GetMessage("MP3_BASKET_NAME")),
				"MODULE" => "main",
				"PAY_CALLBACK_FUNC" => "MP3DeliveryOrderCallback"
			);

		$basketID = CSaleBasket::Add($arFields);
		if ($basketID)
		{
			if (CModule::IncludeModule("statistic"))
				CStatistic::Set_Event("sale2basket", "main", "");

			LocalRedirect("order.php");
		}
		else
		{
			if ($ex = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= GetMessage("MP3_ERROR_ADD_BASKET")."<br>";
		}
	}
}

if ($USER->IsAuthorized())
{
	$baseLangCurrency = CSaleLang::GetLangCurrency(SITE_ID);
	echo ShowError($errorMessage);
	?>
	<table border="0" cellpadding="0" cellspacing="0" class="tableborder" width="100%"><tr><td>
	<table border="0" cellspacing="1" width="100%" cellpadding="2">
		<tr>
			<td class="tablehead" align="center" colspan="2">
				<font class="tableheadtext"><?= GetMessage("MP3_YOUR_ACCOUNT") ?></font>
			</td>
		</tr>
		<tr>
			<td class="tablebody" align="right" width="40%">
				<font class="tablebodytext"><?= GetMessage("MP3_ACCOUNT_OWNER") ?>:</font>
			</td>
			<td class="tablebody" align="left" width="60%">
				<font class="tablebodytext"><?= $USER->GetFullName() ?></font>
			</td>
		</tr>
		<tr>
			<td class="tablebody" align="right">
				<font class="tablebodytext"><?= GetMessage("MP3_ACCOUNT_SUM") ?>:</font>
			</td>
			<td class="tablebody" align="left">
				<font class="tablebodytext"><?
					$dbUserAccount = CSaleUserAccount::GetList(
							array(),
							array(
									"USER_ID" => $USER->GetID(),
									"CURRENCY" => $baseLangCurrency
								)
						);
					if (!($arUserAccount = $dbUserAccount->Fetch()))
						$arUserAccount = array("CURRENT_BUDGET" => 0);

					echo SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $baseLangCurrency);
					?></font>
			</td>
		</tr>
	</table>
	</td></tr></table>

	<br><br>

	<form method="post" action="buy_money.php">
	<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td>
		<table border="0" cellpadding="0" cellspacing="0" class="tableborder" width="100%"><tr><td>
		<table border="0" cellspacing="1" width="100%" cellpadding="2">
			<tr>
				<td class="tablehead" align="center" colspan="2">
					<font class="tableheadtext"><?= GetMessage("MP3_BUY") ?></font>
				</td>
			</tr>
			<tr>
				<td class="tablebody" align="right" width="40%">
					<font class="tablebodytext"><?= GetMessage("MP3_SELECT_SUM") ?>:</font>
				</td>
				<td class="tablebody" align="left" width="60%">
					<font class="tablebodytext">
					<?
					foreach ($arMP3Sums as $sumID => $arSumParams)
					{
						if (isset($arSumParams["HIDDEN"]) && $arSumParams["HIDDEN"] == "Y")
							continue;

						$showPrice = $arSumParams["PRICE"];
						$showCurrency = $arSumParams["CURRENCY"];
						if ($bMP3ConvertCurrency && ($arSumParams["CURRENCY"] != $baseLangCurrency))
						{
							$showPrice = CCurrencyRates::ConvertCurrency($arSumParams["PRICE"], $arSumParams["CURRENCY"], $baseLangCurrency);
							$showCurrency = $baseLangCurrency;
						}
						?>
						<input type="radio" name="PRICE_ID" id="PRICE_ID_<?= $sumID ?>" value="<?= $sumID ?>"><label for="PRICE_ID_<?= $sumID ?>"><?
						echo SaleFormatCurrency($arSumParams["PRICE"], $arSumParams["CURRENCY"]);
						if ($showCurrency != $arSumParams["CURRENCY"])
							echo " (".SaleFormatCurrency($showPrice, $showCurrency).")";
						?></label><br>
						<?
					}
					?>
					</font>
				</td>
			</tr>
		</table>
		</td></tr></table>
	</td></tr>
	<tr><td align="center">
		<br>
		<input type="hidden" name="action" value="buy">
		<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= GetMessage("MP3_PAY") ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="inputbuttonflat">
	</td></tr></table>
	</form>
	<?
}
else
{
	?><font class="text"><?= GetMessage("MP3_NOT_AUTH") ?></font><?
}
?>

<?
/////////////////////////////////////////////////////////////////////////////
else:
	?><font class="text"><?= GetMessage("MP3_NO_SALE_MODULE") ?></font><?
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>