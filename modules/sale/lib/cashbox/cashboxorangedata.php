<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Text;
use Bitrix\Main\Localization;
use Bitrix\Sale\Cashbox\Errors;
use Bitrix\Sale\Result;
use Bitrix\Catalog;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxOrangeData
 * @package Bitrix\Sale\Cashbox
 */
class CashboxOrangeData extends Cashbox implements IPrintImmediately, ICheckable
{
	const RESPONSE_HTTP_CODE_200 = 200;
	const RESPONSE_HTTP_CODE_201 = 201;

	const HANDLER_MODE_TEST = 'TEST';
	const HANDLER_MODE_ACTIVE = 'ACTIVE';

	const HANDLER_TEST_URL = 'ssl://apip.orangedata.ru:2443/api/v2';
	const HANDLER_ACTIVE_URL = 'ssl://api.orangedata.ru:12003/api/v2';

	private $pathToSslCertificate = '';
	private $pathToSslCertificateKey = '';

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_TITLE');
	}

	/**
	 * @return array
	 */
	private function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 4,
			SellReturnCashCheck::getType() => 4,
			SellReturnCheck::getType() => 4,
			AdvancePaymentCheck::getType() => 3,
			AdvanceReturnCashCheck::getType() => 3,
			AdvanceReturnCheck::getType() => 3,
			CreditCheck::getType() => 6,
			CreditReturnCheck::getType() => 6,
			CreditPaymentCheck::getType() => 7,
		);
	}

	/**
	 * @return array
	 */
	private function getCalculatedSignMap()
	{
		return array(
			Check::CALCULATED_SIGN_INCOME => 1,
			Check::CALCULATED_SIGN_CONSUMPTION => 2
		);
	}

	/**
	 * @param Check $check
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	public function buildCheckQuery(Check $check)
	{
		$checkInfo = $check->getDataForCheck();

		$calculatedSignMap = $this->getCalculatedSignMap();

		if ($checkInfo['client_phone'])
		{
			$phone = \NormalizePhone($checkInfo['client_phone']);
			if ($phone[0] !== '7')
			{
				$phone = '7'.$phone;
			}

			$customerContact = '+'.$phone;
		}
		else
		{
			$customerContact = $checkInfo['client_email'];
		}

		$result = array(
			'id' => $checkInfo['unique_id'],
			'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
			'group' => $this->getField('NUMBER_KKM') ?: null,
			'key' => $this->getValueFromSettings('SECURITY', 'KEY_SIGN') ?: null,
			'content' => array(
				'type' => $calculatedSignMap[$check::getCalculatedSign()],
				'positions' => array(),
				'checkClose' => array(
					'payments' => array(),
					'taxationSystem' => $this->getValueFromSettings('TAX', 'SNO'),
				),
				'customerContact' => $customerContact,
			)
		);

		$checkType = $this->getCheckTypeMap();
		foreach ($checkInfo['items'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			if ($vat === null)
			{
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
			}

			$result['content']['positions'][] = array(
				'text' => $item['name'],
				'quantity' => $item['quantity'],
				'price' => $item['price'],
				'tax' => $vat,
				'paymentMethodType' => $checkType[$check::getType()],
				'paymentSubjectType' => null
			);
		}

		$paymentTypeMap = $this->getPaymentTypeMap();
		foreach ($checkInfo['payments'] as $payment)
		{
			$result['content']['checkClose']['payments'][] = array(
				'type' => $paymentTypeMap[$payment['type']],
				'amount' => $payment['sum'],
			);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getPaymentTypeMap()
	{
		return array(
			Check::PAYMENT_TYPE_CASH => 1,
			Check::PAYMENT_TYPE_CASHLESS => 2,
			Check::PAYMENT_TYPE_ADVANCE => 14,
			Check::PAYMENT_TYPE_CREDIT => 15,
		);
	}

	/**
	 * @param $url
	 * @param $data
	 * @return string
	 */
	private function getPrintQueryHeaders($url, $data)
	{
		$sign = $this->sign($data);
		if ($sign === false)
		{
			return false;
		}

		$urlObj = new Main\Web\Uri($url);

		$header = "POST /api/v2/documents/ HTTP/1.0\r\n";
		$header .= "Host: ".$urlObj->getHost()."\r\n";
		$header .= "Accept: application/json\r\n";
		$header .= "Content-Type: application/json\r\n";
		$header .= "X-Signature: ".$sign."\r\n";
		$header .= sprintf("Content-length: %s\r\n", Text\BinaryString::getLength($data));
		$header .= "\r\n";

		return $header;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function buildZReportQuery($id)
	{
		return array();
	}

	/**
	 * @param Check $check
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function printImmediately(Check $check)
	{
		$result = new Result();

		$url = $this->getUrl();
		$url .= '/documents/';

		$data = $this->buildCheckQuery($check);
		$encodedData = $this->encode($data);

		$headers = $this->getPrintQueryHeaders($url, $encodedData);
		if ($headers === false)
		{
			$result->addError(new Errors\Error(Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_SIGN')));
			return $result;
		}

		$queryResult = $this->sendQuery($url, $headers, $encodedData);

		if (!$queryResult->isSuccess())
		{
			$result->addErrors($queryResult->getErrors());
			return $result;
		}

		$response = $queryResult->getData();
		$httpCode = $response['http_code'];
		if ($httpCode === static::RESPONSE_HTTP_CODE_201)
		{
			$result->setData(array('UUID' => $data['id']));
		}
		else
		{
			$error = '';

			if (isset($response['content']))
			{
				$content = $this->decode($response['content']);
				if (isset($content['errors']))
				{
					$error = implode("\n", $content['errors']);
				}
				else
				{
					$error = Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_RESPONSE_'.$httpCode);
				}
			}

			if (!$error)
			{
				$error = Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_CHECK_PRINT');
			}

			$result->addError(new Errors\Error($error));
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function getUrl()
	{
		if ($this->getValueFromSettings('INTERACTION', 'MODE_HANDLER') === static::HANDLER_MODE_ACTIVE)
		{
			return static::HANDLER_ACTIVE_URL;
		}

		return static::HANDLER_TEST_URL;
	}

	/**
	 * @param $url
	 * @param $headers
	 * @param string $data
	 * @return Result
	 */
	private function sendQuery($url, $headers, $data = '')
	{
		$context = $this->createStreamContext();

		$errNumber = '';
		$errString = '';
		$client = stream_socket_client($url, $errNumber, $errString, 5, STREAM_CLIENT_CONNECT, $context);

		$result = new Result();
		if ($client !== false)
		{
			fputs($client, $headers.$data);
			$response = stream_get_contents($client);
			fclose($client);

			list($responseHeaders, $content) = explode("\r\n\r\n", $response);
			$httpCode = $this->extractResponseStatus($responseHeaders);
			$result->addData(array('http_code' => $httpCode, 'content' => $content));
		}
		else
		{
			$result->addError(
				new Errors\Error(
					Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_SEND_QUERY')
				)
			);

			$error = new Errors\Error($errNumber.': '.$errString);
			Manager::writeToLog($this->getField('ID'), $error);
		}

		return $result;
	}

	/**
	 * @param $headers
	 * @return int
	 */
	private function extractResponseStatus($headers)
	{
		$headers = explode("\n", $headers);
		preg_match('#HTTP\S+ (\d+)#', $headers[0], $find);

		return (int)$find[1];
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		if ($this->pathToSslCertificate !== ''
			&& Main\IO\File::isFileExists($this->pathToSslCertificate)
		)
		{
			unlink($this->pathToSslCertificate);
		}

		if ($this->pathToSslCertificateKey !== ''
			&& Main\IO\File::isFileExists($this->pathToSslCertificateKey)
		)
		{
			unlink($this->pathToSslCertificateKey);
		}
	}

	/**
	 * @return resource
	 */
	private function createStreamContext()
	{
		$sslCert = $this->getValueFromSettings('SECURITY', 'SSL_CERT');
		$this->pathToSslCertificate = $this->createTmpFile($sslCert);

		$sslKey = $this->getValueFromSettings('SECURITY', 'SSL_KEY');
		$this->pathToSslCertificateKey = $this->createTmpFile($sslKey);

		return stream_context_create(array(
			'ssl' => array(
				'local_cert' => $this->pathToSslCertificate,
				'local_pk' => $this->pathToSslCertificateKey,
				'passphrase' => $this->getValueFromSettings('SECURITY', 'SSL_KEY_PASS'),
			)
		));
	}

	/**
	 * @param Check $check
	 * @return Result
	 */
	public function check(Check $check)
	{
		$result = new Result();

		$url = $this->getUrl();
		$url .= '/documents/'.$this->getValueFromSettings('SERVICE', 'INN').'/status/'.$check->getField('EXTERNAL_UUID');

		$header = $this->getCheckQueryHeaders($url);
		$queryResult = $this->sendQuery($url, $header);

		$data = $queryResult->getData();
		if ($data['http_code'] !== static::RESPONSE_HTTP_CODE_200)
		{
			$error = Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_RESPONSE_'.$data['http_code']);
			if (!$error)
			{
				$error = implode("\n", $queryResult->getErrorMessages());
			}

			$result->addError(new Errors\Error($error));

			return $result;
		}

		$response = $this->decode($data['content']);
		if ($response === false)
		{
			$result->addError(new Errors\Error(Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_CHECK_CHECK')));
			return $result;
		}

		return static::applyCheckResult($response);}

	/**
	 * @param $url
	 * @return string
	 */
	private function getCheckQueryHeaders($url)
	{
		$urlObj = new Main\Web\Uri($url);

		$header = "GET ".$urlObj->getPath()." HTTP/1.0\r\n";
		$header .= "Host: ".$urlObj->getHost()."\r\n";
		$header .= "Accept: application/json\r\n";
		$header .= "Content-Type: application/json\r\n";
		$header .= "\r\n";

		return $header;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	protected static function extractCheckData(array $data)
	{
		$result = array();

		if (!$data['id'])
		{
			return $result;
		}

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($data['id']);

		$result['ID'] = $checkInfo['ID'];
		$result['CHECK_TYPE'] = $checkInfo['TYPE'];

		$check = CheckManager::getObjectById($checkInfo['ID']);
		$dateTime = new Main\Type\DateTime($data['processedAt'], 'Y-m-d\TH:i:s.u');
		$result['LINK_PARAMS'] = array(
			Check::PARAM_REG_NUMBER_KKT => $data['deviceRN'],
			Check::PARAM_FISCAL_DOC_ATTR => $data['fp'],
			Check::PARAM_FISCAL_DOC_NUMBER => $data['documentNumber'],
			Check::PARAM_FISCAL_RECEIPT_NUMBER => $data['documentIndex'],
			Check::PARAM_FN_NUMBER => $data['fsNumber'],
			Check::PARAM_SHIFT_NUMBER => $data['shiftNumber'],
			Check::PARAM_DOC_SUM => $data['total'],
			Check::PARAM_DOC_TIME => $dateTime->getTimestamp(),
			Check::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
		);

		return $result;
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function sign($data)
	{
		if (!function_exists('openssl_get_privatekey') || !function_exists('openssl_private_encrypt'))
		{
			return false;
		}

		$data = pack('H*', '3031300d060960864801650304020105000420') . hash('sha256', $data, true);
		$pk = openssl_get_privatekey($this->getValueFromSettings('SECURITY', 'PKEY'));

		openssl_private_encrypt($data, $res, $pk);
		return base64_encode($res);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private function encode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	private function decode($data)
	{
		try
		{
			return Main\Web\Json::decode($data);
		}
		catch (Main\ArgumentException $exception)
		{
			return false;
		}
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	private function createTmpFile($data)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'orange_data');
		if ($data !== null)
		{
			file_put_contents($filePath, $data);
		}

		return $filePath;
	}

	/**
	 * @param int $modelId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = array(
			'SECURITY' => array(
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY'),
				'ITEMS' => array(
					'PKEY' => array(
						'TYPE' => 'SECURITY_FILE_CONTROL',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_PKEY'),
					),
					'SSL_CERT' => array(
						'TYPE' => 'SECURITY_FILE_CONTROL',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_CERT'),
					),
					'SSL_KEY' => array(
						'TYPE' => 'SECURITY_FILE_CONTROL',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_KEY'),
					),
					'SSL_KEY_PASS' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_KEY_PASS'),
					),
					'KEY_SIGN' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_KEY_SIGN'),
					),
				)
			)
		);

		$settings['SERVICE'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SERVICE'),
			'REQUIRED' => 'Y',
			'ITEMS' => array(
				'INN' => array(
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SERVICE_INN_LABEL')
				)
			)
		);

		$settings['VAT'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_VAT'),
			'REQUIRED' => 'Y',
			'ITEMS' => array(
				'NOT_VAT' => array(
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => 6
				)
			)
		);

		if (Main\Loader::includeModule('catalog'))
		{
			$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
			$vatList = $dbRes->fetchAll();
			if ($vatList)
			{
				$defaultVat = array(0 => 5, 10 => 2, 18 => 1);
				foreach ($vatList as $vat)
				{
					$value = '';
					if (isset($defaultVat[(int)$vat['RATE']]))
						$value = $defaultVat[(int)$vat['RATE']];

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = array(
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
						'VALUE' => $value
					);
				}
			}
		}

		$settings['TAX'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SNO'),
			'REQUIRED' => 'Y',
			'ITEMS' => array(
				'SNO' => array(
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SNO_LABEL'),
					'VALUE' => 0,
					'OPTIONS' => array(
						0 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_OSN'),
						1 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_UI'),
						2 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_UIO'),
						3 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_ENVD'),
						4 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_ESN'),
						5 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_PATENT')
					)
				)
			)
		);

		$settings['INTERACTION'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_INTERACTION'),
			'ITEMS' => array(
				'MODE_HANDLER' => array(
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_MODE_HANDLER_LABEL'),
					'OPTIONS' => array(
						static::HANDLER_MODE_ACTIVE => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_MODE_ACTIVE'),
						static::HANDLER_MODE_TEST => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_MODE_TEST'),
					)
				)
			)
		);

		return $settings;
	}

	/**
	 * @param Main\HttpRequest $request
	 * @return array
	 */
	public static function extractSettingsFromRequest(Main\HttpRequest $request)
	{
		$settings = parent::extractSettingsFromRequest($request);
		$files = $request->getFile('SETTINGS');

		foreach ($settings['SECURITY'] as $fieldId => $field)
		{
			if ($files['error']['SECURITY'][$fieldId] === 0
				&& $files['tmp_name']['SECURITY'][$fieldId]
			)
			{
				$content = file_get_contents($files['tmp_name']['SECURITY'][$fieldId]);
				$settings['SECURITY'][$fieldId] = $content ?: '';
			}
		}

		return $settings;
	}

	/**
	 * @return bool
	 */
	public static function isSupportedFFD105()
	{
		return true;
	}

}
