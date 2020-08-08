<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
include_once(dirname(__FILE__)."/init_vars.php");
include(GetLangFileName(dirname(__FILE__)."/lang/", "/mp3.php"));

$APPLICATION->SetTitle(GetMessage("MP3_LIST_TITLE"));

if (CModule::IncludeModule("sale")):
/////////////////////////////////////////////////////////////////////////////
?>

<?
$baseLangCurrency = CSaleLang::GetLangCurrency(SITE_ID);

$errorMessage = "";
$successMessage = "";
if ($USER->IsAuthorized() && ($action == "buy"))
{
	if (!isset($buy_mp3))
		$errorMessage .= GetMessage("MP3_ERROR_EMPTY_MP3")."<br>";

	if ($errorMessage == '')
	{
		if (!is_array($buy_mp3))
			$buy_mp3 = array($buy_mp3);

		$arBuyMP3 = array();
		for ($i = 0; $i < count($buy_mp3); $i++)
		{
			$buy_mp3[$i] = str_replace("/", "", $buy_mp3[$i]);
			$buy_mp3[$i] = str_replace("\\", "", $buy_mp3[$i]);
			$buy_mp3[$i] = Trim($buy_mp3[$i]);
			while (mb_substr($buy_mp3[$i], 0, 1) == ".")
				$buy_mp3[$i] = mb_substr($buy_mp3[$i], 1);
			if ($buy_mp3[$i] <> '')
				if (file_exists($mp3Path2Original.$buy_mp3[$i]) && is_file($mp3Path2Original.$buy_mp3[$i]))
					if (!CSaleAuxiliary::CheckAccess($USER->GetID(), $mp3AuxiliaryPrefix.$buy_mp3[$i], $mp3AccessTimeLength, $mp3AccessTimeType))
						$arBuyMP3[] = $buy_mp3[$i];
		}

		if (count($arBuyMP3) <= 0)
			$errorMessage .= GetMessage("MP3_ERROR_EMPTY_MP3")."<br>";
	}

	if ($errorMessage == '')
	{
		$userBudget = 0;
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array(
						"USER_ID" => $USER->GetID(),
						"CURRENCY" => $baseLangCurrency
					)
			);
		if ($arUserAccount = $dbUserAccount->Fetch())
			$userBudget = DoubleVal($arUserAccount["CURRENT_BUDGET"]);

		if ($userBudget <= 0)
			$errorMessage .= GetMessage("MP3_ERROR_NO_MONEY")."<br>";
	}

	if ($errorMessage == '')
	{
		$itemPrice = $mp3Price;
		if ($mp3Currency != $baseLangCurrency)
			$itemPrice = roundEx(CCurrencyRates::ConvertCurrency($mp3Price, $mp3Currency, $baseLangCurrency), SALE_VALUE_PRECISION);

		if ($mp3Price > 0 && $itemPrice <= 0)
		{
			$itemPrice = 1;
			for ($i = 0; $i < SALE_VALUE_PRECISION; $i++)
				$itemPrice = $itemPrice / 10;
		}

		$paySum = count($arBuyMP3) * $itemPrice;

		if ($paySum > $userBudget)
			$errorMessage .= str_replace("#PRICE#", SaleFormatCurrency($paySum, $baseLangCurrency), str_replace("#SUM#", SaleFormatCurrency($userBudget, $baseLangCurrency), GetMessage("MP3_ERROR_NOT_ENOUGH")))."<br>";
	}

	if ($errorMessage == '')
	{
		if (!CSaleUserAccount::Pay($USER->GetID(), $paySum, $baseLangCurrency, 0, False))
			$errorMessage .= str_replace("#PRICE#", SaleFormatCurrency($paySum, $baseLangCurrency), GetMessage("MP3_ERROR_PAY"))."<br>";
	}

	if ($errorMessage == '')
	{
		CSaleAuxiliary::DeleteByTime($mp3AccessTimeLength, $mp3AccessTimeType);
		for ($i = 0; $i < count($arBuyMP3); $i++)
		{
			$arFields = array(
					"USER_ID" => $USER->GetID(),
					"ITEM" => $arBuyMP3[$i],
					"ITEM_MD5" => $mp3AuxiliaryPrefix.$arBuyMP3[$i],
					"DATE_INSERT" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)))
				);
			$auxiliaryID = CSaleAuxiliary::Add($arFields);
			$auxiliaryID = intval($auxiliaryID);
			if ($auxiliaryID <= 0)
				$errorMessage .= str_replace("#COMP#", $arBuyMP3[$i], GetMessage("MP3_ERROR_AUXILIARY"))."<br>";
		}
	}

	if ($errorMessage == '')
		$successMessage .= GetMessage("MP3_SUCCESS_MESSAGE")."<br>";
}


$arMP3Files = array();
if (is_dir($mp3Path2Original))
{
	$hPath2Original = opendir($mp3Path2Original);
	while (($fileName = readdir($hPath2Original)) != false)
	{
		if (filetype($mp3Path2Original.$fileName) == "file" && mb_substr($fileName, 0, 1) != ".")
		{
			if (strrchr(mb_strtolower($fileName), ".") == ".mp3")
			{
				$fileDate = filemtime($mp3Path2Original.$fileName);
				$arMP3Files[$fileName] = $fileDate;
			}
		}
	}
	closedir($hPath2Original);
}
asort($arMP3Files);
$arMP3Files = array_reverse($arMP3Files);
?>

