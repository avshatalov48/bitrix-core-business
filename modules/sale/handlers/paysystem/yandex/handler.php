<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;

Loc::loadMessages(__FILE__);

/**
 * Class YandexHandler
 * @package Sale\Handlers\PaySystem
 */
class YandexHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefundExtended, PaySystem\IHold
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$params = array(
			'URL' => $this->getUrl($payment, 'pay'),
			'PS_MODE' => $this->service->getField('PS_MODE'),
			'BX_PAYSYSTEM_CODE' => $this->service->getField('ID'),
		);

		$this->setExtraParams($params);

		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('BX_HANDLER' => 'YANDEX');
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	static protected function isMyResponseExtended(Request $request, $paySystemId)
	{
		$id = $request->get('BX_PAYSYSTEM_CODE');
		return (int)$id === (int)$paySystemId;
	}

	/**
	 * @param Payment $payment
	 * @param int $refundableSum
	 * @return PaySystem\ServiceResult
	 */
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();
		$error = '';
		$requestDT = date('c');
		$currency = $this->isTestMode($payment) ? 10643 : 643;
		$cause = Loc::getMessage('SALE_HPS_YANDEX_CUSTOMER_REJECTION');

		$shopId = $this->getBusinessValue($payment, 'YANDEX_SHOP_ID');
		$request = '
			<returnPaymentRequest
				clientOrderId=\''.$payment->getId().'\'
				requestDT=\''.$requestDT.'\'
				invoiceId=\''.$payment->getField('PS_INVOICE_ID').'\'
				shopId=\''.$shopId.'\'
				amount=\''.number_format($refundableSum, 2, '.', '').'\'
				currency=\''.$currency.'\'
				cause=\''.Encoding::convertEncoding($cause, LANG_CHARSET, 'UTF-8').'\'
	        />';

		$url = $this->getUrl($payment, 'return');

		$signResult = $this->signRequest($payment, $request);
		if ($signResult->isSuccess())
		{
			$data = $signResult->getData();
			$pkcs7 = $data['PKCS7'];

			$CertPem = PaySystem\YandexCert::getValue('CERT', $shopId);
			$PkeyPem = PaySystem\YandexCert::getValue('PKEY', $shopId);

			$cert = self::createTmpFile($CertPem);
			$pkey = self::createTmpFile($PkeyPem);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_USERAGENT, "1C-Bitrix");
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $pkcs7);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSLCERT, $cert);
			curl_setopt($ch, CURLOPT_SSLKEY, $pkey);
			$content = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlError = curl_error($ch);
			curl_close($ch);

			PaySystem\Logger::addDebugInfo('Yandex: returnPaymentResponse: '.$content);

			if ($content !== false)
			{
				$element = $this->parseXmlResponse('returnPaymentResponse', $content);
				$status = (int)$element->getAttribute('status');
				if ($status == 0)
				{
					$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
				}
				else
				{
					$error .= Loc::getMessage('SALE_HPS_YANDEX_REFUND_ERROR').' '.Loc::getMessage('SALE_HPS_YANDEX_REFUND_ERROR_INFO', array('#STATUS#' => $status, '#ERROR#' => $element->getAttribute('error')));
				}
			}
			else
			{
				$error .= Loc::getMessage('SALE_HPS_YANDEX_REFUND_CONNECTION_ERROR', array('#URL#' => $url, '#ERROR#' => $curlError, '#CODE#' => $httpCode));
			}
		}
		else
		{
			$error .= implode("\n", $signResult->getErrorMessages());
		}

		if ($error !== '')
		{
			$result->addError(new Error($error));

			$error = 'Yandex: returnPaymentRequest: '.join('\n', $result->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function isCorrectHash(Payment $payment, Request $request)
	{
		$hash = md5(
			implode(";", array(
					$request->get('action'),
					$request->get('orderSumAmount'),
					$request->get('orderSumCurrencyPaycash'),
					$request->get('orderSumBankPaycash'),
					$this->getBusinessValue($payment, 'YANDEX_SHOP_ID'),
					$request->get('invoiceId'),
					$this->getBusinessValue($payment, 'PAYMENT_BUYER_ID'),
					$this->getBusinessValue($payment, 'YANDEX_SHOP_KEY')
				)
			)
		);

		PaySystem\Logger::addDebugInfo(
			'Yandex: calculatedHash='.ToUpper($hash)."; yandexHash=".ToUpper($request->get('md5'))
		);

		return ToUpper($hash) === ToUpper($request->get('md5'));
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function isCorrectSum(Payment $payment, Request $request)
	{
		$sum = $request->get('orderSumAmount');
		$paymentSum = $this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY');

		PaySystem\Logger::addDebugInfo(
			'Yandex: yandexSum='.roundEx($sum, 2)."; paymentSum=".roundEx($paymentSum, 2)
		);

		return roundEx($paymentSum, 2) == roundEx($sum, 2);
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

		if (!$result->isResultApplied() && $data['CODE'] === 0)
		{
			$data['CODE'] = 200;
		}

		$dateISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
		header("Content-Type: text/xml");
		header("Pragma: no-cache");
		$text = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

		if (strlen($data['HEAD']) > 0)
		{
			$text .= "<".$data['HEAD']." performedDatetime=\"".$dateISO."\"";
			$text .= " code=\"".$data['CODE']."\" shopId=\"".$data['SHOP_ID']."\" invoiceId=\"".$data['INVOICE_ID']."\"";

			if (strlen($data['TECH_MESSAGE']) > 0)
				$text .= " techMessage=\"".$data['TECH_MESSAGE']."\"";

			$text .= "/>";
		}

		PaySystem\Logger::addDebugInfo('Yandex: response: '.$text);

		echo $text;
		die();
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('orderNumber');
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processCheckAction(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();
		$data = $this->extractDataFromRequest($request);

		if ($this->isCorrectSum($payment, $request))
		{
			$data['CODE'] = 0;
		}
		else
		{
			$data['CODE'] = 100;
			$errorMessage = 'Incorrect payment sum';
			$result->addError(new Error($errorMessage));

			PaySystem\Logger::addError('Yandex: checkOrderResponse: '.$errorMessage);
		}

		$result->setData($data);

		return $result;
	}

	/**
	 * @param Request $request
	 * @return array
	 */
	private function extractDataFromRequest(Request $request)
	{
		return array(
			'HEAD' => $request->get('action').'Response',
			'SHOP_ID' => $request->get('shopId'),
			'INVOICE_ID' =>  $request->get('invoiceId')
		);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processNoticeAction(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();
		$data = $this->extractDataFromRequest($request);
		$modeList = static::getHandlerModeList();
		$description = Loc::getMessage('SALE_HPS_YANDEX_TRANSACTION').": ".$request->get('invoiceId')."; ";
		if ($request->get('paymentDatetime'))
		{
			$description .= Loc::getMessage('SALE_HPS_YANDEX_DATE_PAYED').": ".$request->get('paymentDatetime');
		}

		$fields = array(
			"PS_STATUS_CODE" => substr($data['HEAD'], 0, 5),
			"PS_STATUS_DESCRIPTION" => $description,
			"PS_STATUS_MESSAGE" => $modeList[$request->get('paymentType')],
			"PS_SUM" => $request->get('orderSumAmount'),
			"PS_CURRENCY" => substr($request->get('orderSumCurrencyPaycash'), 0, 3),
			"PS_RESPONSE_DATE" => new DateTime(),
			"PS_INVOICE_ID" => $request->get('invoiceId')
		);

		if ($this->isCorrectSum($payment, $request))
		{
			$data['CODE'] = 0;
			$fields["PS_STATUS"] = "Y";

			PaySystem\Logger::addDebugInfo(
				'Yandex: PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
			);

			if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') == 'Y')
			{
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			}
		}
		else
		{
			$data['CODE'] = 200;
			$fields["PS_STATUS"] = "N";
			$errorMessage = 'Incorrect payment sum';
			$result->addError(new Error($errorMessage));

			PaySystem\Logger::addError('Yandex: paymentAvisoResponse: '.$errorMessage);
		}

		$result->setData($data);
		$result->setPsData($fields);

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processCancelAction(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();
		$data = $this->extractDataFromRequest($request);

		if ($this->isCorrectHash($payment, $request))
		{
			$data['CODE'] = 0;
			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}
		else
		{
			$data['CODE'] = 1;

			$errorMessage = 'Incorrect payment hash sum';
			$result->addError(new Error($errorMessage));

			PaySystem\Logger::addError('Yandex: cancelOrderResponse: '.$errorMessage);
		}

		$result->setData($data);

		return $result;
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => array(
				self::TEST_URL => 'https://demomoney.yandex.ru/eshop.xml',
				self::ACTIVE_URL => 'https://money.yandex.ru/eshop.xml'
			),
			'confirm' => array(
				self::ACTIVE_URL => 'https://penelope.yamoney.ru/webservice/mws/api/confirmPayment',
				self::TEST_URL => 'https://penelope-demo.yamoney.ru:8083/webservice/mws/api/confirmPayment'
			),
			'cancel' => array(
				self::ACTIVE_URL => 'https://penelope.yamoney.ru/webservice/mws/api/cancelPayment',
				self::TEST_URL => 'https://penelope-demo.yamoney.ru:8083/webservice/mws/api/cancelPayment'
			),
			'return' => array(
				self::ACTIVE_URL => 'https://penelope.yamoney.ru/webservice/mws/api/returnPayment',
				self::TEST_URL => 'https://penelope-demo.yamoney.ru:8083/webservice/mws/api/returnPayment',
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
		$result = new PaySystem\ServiceResult();
		$action = $request->get('action');

		if ($this->isCorrectHash($payment, $request))
		{
			if ($action == 'checkOrder')
			{
				return $this->processCheckAction($payment, $request);
			}
			else if ($action == 'cancelOrder')
			{
				return $this->processCancelAction($payment, $request);
			}
			else if ($action == 'paymentAviso')
			{
				return $this->processNoticeAction($payment, $request);
			}
			else
			{
				$data = $this->extractDataFromRequest($request);
				$data['TECH_MESSAGE'] = 'Unknown action: '.$action;
				$result->setData($data);
				$result->addError(new Error('Unknown action: '.$action.'. Request='.join(', ', $request->toArray())));
			}
		}
		else
		{
			$data = $this->extractDataFromRequest($request);
			$data['TECH_MESSAGE'] = 'Incorrect hash sum';
			$data['CODE'] = 1;

			$result->setData($data);
			$result->addError(new Error('Incorrect hash sum'));
		}

		if (!$result->isSuccess())
		{
			$error = 'Yandex: processRequest: '.$action.': '.join('\n', $result->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return ($this->getBusinessValue($payment, 'PS_IS_TEST') == 'Y');
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function confirm(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();
		$httpClient = new HttpClient();

		$url = $this->getUrl($payment, 'confirm');
		$requestDT = date('c');

		$request = array(
			'orderId' => $this->getBusinessValue($payment, 'PAYMENT_ID'),
			'amount' => $this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'),
			'currency' => $this->getBusinessValue($payment, 'PAYMENT_CURRENCY'),
			'requestDT' => $requestDT
		);
		$responseString = $httpClient->post($url, $request);

		if ($responseString !== false)
		{
			$element = $this->parseXmlResponse('confirmPaymentResponse', $responseString);
			$status = (int)$element->getAttribute('status');
			if ($status == 0)
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			else
				$result->addError(new Error('Error on try to confirm payment. Status: '.$status));
		}
		else
		{
			$result->addError(new Error("Error sending request. URL=".$url." PARAMS=".join(' ', $request)));
		}

		if (!$result->isSuccess())
		{
			$error = 'Yandex: confirmPayment: '.join('\n', $result->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function cancel(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();
		$httpClient = new HttpClient();

		$url = $this->getUrl($payment, 'cancel');
		$requestDT = date('c');
		$request = array(
			'orderId' => $this->getBusinessValue($payment, 'PAYMENT_ID'),
			'requestDT' => $requestDT
		);
		$responseString = $httpClient->post($url, $request);

		if($responseString !== false)
		{
			$element = $this->parseXmlResponse('cancelPaymentResponse', $responseString);
			$status = (int)$element->getAttribute('status');
			if ($status == 0)
				$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
			else
				$result->addError(new Error('Error on try to cancel payment. Status: '.$status));
		}
		else
		{
			$result->addError(new Error('Error sending request. URL='.$url.' PARAMS='.join(' ', $request)));
		}

		if (!$result->isSuccess())
		{
			$error = 'Yandex: cancelPayment: '.join('\n', $result->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param $operation
	 * @param $requestString
	 * @return \CDataXMLNode
	 */
	private function parseXmlResponse($operation, $requestString)
	{
		$xmlParser = new \CDataXML();

		$xmlParser->LoadString($requestString);
		$tree = $xmlParser->GetTree();
		$elements = $tree->elementsByName($operation);

		return $elements[0];
	}

	/**
	 * @param Payment $payment
	 * @param $xml
	 * @return PaySystem\ServiceResult
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private function signRequest(Payment $payment, $xml)
	{
		$result = new PaySystem\ServiceResult();

		$dataFile = self::createTmpFile($xml);
		$signedFile = self::createTmpFile();

		$shopId = $this->getBusinessValue($payment, 'YANDEX_SHOP_ID');
		$CertPem = PaySystem\YandexCert::getValue('CERT', $shopId);
		$PkeyPem = PaySystem\YandexCert::getValue('PKEY', $shopId);

		if ($PkeyPem && $CertPem)
		{
			if (openssl_pkcs7_sign($dataFile, $signedFile, $CertPem, $PkeyPem, array(), PKCS7_NOCHAIN + PKCS7_NOCERTS))
			{
				$signedData = explode("\n\n", file_get_contents($signedFile));
				$pkcs7 = "-----BEGIN PKCS7-----\n".$signedData[1]."\n-----END PKCS7-----";
				$result->setData(array('PKCS7' => $pkcs7));
			}
			else
			{
				$result->addError(new Error(Loc::getMessage('SALE_HPS_YANDEX_REFUND_ERROR')));
			}
		}
		else
		{
			$result->addError(new Error(Loc::getMessage('SALE_HPS_YANDEX_REFUND_ERROR')));
		}

		return $result;
	}

	/**
	 * @param null $data
	 * @return mixed
	 */
	private static function createTmpFile($data = null)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'YaMWS');
		if ($data !== null)
			file_put_contents($filePath, $data);

		return $filePath;
	}


	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList()
	{
		return array(
			"PC" => Loc::getMessage("SALE_HPS_YANDEX_YMoney"),
			"AC" => Loc::getMessage("SALE_HPS_YANDEX_Cards"),
			"GP" => Loc::getMessage("SALE_HPS_YANDEX_Terminals"),
			"MC" => Loc::getMessage("SALE_HPS_YANDEX_Mobile"),
			"WM" => "WebMoney",
			"SB" => Loc::getMessage("SALE_HPS_YANDEX_Sberbank"),
			"MP" => Loc::getMessage("SALE_HPS_YANDEX_mPOS"),
			"AB" => Loc::getMessage("SALE_HPS_YANDEX_AlphaClick"),
			"MA" => Loc::getMessage("SALE_HPS_YANDEX_MasterPass"),
			"PB" => Loc::getMessage("SALE_HPS_YANDEX_Promsvyazbank"),
			"QW" => Loc::getMessage("SALE_HPS_YANDEX_Qiwi"),
//			"KV" => Loc::getMessage("SALE_HPS_YANDEX_TinkoffBank"),
			"QP" => Loc::getMessage("SALE_HPS_YANDEX_YKuppiRu"),
			"" => Loc::getMessage("SALE_HPS_YANDEX_Smart")
		);
	}

	/**
	 * @return bool
	 */
	public function isRefundableExtended()
	{
		$whiteList = array('PC', 'AC', 'MC', 'WM', 'MP', 'AB', 'MA', 'QW', 'KV', 'QP');
		return in_array($this->service->getField('PS_MODE'), $whiteList);
	}

	/**
	 * @return bool
	 */
	public function isTuned()
	{
		$personTypeList = PaySystem\Manager::getPersonTypeIdList($this->service->getField('ID'));
		$personTypeId = array_shift($personTypeList);
		$shopId = BusinessValue::get('YANDEX_SHOP_ID', $this->service->getConsumerName(), $personTypeId);

		return !empty($shopId);
	}

	/**
	 * @param array $paySystemList
	 * @return array
	 */
	public static function findMyDataRefundablePage(array $paySystemList)
	{
		$result = array();
		$personTypeList = BusinessValue::getPersonTypes();
		$handler = PaySystem\Manager::getFolderFromClassName(get_called_class());
		$description = PaySystem\Manager::getHandlerDescription($handler);

		foreach ($paySystemList as $data)
		{
			foreach ($personTypeList as $personType)
			{
				$shopId = BusinessValue::get('YANDEX_SHOP_ID', PaySystem\Service::PAY_SYSTEM_PREFIX.$data['ID'], $personType['ID']);
				if ($shopId && !isset($result[$shopId]))
				{
					$cert = PaySystem\YandexCert::getCert($shopId);
					$result[$shopId] = array(
						'EXTERNAL_ID' => $shopId,
						'NAME' => $description['NAME'],
						'HANDLER' => 'yandex',
						'LINK_PARAMS' => 'shop_id='.$shopId,
						'CONFIGURED' => ($cert) ? 'Y' : 'N'
					);
				}
			}
		}

		return $result;
	}
}