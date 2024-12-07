<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\ServiceResult;

Loc::loadMessages(__FILE__);

class AuthorizeHandler extends PaySystem\BaseServiceHandler
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$instance = Application::getInstance();
			$context = $instance->getContext();
			$request = $context->getRequest();
		}

		if ($request->get('ccard_num') !== null)
		{
			$serviceResult = new ServiceResult();
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();

			$params = $this->getParamsBusValue($payment);

			$queryString  = "x_version=3.1";
			$queryString .= "&x_login=".urlencode($params["AUTHORIZE_LOGIN"]);
			$queryString .= "&x_tran_key=".urlencode($params["AUTHORIZE_TRANSACTION_KEY"]);
			$queryString .= "&x_test_request=".($this->isTestMode($payment) ? "TRUE" : "FALSE");

			$queryString .= "&x_delim_data=True";
			$queryString .= "&x_relay_response=False";
			$queryString .= "&x_delim_char=,";
			$queryString .= "&x_encap_char=|";

			$arTmp = array(
				"x_first_name" => "BUYER_PERSON_NAME_FIRST",	"x_last_name" => "BUYER_PERSON_LAST_NAME",
				"x_company" => "BUYER_PERSON_COMPANY",	"x_address" => "BUYER_PERSON_ADDRESS",	"x_city" => "BUYER_PERSON_CITY",
				"x_state" => "BUYER_PERSON_STATE",	"x_zip" => "BUYER_PERSON_ZIP",	"x_country" => "BUYER_PERSON_COUNTRY",
				"x_phone" => "BUYER_PERSON_PHONE",	"x_fax" => "BUYER_PERSON_FAX"
			);
			foreach ($arTmp as $key => $value)
			{
				if (array_key_exists($value, $params))
					$queryString .= "&".$key."=".urlencode($params[$key]);
			}

			$queryString .= "&x_cust_id=".urlencode($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["USER_ID"]);
			$queryString .= "&x_customer_ip=".urlencode($_SERVER["REMOTE_ADDR"]);

			if (array_key_exists('BUYER_PERSON_EMAIL', $params))
				$queryString .= "&x_email=".urlencode($params['BUYER_PERSON_EMAIL']);

			$queryString .= "&x_email_customer=FALSE";
			$queryString .= "&x_merchant_email=".urlencode(\COption::GetOptionString("sale", "order_email", ""));

			$queryString .= "&x_invoice_num=".urlencode($params['PAYMENT_ID']);
			$queryString .= "&x_description=".urlencode($payment->getField('DATE_BILL'));

			$arTmp = array(
				"x_ship_to_first_name" => "SHIP_BUYER_PERSON_NAME_FIRST", "x_ship_to_last_name" => "SHIP_BUYER_PERSON_NAME_LAST",
				"x_ship_to_company" => "SHIP_BUYER_PERSON_COMPANY", "x_ship_to_address" => "SHIP_BUYER_PERSON_ADDRESS",
				"x_ship_to_city" => "SHIP_BUYER_PERSON_CITY", "x_ship_to_state" => "SHIP_BUYER_PERSON_STATE",
				"x_ship_to_zip" => "SHIP_BUYER_PERSON_ZIP", "x_ship_to_country" => "SHIP_BUYER_PERSON_COUNTRY"
			);
			foreach ($arTmp as $key => $value)
			{
				if (array_key_exists($value, $params))
					$queryString .= "&".$key."=".urlencode($params[$key]);
			}

			$queryString .= "&x_amount=".urlencode($params["PAYMENT_SHOULD_PAY"]);
			$queryString .= "&x_currency_code=".urlencode($payment->getField("CURRENCY"));
			$queryString .= "&x_method=CC";
			$queryString .= "&x_type=AUTH_CAPTURE";
			$queryString .= "&x_recurring_billing=NO";
			$queryString .= "&x_card_num=".urlencode($request->get('ccard_num'));
			$queryString .= "&x_exp_date=".urlencode($request->get('ccard_date1').$request->get('ccard_date2'));
			$queryString .= "&x_card_code=".urlencode($request->get('ccard_code'));

			$queryString .= "&x_tax=".urlencode($order->getTaxValue());
			$queryString .= "&x_freight=".urlencode($order->getDeliveryPrice());

			$http = new HttpClient();
			$result = $http->post($this->getUrl($payment, 'pay'), $queryString);
			if ($result)
			{
				$mass = explode("|,|", "|,".$result);

				$hashValue = $params["AUTHORIZE_SECRET_KEY"];
				if ($hashValue <> '')
				{
					if (md5($hashValue.($params["AUTHORIZE_LOGIN"]).$mass[7].sprintf("%.2f", $params["PAYMENT_SHOULD_PAY"])) != mb_strtolower($mass[38]))
					{
						$mass = array();
						$mass[1] = 3;
						$mass[4] = "MD5 transaction signature is incorrect!";
						$mass[3] = 0;
						$mass[2] = 0;
					}
				}

				$psStatus = ((int)$mass[1] == 1) ? "Y" : "N";
				$psStatusCode = $mass[3];
				if ($psStatus == "Y")
				{
					$psStatusDescription = "Approval Code: ".$mass[5].(!empty($mass[7]) ? "; Transaction ID: ".$mass[7] : "");
				}
				else
				{
					$psStatusDescription = (int)($mass[1]) == 2 ? "Declined" : "Error";
					$psStatusDescription .= ": ".$mass[4]." (Reason Code ".$mass[3]." / Sub ".$mass[2].")";

					$errorMsg = ((int)$mass[1] == 2) ? "Transaction was declined" : "Error while processing transaction";
					$errorMsg .= ": ".$mass[4]." (".$mass[3]."/".$mass[2].")";

					$serviceResult->addError(new Error($errorMsg));
				}

				$psStatusMsg = "";
				if (!empty($mass[6]))
					$psStatusMsg .= "\nAVS Result: [".$mass[6]."] ".Loc::getMessage("AN_AVS_".$mass[6]).";";

				if (!empty($mass[39]))
					$psStatusMsg .= "\nCard Code Result: [".$mass[39]."] ".Loc::getMessage('AN_CVV_'.$mass[39]).";";

				if (!empty($mass[40]))
					$psStatusMsg .= "\nCAVV: [".$mass[40]."] ".Loc::getMessage('AN_CAVV_'.$mass[40]).";";

				$psData = array(
					"PS_STATUS" => $psStatus,
					"PS_STATUS_CODE" => $psStatusCode,
					"PS_STATUS_DESCRIPTION" => $psStatusDescription,
					"PS_STATUS_MESSAGE" => $psStatusMsg,
					"PS_SUM" => $mass[10],
					"PS_CURRENCY" => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					"PS_RESPONSE_DATE" => new DateTime()
				);
				$serviceResult->setPsData($psData);

				foreach ($http->getError() as $code => $error)
				{
					$serviceResult->addError(new Error($error, $code));
				}
			}

			return $serviceResult;
		}

		$this->setExtraParams([
			'PAYMENT_ID' => $payment->getId(),
			'PAYSYSTEM_ID' => $this->service->getField('ID'),
		]);
		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array();
	}

	/**
	 * @return array
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => 'https://secure.authorize.net/gateway/transact.dll'
		);
	}

}

