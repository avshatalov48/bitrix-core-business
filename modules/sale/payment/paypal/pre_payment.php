<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

class CSalePaySystemPrePayment
{
	var $username = "";
	var $pwd = "";
	var $signature = "";
	var $currency = "";
	var $serverName = "";
	var $testMode = true;
	var $domain = "";
	var $token = "";
	var $payerId = "";
	var $encoding = "";
	var $version = "";
	var $notifyUrl = "";
	var $taxAmount = "";
	var $deliveryAmount = "";

	function init()
	{
		$this->username = CSalePaySystemAction::GetParamValue("PAYPAL_USER");
		$this->pwd = CSalePaySystemAction::GetParamValue("PAYPAL_PWD");
		$this->signature = CSalePaySystemAction::GetParamValue("PAYPAL_SIGNATURE");
		$this->currency = CSalePaySystemAction::GetParamValue("PAYMENT_CURRENCY");
		$this->testMode = (CSalePaySystemAction::GetParamValue("PS_IS_TEST") == "Y");
		$this->notifyUrl = CSalePaySystemAction::GetParamValue("PAYPAL_NOTIFY_URL");

		if($this->currency == '')
			$this->currency = CSaleLang::GetLangCurrency(SITE_ID);

		if($this->testMode)
			$this->domain = "sandbox.";
		if($_REQUEST["token"] <> '')
			$this->token = $_REQUEST["token"];
		if($_REQUEST["PayerID"] <> '')
			$this->payerId = $_REQUEST["PayerID"];
		$this->version = "98.0";

		$dbSite = CSite::GetByID(SITE_ID);
		$arSite = $dbSite->Fetch();
		$this->serverName = $arSite["SERVER_NAME"];
		if ($this->serverName == '')
		{
			if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
				$this->serverName = SITE_SERVER_NAME;
			else
				$this->serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
		}
		
		$this->serverName = (CMain::IsHTTPS() ? "https" : "http")."://".$this->serverName;

		if($this->username == '' || $this->username == '' || $this->username == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("CSalePaySystempaypal: init error", "CSalePaySystempaypal_init_error");
			return false;
		}
		return true;
	}
	
	function BasketButtonShow()
	{
		if(LANGUAGE_ID == "ru")
			$imgSrc = "//www.1c-bitrix.ru/download/sale/paypal.jpg";
		elseif(LANGUAGE_ID == "de")
			$imgSrc = "//www.paypal.com/de_DE/i/btn/btn_xpressCheckout.gif";
		else
			$imgSrc = "//www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif";
		return "<input name=\"paypalbutton\" style=\"padding-top:7px;\" type=\"image\" src=\"".$imgSrc."\" value=\"".GetMessage("PPL_BUTTON")."\" onclick='var cp=BX(\"coupon\"); if (cp) cp.disabled=true;'>";
	}