<?= ShowError($errorMessage); ?>
<?= ShowNote($successMessage, "oktext"); ?>

<?
if ($USER->IsAuthorized())
{
	$dbUserAccount = CSaleUserAccount::GetList(
			array(),
			array(
					"USER_ID" => $USER->GetID(),
					"CURRENCY" => $baseLangCurrency
				)
		);
	if (!($arUserAccount = $dbUserAccount->Fetch()))
		$arUserAccount = array("CURRENT_BUDGET" => 0);
	?>
	<font class="text">
	<?
	$userName = $USER->GetFullName();
	if ($userName <> '')
		echo str_replace("#NAME#", htmlspecialchars($userName), GetMessage("MP3_ADDRESS1"));
	else
		echo GetMessage("MP3_ADDRESS2");
	?>
	<?= str_replace("#SUM#", SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $baseLangCurrency), GetMessage("MP3_USER_ACCT_SUM")); ?>
	<?= str_replace("#URL#", "buy_money.php", GetMessage("MP3_USER_BUY_MONEY")); ?>
	</font><br><br>
	<?
}
else
{
	?>
	<font class="text">
	<?= GetMessage("MP3_REG_PROMT"); ?>
	<?= str_replace("#URL#", "auth_site.php", GetMessage("MP3_AUTH_LINK")); ?>
	<?= str_replace("#URL#", "auth_site.php?register=yes", GetMessage("MP3_REG_LINK")); ?>
	</font><br><br>
	<?
}
?>

<font class="text">
<?= str_replace("#PRICE#", SaleFormatCurrency($mp3Price, $mp3Currency), GetMessage("MP3_FILE_PRICE")); ?>
</font><br><br>

<script language="JavaScript">
<!--
function OnSelectAll(fl)
{
	for (var i = 0; i < document.mp3_form.elements.length; i++)
	{
		if (document.mp3_form.elements[i].type == "checkbox")
		{
			if (document.mp3_form.elements[i].name == "buy_mp3[]")
				document.mp3_form.elements[i].checked = fl;
		}
	}
}
//-->
</script>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" name="mp3_form">
<table border="0" cellpadding="0" cellspacing="0" class="tableborder" width="100%"><tr><td>
<table border="0" cellspacing="1" width="100%" cellpadding="2">
	<tr>
		<td class="tablehead">
			<font class="tableheadtext"><input type="checkbox" name="select_all" value="Y" onClick="OnSelectAll(this.checked)"></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_NAME") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_AUTHOR") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_ALBUM") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_GENRE") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_TIME") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_SIZE") ?></font>
		</td>
		<td class="tablehead">
			<font class="tableheadtext"><?= GetMessage("MP3_LIST_BIT") ?></font>
		</td>
	</tr>
<?
while (list($fileName, $fileDate) = each($arMP3Files))
{
	$arMP3Tags = ReadMP3Tags($mp3Path2Original.$fileName);
	$bCanAccess = False;
	if (CSaleAuxiliary::CheckAccess($USER->GetID(), $mp3AuxiliaryPrefix.$fileName, $mp3AccessTimeLength, $mp3AccessTimeType))
		$bCanAccess = True;
	?>
	<tr>
		<td class="tablebody">
			<font class="tablebodytext"><input type="checkbox" name="buy_mp3[]" <?if ($bCanAccess) echo "disabled checked"; elseif(is_array($buy_mp3) && in_array($fileName, $buy_mp3)) echo "checked";?> value="<?= htmlspecialchars($fileName) ?>"></font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext">
			<?
			if ($arMP3Tags["title"] <> '')
			{
				if ($bCanAccess)
					echo '<a href="'.$mp3Url2Original.$fileName.'">';
				echo $arMP3Tags["title"];
				if ($bCanAccess)
					echo '</a>';
			}
			else
				echo "&nbsp;";
			?>
			</font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext">
			<?
			if ($arMP3Tags["artist"] <> '')
				echo $arMP3Tags["artist"];
			else
				echo "&nbsp;";
			?>
			</font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext">
			<?
			echo $arMP3Tags["album"];
			if ($arMP3Tags["year"] <> '')
				echo " (".$arMP3Tags["year"].")";
			?>&nbsp;</font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext"><?= $arMP3Tags["genre"] ?></font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext"><?= $arMP3Tags["lenght"] ?></font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext"><?= number_format($arMP3Tags["filesize"]/1000000.0, 1, ".", "") ?> Mb</font>
		</td>
		<td class="tablebody">
			<font class="tablebodytext"><?= $arMP3Tags["bitrate"] ?></font>
		</td>
	</tr>
	<?
}
?>
</table>
</td></tr></table><br>
<input type="hidden" name="action" value="buy">
<input type="submit" value="<?= GetMessage("MP3_LIST_BUTTON") ?>" class="inputbuttonflat">
</form>
<?
/******************************************************************************/
/*********  END OF MP3 CATALOG  ***********************************************/
/******************************************************************************/
?>

<?
/////////////////////////////////////////////////////////////////////////////
else:
	?><font class="text"><?= GetMessage("MP3_NO_SALE_MODULE") ?></font><?
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>