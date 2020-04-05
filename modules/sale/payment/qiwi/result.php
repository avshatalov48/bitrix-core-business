<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;

Loc::loadLanguageFile(Path::combine(__DIR__, "statuses.php"));

$orderID 	= strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0 ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$login 		= CSalePaySystemAction::GetParamValue("API_LOGIN");
$password 	= CSalePaySystemAction::GetParamValue("API_PASSWORD");
$shopId		= CSalePaySystemAction::GetParamValue("SHOP_ID");
$changePayStatus = CSalePaySystemAction::GetParamValue("CHANGE_STATUS_PAY") == "Y";
$statusUrl	= "https://w.qiwi.com/api/v2/prv/{prv_id}/bills/{bill_id}";

$request = new HttpClient();
$request->setAuthorization($login, $password);
$request->setHeader("Accept", "text/json");
$request->setCharset("utf-8");

$response = $request->get(str_replace(
	array("{prv_id}", "{bill_id}"),
	array($shopId, 	$orderID),
	$statusUrl
));

if($response === false)
	return 1;

$response = (array)json_decode($response);
if(!$response || !isset($response['response']))
	return 1;
$response = (array)$response['response'];

if((int)$response['result_code'])
{
	CSaleOrder::Update($orderID, array(
		"PS_STATUS" 		=> "N",
		"PS_STATUS_CODE" 	=> $response['result_code'],
		"PS_STATUS_MESSAGE" 	=> Loc::getMessage("SALE_QWH_ERROR_CODE_" . $response['result_code']),
		"PS_STATUS_DESCRIPTION" => isset($response['description']) ? $response['description'] : "",
		"PS_RESPONSE_DATE"	=> \Bitrix\Main\Type\DateTime::createFromTimestamp(time())
	));
}
elseif(isset($response['bill']))
{
	$bill = (array)$response['bill'];

	if($order = CSaleOrder::getByID($bill['bill_id']))
	{
		$paidInfo = array(
			"PS_STATUS" 		=> $bill['status'] == "paid" ? "Y" : "N",
			"PS_STATUS_CODE"	=> substr($bill['status'], 0, 10),
			"PS_STATUS_MESSAGE" => Loc::getMessage("SALE_QWH_STATUS_MESSAGE_" . strtoupper($bill['status'])),
			"PS_RESPONSE_DATE"	=> \Bitrix\Main\Type\DateTime::createFromTimestamp(time()),
			"PS_SUM"			=> (double)$bill['amount'],
			"PS_CURRENCY"		=> $bill['ccy'],
			'PS_STATUS_DESCRIPTION'	=> ''
		);

		foreach($bill as $key => $value)
			$paidInfo['PS_STATUS_DESCRIPTION'] .= "{$key}:{$value}, ";

		CSaleOrder::Update($orderID, $paidInfo);

		if($bill['status'] == "paid" && (double)$bill['amount'] == (double)$order['PRICE'] && $changePayStatus)
		{
			CSaleOrder::payOrder($orderID, "Y", true, true);
		}
	}
}
?>