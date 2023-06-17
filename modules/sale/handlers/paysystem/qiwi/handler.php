<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Main\Application;
use Bitrix\Sale\PriceMaths;

class QiwiHandler extends PaySystem\ServiceHandler implements PaySystem\ICheckable
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		$phone = $this->normalizePhone($this->getPhone($payment, $request));
		if (!preg_match('/^\+7\d{10}$/', $phone))
		{
			$params = [
				'PAYMENT_ID' => $payment->getId(),
				'PAYSYSTEM_ID' => $this->service->getField('ID'),
				'BUYER_PERSON_PHONE' => $phone,
				'RETURN_URL' => $this->getSuccessUrl($payment),
			];
			$this->setExtraParams($params);
			return $this->showTemplate($payment, 'template_query');
		}

		$params = [
			'URL' => $this->getUrl($payment, 'pay'),
			'BUYER_PERSON_PHONE' => $phone,
			'QIWI_SUCCESS_URL' => $this->getSuccessUrl($payment),
			'QIWI_FAIL_URL' => $this->getFailUrl($payment),
		];
		$this->setExtraParams($params);
		return $this->showTemplate($payment, 'template');
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return string
	 */
	private function getPhone(Payment $payment, Request $request): string
	{
		$newPhone = trim((string)$request->getPost('NEW_PHONE'));
		if ($newPhone !== '')
		{
			return $newPhone;
		}

		return (string)$this->getBusinessValue($payment, 'BUYER_PERSON_PHONE');
	}

	/**
	 * @param $number
	 * @return bool|string|string[]|null
	 */
	private function normalizePhone($number)
	{
		$normalizedNumber = \NormalizePhone($number);
		if ($normalizedNumber)
		{
			return '+'.$normalizedNumber;
		}

		return $number;
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('txn_id', 'to', 'from', 'summ');
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('bill_id');
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => array(
				self::ACTIVE_URL => 'https://w.qiwi.com/order/external/create.action'
			),
			'check' => array(
				self::ACTIVE_URL => 'https://w.qiwi.com/api/v2/prv/{prv_id}/bills/{bill_id}'
			)
		);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		return $this->processNoticeAction($payment, $request);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processNoticeAction(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$instance = \Bitrix\Main\Application::getInstance();
		$context = $instance->getContext();
		$server = $context->getServer();

		if ($this->getBusinessValue($payment, 'QIWI_AUTHORIZATION') == 'OPEN')
		{
			$login = $this->getBusinessValue($payment, 'QIWI_SHOP_ID');
			$password = $this->getBusinessValue($payment, 'QIWI_NOTICE_PASSWORD');

			if (!$this->checkAuth($login, $password))
			{
				$result->setData(array('CODE' => 'QIWI_WALLET_ERROR_CODE_AUTH'));
				return $result;
			}
		}
		else
		{
			if ($server->get('HTTP_X_API_SIGNATURE') !== null && $this->getBusinessValue($payment, 'QIWI_API_PASSWORD'))
			{
				$key = $this->getBusinessValue($payment, 'QIWI_API_PASSWORD');
				$postParams = $_POST;
				ksort($postParams);
				$check = base64_encode(sha1($key, implode("|", array_values($postParams))));

				if ($check != $server->get('HTTP_X_API_SIGNATURE'))
				{
					$result->setData(array('CODE' => 'QIWI_WALLET_ERROR_CODE_AUTH'));
					return $result;
				}
			}
			else
			{
				$result->setData(array('CODE' => 'QIWI_WALLET_ERROR_CODE_AUTH'));
				return $result;
			}
		}


		$fields = array(
			"PS_STATUS" 		=> $request->get('status') == "paid" ? "Y" : "N",
			"PS_STATUS_CODE"	=> mb_substr($request->get('status'), 0, 5),
			"PS_STATUS_MESSAGE" => Loc::getMessage("SALE_QWH_STATUS_MESSAGE_".mb_strtoupper($_POST['status'])),
			"PS_RESPONSE_DATE"	=> new DateTime(),
			"PS_SUM"			=> (double)$request->get('amount'),
			"PS_CURRENCY"		=> $request->get('ccy'),
			"PS_STATUS_DESCRIPTION" => ""
		);

		if ((int)$request->get('error') > 0)
		{
			$paidInfo['PS_STATUS_DESCRIPTION'] = "Error: ".Loc::getMessage("SALE_HPS_QIWI_ERROR_CODE_".$request->get('error'));
			$result->setPsData($fields);
			$result->setData(array('CODE' => 'QIWI_WALLET_ERROR_CODE_OTHER'));

			return $result;
		}

		foreach($_POST as $key => $value)
			$fields['PS_STATUS_DESCRIPTION'] .= $key.':'.$value.', ';

		$result->setPsData($fields);

		$changeStatusPay = $this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') == "Y";

		if ($request->get('status') == "paid" && $changeStatusPay)
		{
			$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			$result->setData(array('CODE' => 'QIWI_WALLET_ERROR_CODE_NONE'));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return false;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB', 'USD');
	}

	/**
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		global $APPLICATION;

		$APPLICATION->RestartBuffer();

		$data = $result->getData();

		header("Content-Type: text/xml");
		header("Pragma: no-cache");
		$xml = '<?xml version="1.0" encoding="UTF-8"?><result><result_code>'.$this->getErrorCodeValue($data['CODE']).'</result_code></result>';

		$charsetConverter = \CharsetConverter::getInstance();

		$instance = Application::getInstance();
		$context = $instance->getContext();
		$culture = $context->getCulture();
		$siteCharset = $culture->getCharset();

		echo  $charsetConverter->ConvertCharset($xml, $siteCharset, "utf-8");
		die();
	}

	/**
	 * @return bool|null|string
	 */
	protected function getAuthHeader()
	{
		$instance = \Bitrix\Main\Application::getInstance();
		$context = $instance->getContext();
		$server = $context->getServer();

		$incomingToken = false;
		if ($server->get("REMOTE_USER") !== null)
		{
			$incomingToken = $server->get("REMOTE_USER");
		}
		elseif ($server->get("REDIRECT_REMOTE_USER") !== null)
		{
			$incomingToken = $server->get("REDIRECT_REMOTE_USER");
		}
		elseif ($server->get("HTTP_AUTHORIZATION") !== null)
		{
			$incomingToken = $server->get("HTTP_AUTHORIZATION");
		}
		elseif (function_exists("apache_request_headers"))
		{
			$headers = \apache_request_headers();

			if(array_key_exists("Authorization", $headers))
				$incomingToken = $headers["Authorization"];
		}
		return $incomingToken;
	}

	/**
	 * @param $login
	 * @param $password
	 * @return bool
	 */
	protected function checkAuth($login, $password)
	{
		if($password == '')
			return false;

		$header = $this->getAuthHeader();

		if(!$header)
			return false;

		$check = 'Basic '.base64_encode($login.':'.$password);
		return $header == $check;
	}

	/**
	 * @param $code
	 * @return mixed
	 */
	protected function getErrorCodeValue($code)
	{
		$codes = array(
			'QIWI_WALLET_ERROR_CODE_NONE' => 0,
			'QIWI_WALLET_ERROR_CODE_BAD_REQUEST' => 5,
			'QIWI_WALLET_ERROR_CODE_BUSY' => 13,
			'QIWI_WALLET_ERROR_CODE_AUTH' => 150,
			'QIWI_WALLET_ERROR_CODE_NOT_FOUND' => 210,
			'QIWI_WALLET_ERROR_CODE_EXISTS' => 215,
			'QIWI_WALLET_ERROR_CODE_TOO_LOW' => 241,
			'QIWI_WALLET_ERROR_CODE_TOO_HIGH' => 242,
			'QIWI_WALLET_ERROR_CODE_NO_PURSE' => 298,
			'QIWI_WALLET_ERROR_CODE_OTHER' => 300
		);

		return $codes[$code];
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function check(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'check');

		$request = new HttpClient();
		$request->setAuthorization($this->getBusinessValue($payment, 'QIWI_API_LOGIN'), $this->getBusinessValue($payment, 'QIWI_API_PASSWORD'));
		$request->setHeader("Accept", "text/json");
		$request->setCharset("utf-8");

		$response = $request->get(str_replace(
			array("{prv_id}", "{bill_id}"),
			array($this->getBusinessValue($payment, 'QIWI_SHOP_ID'), $this->getBusinessValue($payment, 'PAYMENT_ID')),
			$url
		));

		if($response === false)
		{
			$result->addErrors($request->getError());
			return $result;
		}

		$response = (array)json_decode($response);
		if(!$response || !isset($response['response']))
		{
			$result->addErrors($request->getError());
			return $result;
		}

		$response = (array)$response['response'];
		if ((int)$response['result_code'])
		{
			$psData = array(
				"PS_STATUS" => 'N',
				"PS_STATUS_CODE" => $response['result_code'],
				"PS_STATUS_MESSAGE" => Loc::getMessage("SALE_QWH_ERROR_CODE_" . $response['result_code']),
				"PS_STATUS_DESCRIPTION" => isset($response['description']) ? $response['description'] : "",
				"PS_RESPONSE_DATE" => new DateTime()
			);

			$result->setPsData($psData);
		}
		elseif(isset($response['bill']))
		{
			$bill = (array)$response['bill'];

			$psData = array(
				"PS_STATUS" => $bill['status'] == "paid" ? "Y" : "N",
				"PS_STATUS_CODE" => mb_substr($bill['status'], 0, 10),
				"PS_STATUS_MESSAGE" => Loc::getMessage("SALE_QWH_STATUS_MESSAGE_".mb_strtoupper($bill['status'])),
				"PS_RESPONSE_DATE"	=> new DateTime(),
				"PS_SUM" => (double)$bill['amount'],
				"PS_CURRENCY" => $bill['ccy'],
				'PS_STATUS_DESCRIPTION'	=> ''
			);

			foreach($bill as $key => $value)
				$psData['PS_STATUS_DESCRIPTION'] .= "{$key}:{$value}, ";

			$billAmount = PriceMaths::roundPrecision($bill['amount']);
			$paymentSum = PriceMaths::roundPrecision($payment->getSum());

			if($bill['status'] == "paid" && $billAmount == $paymentSum && $this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY'))
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);

			$result->setPsData($psData);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getSuccessUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'QIWI_SUCCESS_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getFailUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'QIWI_FAIL_URL') ?: $this->service->getContext()->getUrl();
	}
}