	function BasketButtonAction($orderData = array())
	{
		global $APPLICATION;
		if (array_key_exists('paypalbutton_x', $_POST) && array_key_exists('paypalbutton_y', $_POST))
		{
			$url = "https://api-3t.".$this->domain."paypal.com/nvp";

			$arFields = array(
					"METHOD" => "SetExpressCheckout",
					"VERSION" => "98.0",
					"USER" => $this->username,
					"PWD" => $this->pwd,
					"SIGNATURE" => $this->signature,
					"PAYMENTREQUEST_0_AMT" => number_format($orderData["AMOUNT"], 2, ".", ""),
					"PAYMENTREQUEST_0_CURRENCYCODE" => $this->currency,
					"RETURNURL" => $this->serverName.$orderData["PATH_TO_ORDER"],
					"CANCELURL" => $this->serverName.$APPLICATION->GetCurPageParam("paypal=Y&paypal_error=Y", array("paypal", "paypal_error")),
					"PAYMENTREQUEST_0_PAYMENTACTION" => "Authorization",
					"PAYMENTREQUEST_0_DESC" => "Order payment for ".$this->serverName,
					"LOCALECODE" => ToUpper(LANGUAGE_ID),
					"buttonsource" => "Bitrix_Cart",
				);

			if(!empty($orderData["BASKET_ITEMS"]))
			{
				$arFields["PAYMENTREQUEST_0_ITEMAMT"] = number_format($orderData["AMOUNT"], 2, ".", "");
				foreach($orderData["BASKET_ITEMS"] as $k => $val)	
				{
					$arFields["L_PAYMENTREQUEST_0_NAME".$k] = $APPLICATION->ConvertCharset($val["NAME"], SITE_CHARSET, "utf-8");
					$arFields["L_PAYMENTREQUEST_0_AMT".$k] = number_format($val["PRICE"], 2, ".", "");
					$arFields["L_PAYMENTREQUEST_0_QTY".$k] = $val["QUANTITY"];
				}
			}

			$arFields["RETURNURL"] .= ((mb_strpos($arFields["RETURNURL"], "?") === false) ? "?" : "&")."paypal=Y";

			$ht = new \Bitrix\Main\Web\HttpClient(array("version" => "1.1"));
			if($res = @$ht->post($url, $arFields))
			{
				$result = $this->parseResult($res);

				if($result["TOKEN"] <> '')
				{
					$url = "https://www.".$this->domain."paypal.com/webscr?cmd=_express-checkout&token=".$result["TOKEN"];
					if($orderData["ORDER_REQUEST"] == "Y")
						return $url;
					LocalRedirect($url);
				}
				else
				{
					$GLOBALS["APPLICATION"]->ThrowException($result['L_SHORTMESSAGE0'].' : '.$result['L_LONGMESSAGE0'], "CSalePaySystemPrePayment_action_error");
					return false;
				}
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("PPL_ERROR"), "CSalePaySystemPrePayment_action_error");
				return false;
			}
		}

