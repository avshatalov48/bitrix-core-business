<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));
if(!function_exists("bx_hmac"))
{
	function bx_hmac($algo, $data, $key, $raw_output = false) 
	{
		$algo = mb_strtolower($algo);
		$pack = "H".mb_strlen($algo("test"));
		$size = 64; 
		$opad = str_repeat(chr(0x5C), $size); 
		$ipad = str_repeat(chr(0x36), $size); 

		if (mb_strlen($key) > $size) {
			$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00)); 
		} else { 
			$key = str_pad($key, $size, chr(0x00)); 
		} 

		$lenKey = mb_strlen($key) - 1;
		for ($i = 0; $i < $lenKey; $i++) { 
			$opad[$i] = $opad[$i] ^ $key[$i]; 
			$ipad[$i] = $ipad[$i] ^ $key[$i]; 
		} 

		$output = $algo($opad.pack($pack, $algo($ipad.$data))); 
		return ($raw_output) ? pack($pack, $output) : $output; 
	} 
}

$amount = CSalePaySystemAction::GetParamValue("SHOULD_PAY"); 
$amount = number_format($amount, 2, ".", "");
$currency = CSalePaySystemAction::GetParamValue("CURRENCY"); 
if($currency == '')
	$currency = "UAH";

$order = CSalePaySystemAction::GetParamValue("ORDER_ID"); 
if(mb_strlen($order) < 6)
{
	$n = 6 - mb_strlen($order);
	for($i = 0; $i < $n; $i++)
		$order = "0".$order;
}

$desc = trim(CSalePaySystemAction::GetParamValue("ORDER_DESC").CSalePaySystemAction::GetParamValue("ORDER_ID")); 
$m_name = CSalePaySystemAction::GetParamValue("MERCH_NAME"); 
$m_url = CSalePaySystemAction::GetParamValue("MERCH_URL"); 
$merchant = CSalePaySystemAction::GetParamValue("MERCHANT");
$terminal = CSalePaySystemAction::GetParamValue("TERMINAL");
$email = CSalePaySystemAction::GetParamValue("EMAIL"); 
$backref = htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_RESULT")); 
$mac = CSalePaySystemAction::GetParamValue("MAC");

if(CSalePaySystemAction::GetParamValue("IS_TEST") <> '')
	$server_url = "https://3ds.eximb.com:443/cgi-bin/cgi_test";
else
	$server_url = "https://3ds.eximb.com/cgi-bin/cgi_link";

$trtype = 0;  
$country = ""; 
$merch_gmt = ""; 
$time = ""; 

$var = unpack("H*r", ToUpper(mb_substr(md5(uniqid(30)), 0, 8)));
$nonce = $var[r];

$key = pack("H*", $mac);   
$time = gmdate("YmdHis", time());

$sign = bx_hmac("sha1", 
		($amount <> '' ? mb_strlen($amount).$amount : "-").
		($currency <> '' ? mb_strlen($currency).$currency : "-").
		($order <> '' ? mb_strlen($order).$order : "-").
		($desc <> '' ? mb_strlen($desc).$desc : "-").
		($m_name <> '' ? mb_strlen($m_name).$m_name : "-").
		($m_url <> '' ? mb_strlen($m_url).$m_url : "-").
		($merchant <> '' ? mb_strlen($merchant).$merchant : "-").
		($terminal <> '' ? mb_strlen($terminal).$terminal : "-").
		($email <> '' ? mb_strlen($email).$email : "-").
		($trtype <> '' ? mb_strlen($trtype).$trtype : "-").
		"--".
		($time <> '' ? mb_strlen($time).$time : "-").
		($nonce <> '' ? mb_strlen($nonce).$nonce : "-").
		($backref <> '' ? mb_strlen($backref).$backref : "-")
		, 
		$key
	);
?>

<form name="cardform" action="<?=$server_url?>" method="post">
	<input type="hidden" name="TRTYPE" VALUE="<?=$trtype?>">
	<input type="hidden" name="AMOUNT" value="<?=$amount?>">
	<input type="hidden" name="CURRENCY" value="<?=$currency?>">
	<input type="hidden" name="ORDER" value="<?=$order?>">
	<input type="hidden" name="DESC" value="<?=htmlspecialcharsbx($desc)?>">
	<input type="hidden" name="MERCH_NAME" value="<?=htmlspecialcharsbx($m_name)?>">
	<input type="hidden" name="MERCH_URL" value="<?=htmlspecialcharsbx($m_url)?>">
	<input type="hidden" name="MERCHANT" value="<?=htmlspecialcharsbx($merchant)?>">
	<input type="hidden" name="TERMINAL" value="<?=htmlspecialcharsbx($terminal)?>">
	<input type="hidden" name="EMAIL" value="<?=htmlspecialcharsbx($email)?>">
	<input type="hidden" name="LANG" value="">
	<input type="hidden" name="BACKREF" value="<?=$backref?>">
	<input type="hidden" name="NONCE" value="<?=$nonce?>">
	<input type="hidden" name="P_SIGN" value="<?=$sign?>">
	<input type="hidden" name="TIMESTAMP" value="<?=$time?>">
	<input type="submit" class="btn btn-primary" value="<?=GetMessage("PAY_BUTTON")?>" name="send_button">
</form>