<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$butiksnr = CSalePaySystemAction::GetParamValue("SHOP_CODE");
$ordrenr = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
$belob = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$testthis = ((CSalePaySystemAction::GetParamValue("TEST_TRANSACTION")) ? "&test=TRUE" : "");

$langthis = "en";
if (LANGUAGE_ID=="dk" || LANGUAGE_ID=="de")
	$langthis = LANGUAGE_ID;

$valuta = 840;
if ($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]=="DKK")
	$valuta = 208;
if ($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]=="EUR")
	$valuta = 978;

$SERVER_NAME_tmp = "";
if (defined("SITE_SERVER_NAME"))
	$SERVER_NAME_tmp = SITE_SERVER_NAME;
if (strlen($SERVER_NAME_tmp)<=0)
	$SERVER_NAME_tmp = COption::GetOptionString("main", "server_name", "");

$afvist = "http://".$SERVER_NAME_tmp;
$godkendt = "http://".$SERVER_NAME_tmp;
?>
<script type="text/javascript">
function open_window(url)
{
	sealWin = window.open(url,'Payment','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=no,width=330,height=420,top=200,left=300');
}
</script>
<input type="submit" value="Payment" name="B1" class="inputbutton" 
	onclick="javascript:open_window('https://betaling.viborgnet.dk/payment/payment.php?butiksnr=<?echo $butiksnr ?>&ordrenr=<?echo $ordrenr ?>&belob=<?echo $belob ?><?echo $testthis ?>&lang=<?echo $langthis ?>&valuta=<?echo $valuta ?>&afvist=<?echo $afvist ?>&godkendt=<?echo $godkendt ?>&stylesheet=')">