		return true;
	}

	function getHiddenInputs()
	{
		$result = "
			<input type=\"hidden\" name=\"paypal\" value=\"Y\">
			<input type=\"hidden\" name=\"token\" value=\"".htmlspecialcharsbx($this->token)."\">
			<input type=\"hidden\" name=\"PayerID\" value=\"".htmlspecialcharsbx($this->payerId)."\">
		";

		if($this->token <> '')
			$result .= "<span style='color: green'>".GetMessage("PPL_PREAUTH_TEXT")."<br /><br /></span>";
		return $result;
	}

	function isAction()
	{
		if($_REQUEST["paypal"] == "Y" && $this->token <> '')
			return true;
		return false;
	}

	function parseResult($data)
	{
		global $APPLICATION;

		$keyarray = array();
		$res1= explode("&", $data);
		foreach($res1 as $res2)
		{
			list($key,$val) = explode("=", $res2);
			$keyarray[urldecode($key)] = urldecode($val);
			if($this->encoding <> '')
				$keyarray[urldecode($key)] = $APPLICATION->ConvertCharset($keyarray[urldecode($key)], $this->encoding, SITE_CHARSET);
		}
		return $keyarray;

	}

	function getProps()
	{
		if($this->token <> '')
		{
			$url = "https://api-3t.".$this->domain."paypal.com/nvp";
			$arFields = array(
					"METHOD" => "GetExpressCheckoutDetails",
					"VERSION" => $this->version,
					"USER" => $this->username,
					"PWD" => $this->pwd,
					"SIGNATURE" => $this->signature,
					"TOKEN" => $this->token,
					"buttonsource" => "Bitrix_Cart",
				);

			$ht = new \Bitrix\Main\Web\HttpClient(array("version" => "1.1"));
			if($res = $ht->post($url, $arFields))
			{
				$result = $this->parseResult($res);
				if($result["ACK"] == "Success")
				{
					$arResult = array(
						"FIO" => $result["FIRSTNAME"]." ".$result["LASTNAME"],
						"EMAIL" => $result["EMAIL"],
						"ZIP" => $result["SHIPTOZIP"],
						"ADDRESS" => $result["SHIPTOSTREET"]." ".$result["SHIPTOSTREET2"],
						"COUNTRY" => $result["SHIPTOCOUNTRYNAME"],
						"STATE" => $result["SHIPTOSTATE"],
						"CITY" => $result["SHIPTOCITY"],
						"LOCATION" => $result["SHIPTOCITY"],
						"PP_SOURCE" => $result,
						);
					return $arResult;
				}
			}
		}
	}

	function payOrder($orderData = array())
	{
		if($this->token <> '')
		{
			global $APPLICATION;
			$url = "https://api-3t.".$this->domain."paypal.com/nvp";
			$arFields = array(
					"METHOD" => "GetExpressCheckoutDetails",
					"VERSION" => $this->version,
					"USER" => $this->username,
					"PWD" => $this->pwd,
					"SIGNATURE" => $this->signature,
					"TOKEN" => $this->token,
					"buttonsource" => "Bitrix_Cart",
				);

			$ht = new \Bitrix\Main\Web\HttpClient(array("version" => "1.1"));
			if($res = $ht->post($url, $arFields))
			{
				$result = $this->parseResult($res);
				if($result["ACK"] == "Success" && in_array($result["CHECKOUTSTATUS"], array("PaymentActionNotInitiated")))
				{
					$arFields["METHOD"] = "DoExpressCheckoutPayment";
					$arFields["PAYERID"] = $this->payerId;
					$arFields["PAYMENTACTION"] = "Sale";
					$arFields["PAYMENTREQUEST_0_AMT"] = number_format($this->orderAmount, 2, ".", "");
					$arFields["PAYMENTREQUEST_0_CURRENCYCODE"] = $this->currency;
					$arFields["PAYMENTREQUEST_0_DESC"] = "Order #".$this->orderId;
					$arFields["PAYMENTREQUEST_0_NOTETEX"] = "Order #".$this->orderId;
					$arFields["PAYMENTREQUEST_0_INVNUM"] = $this->orderId;
					$arFields["PAYMENTREQUEST_0_CUSTOM"] = $this->paymentId;

					if(DoubleVal($this->deliveryAmount) > 0)
					{
						$arFields["PAYMENTREQUEST_0_SHIPPINGAMT"] = number_format($this->deliveryAmount, 2, ".", "");
					}
					$orderProps = $this->getProps();

					if(!empty($orderProps))
					{
						$arFields["PAYMENTREQUEST_0_SHIPTONAME"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTONAME"], SITE_CHARSET, "utf-8");
						$arFields["PAYMENTREQUEST_0_SHIPTOSTREET"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOSTREET"], SITE_CHARSET, "utf-8");
						$arFields["PAYMENTREQUEST_0_SHIPTOSTREET2"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOSTREET2"], SITE_CHARSET, "utf-8");
						$arFields["PAYMENTREQUEST_0_SHIPTOCITY"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOCITY"], SITE_CHARSET, "utf-8");
						$arFields["PAYMENTREQUEST_0_SHIPTOSTATE"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOSTATE"], SITE_CHARSET, "utf-8");
						$arFields["PAYMENTREQUEST_0_SHIPTOZIP"] = $orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOZIP"];
						$arFields["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"] = $APPLICATION->ConvertCharset($orderProps["PP_SOURCE"]["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"], SITE_CHARSET, "utf-8");
					}

					if(!empty($orderData["BASKET_ITEMS"]))
					{
						$arFields["PAYMENTREQUEST_0_ITEMAMT"] = number_format($this->orderAmount-$this->deliveryAmount, 2, ".", "");
						foreach($orderData["BASKET_ITEMS"] as $k => $val)	
						{
							$arFields["L_PAYMENTREQUEST_0_NAME".$k] = $APPLICATION->ConvertCharset($val["NAME"], SITE_CHARSET, "utf-8");
							$arFields["L_PAYMENTREQUEST_0_AMT".$k] = number_format($val["PRICE"], 2, ".", "");
							$arFields["L_PAYMENTREQUEST_0_QTY".$k] = $val["QUANTITY"];
							$arFields["L_PAYMENTREQUEST_0_NUMBER".$k] = $val["PRODUCT_ID"];
						}
					}

					if($this->notifyUrl <> '')
						$arFields["PAYMENTREQUEST_0_NOTIFYURL"] = $this->notifyUrl;

					if($res2 = $ht->Post($url, $arFields))
					{
						$result2 = $this->parseResult($res2);

						/** @var \Bitrix\Sale\Order $order */
						$order = Order::load($this->orderId);
						$payment = $order->getPaymentCollection()->getItemById($this->paymentId);

						if($result2["ACK"] == "Success" && in_array($result2["PAYMENTINFO_0_PAYMENTSTATUS"], array("Completed")))
						{
							$payment->setField('PAID', 'Y');
							$strPS_STATUS_MESSAGE = "";
							$strPS_STATUS_MESSAGE .= "Name: ".$result["FIRSTNAME"]." ".$result["LASTNAME"]."; ";
							$strPS_STATUS_MESSAGE .= "Email: ".$result["EMAIL"]."; ";
							
							$strPS_STATUS_DESCRIPTION = "";
							$strPS_STATUS_DESCRIPTION .= "Payment status: ".$result2["PAYMENTINFO_0_PAYMENTSTATUS"]."; ";
							$strPS_STATUS_DESCRIPTION .= "Payment sate: ".$result2["PAYMENTINFO_0_ORDERTIME"]."; ";

							$arOrderFields = array(
									"PS_STATUS" => "Y",
									"PS_STATUS_CODE" => "-",
									"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
									"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
									"PS_SUM" => $result2["PAYMENTINFO_0_AMT"],
									"PS_CURRENCY" => $result2["PAYMENTINFO_0_CURRENCYCODE"],
									"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime,
									"PAY_VOUCHER_NUM" => $result2["PAYMENTINFO_0_TRANSACTIONID"],
									"PAY_VOUCHER_DATE" => new \Bitrix\Main\Type\DateTime,
								);
						}
						else
						{
							$strPS_STATUS_MESSAGE = "";
							$strPS_STATUS_MESSAGE .= "Name: ".$result["FIRSTNAME"]." ".$result["LASTNAME"]."; ";
							$strPS_STATUS_MESSAGE .= "Email: ".$result["EMAIL"]."; ";
							
							$strPS_STATUS_DESCRIPTION = "";
							$strPS_STATUS_DESCRIPTION .= "Payment status: ".$result2["PAYMENTINFO_0_PAYMENTSTATUS"]."; ";
							$strPS_STATUS_DESCRIPTION .= "Pending reason: ".$result2["PAYMENTINFO_0_PENDINGREASON"]."; ";
							$strPS_STATUS_DESCRIPTION .= "Payment sate: ".$result2["PAYMENTINFO_0_ORDERTIME"]."; ";

							$arOrderFields = array(
									"PS_STATUS" => "N",
									"PS_STATUS_CODE" => $result2["PAYMENTINFO_0_PAYMENTSTATUS"],
									"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
									"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
									"PS_SUM" => $result2["PAYMENTINFO_0_AMT"],
									"PS_CURRENCY" => $result2["PAYMENTINFO_0_CURRENCYCODE"],
									"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime,
									"PAY_VOUCHER_NUM" => $result2["PAYMENTINFO_0_TRANSACTIONID"],
									"PAY_VOUCHER_DATE" => new \Bitrix\Main\Type\DateTime,
								);
						}

						$result = $payment->setFields($arOrderFields);
						if ($result->isSuccess())
							$order->save();
					}
				}
			}
		}
	}
}
?>