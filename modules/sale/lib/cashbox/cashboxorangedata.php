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
class CashboxOrangeData
	extends Cashbox
	implements IPrintImmediately, ICheckable, ITestConnection
{
	private const PARTNER_CODE_BITRIX = '3010144';

	const RESPONSE_HTTP_CODE_200 = 200;
	const RESPONSE_HTTP_CODE_201 = 201;

	const HANDLER_MODE_TEST = 'TEST';
	const HANDLER_MODE_ACTIVE = 'ACTIVE';

	const HANDLER_TEST_URL = 'ssl://apip.orangedata.ru:2443/api/v2';
	const HANDLER_ACTIVE_URL = 'ssl://api.orangedata.ru:12003/api/v2';

	private $pathToSslCertificate = '';
	private $pathToSslCertificateKey = '';

	const CODE_VAT_0 = 5;
	const CODE_VAT_10 = 2;
	const CODE_VAT_20 = 1;
	const CODE_CALC_VAT_10 = 4;
	const CODE_CALC_VAT_20 = 3;

	private const MAX_TEXT_LENGTH = 128;
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
		return [
			SellCheck::getType() => 4,
			SellReturnCashCheck::getType() => 4,
			SellReturnCheck::getType() => 4,
			AdvancePaymentCheck::getType() => 3,
			AdvanceReturnCashCheck::getType() => 3,
			AdvanceReturnCheck::getType() => 3,
			PrepaymentCheck::getType() => 2,
			PrepaymentReturnCheck::getType() => 2,
			PrepaymentReturnCashCheck::getType() => 2,
			FullPrepaymentCheck::getType() => 1,
			FullPrepaymentReturnCheck::getType() => 1,
			FullPrepaymentReturnCashCheck::getType() => 1,
			CreditCheck::getType() => 6,
			CreditReturnCheck::getType() => 6,
			CreditPaymentCheck::getType() => 7,
		];
	}

	/**
	 * @return array
	 */
	private function getCalculatedSignMap()
	{
		return [
			Check::CALCULATED_SIGN_INCOME => 1,
			Check::CALCULATED_SIGN_CONSUMPTION => 2
		];
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

		$result = [
			'id' => static::buildUuid(static::UUID_TYPE_CHECK, $checkInfo['unique_id']),
			'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
			'group' => $this->getField('NUMBER_KKM') ?: null,
			'key' => $this->getValueFromSettings('SECURITY', 'KEY_SIGN') ?: null,
			'content' => [
				'type' => $calculatedSignMap[$check::getCalculatedSign()],
				'positions' => [],
				'checkClose' => [
					'payments' => [],
					'taxationSystem' => $this->getValueFromSettings('TAX', 'SNO'),
				],
				'customerContact' => $this->getCustomerContact($checkInfo),
			],
			'meta' => self::PARTNER_CODE_BITRIX
		];

		$checkType = $this->getCheckTypeMap();
		$paymentObjectMap = $this->getPaymentObjectMap();
		foreach ($checkInfo['items'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			if ($vat === null)
			{
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
			}

			$position = [
				'text' => mb_substr($item['name'], 0, self::MAX_TEXT_LENGTH),
				'quantity' => $item['quantity'],
				'price' => $item['price'],
				'tax' => $this->mapVatValue($check::getType(), $vat),
				'paymentMethodType' => $checkType[$check::getType()],
				'paymentSubjectType' => $paymentObjectMap[$item['payment_object']]
			];

			if (isset($item['nomenclature_code']))
			{
				$position['nomenclatureCode'] = base64_encode($item['nomenclature_code']);
			}

			$result['content']['positions'][] = $position;
		}

		$paymentTypeMap = $this->getPaymentTypeMap();
		foreach ($checkInfo['payments'] as $payment)
		{
			$result['content']['checkClose']['payments'][] = [
				'type' => $paymentTypeMap[$payment['type']],
				'amount' => $payment['sum'],
			];
		}

		return $result;
	}

	/**
	 * @param $checkType
	 * @param $vat
	 * @return mixed
	 */
	private function mapVatValue($checkType, $vat)
	{
		$map = [
			self::CODE_VAT_10 => [
				PrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10,
			],
			self::CODE_VAT_20 => [
				PrepaymentCheck::getType() => self::CODE_CALC_VAT_20,
				PrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_20,
				PrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_20,
			],
		];

		return $map[$vat][$checkType] ?? $vat;
	}

	/**
	 * @param array $data
	 * @return mixed|string
	 */
	private function getCustomerContact(array $data)
	{
		$customerContact = $this->getValueFromSettings('CLIENT', 'INFO');

		if ($customerContact === 'EMAIL')
		{
			return $data['client_email'];
		}
		elseif ($customerContact === 'PHONE')
		{
			$phone = \NormalizePhone($data['client_phone']);
			if ($phone[0] !== '7')
			{
				$phone = '7'.$phone;
			}

			return '+'.$phone;
		}

		if ($data['client_phone'])
		{
			$phone = \NormalizePhone($data['client_phone']);
			if ($phone[0] !== '7')
			{
				$phone = '7'.$phone;
			}

			return '+'.$phone;
		}

		return $data['client_email'];
	}

	/**
	 * @return array
	 */
	private function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 1,
			Check::PAYMENT_OBJECT_EXCISE => 2,
			Check::PAYMENT_OBJECT_JOB => 3,
			Check::PAYMENT_OBJECT_SERVICE => 4,
			Check::PAYMENT_OBJECT_PAYMENT => 10,
		];
	}

	/**
	 * @return array
	 */
	private function getPaymentTypeMap()
	{
		return [
			Check::PAYMENT_TYPE_CASH => 1,
			Check::PAYMENT_TYPE_CASHLESS => 2,
			Check::PAYMENT_TYPE_ADVANCE => 14,
			Check::PAYMENT_TYPE_CREDIT => 15,
		];
	}

	/**
	 * @param $url
	 * @param $data
	 * @return string
	 */
	private function getPostQueryHeaders($url, $data)
	{
		$sign = $this->sign($data);
		if ($sign === false)
		{
			return false;
		}

		$urlObj = new Main\Web\Uri($url);

		$header = "POST ".$urlObj->getPath()." HTTP/1.0\r\n";
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
		return [];
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

		$headers = $this->getPostQueryHeaders($url, $encodedData);
		if ($headers === false)
		{
			return $result->addError(
				new Errors\Error(
					Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_SIGN')
				)
			);
		}

		$queryResult = $this->send($url, $headers, $encodedData);
		if (!$queryResult->isSuccess())
		{
			return $result->addErrors($queryResult->getErrors());
		}

		$result->setData(['UUID' => $data['id']]);

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
	 * @throws Main\ObjectException
	 */
	private function send($url, $headers, $data = '')
	{
		$context = $this->createStreamContext();

		$errNumber = '';
		$errString = '';
		$client = stream_socket_client($url, $errNumber, $errString, 5, STREAM_CLIENT_CONNECT, $context);

		$result = new Result();
		if ($client !== false)
		{
			Logger::addDebugInfo($headers.$data);

			fputs($client, $headers.$data);
			$response = stream_get_contents($client);
			fclose($client);

			Logger::addDebugInfo($response);

			[$responseHeaders, $content] = explode("\r\n\r\n", $response);
			$httpCode = $this->extractResponseStatus($responseHeaders);

			$result->addData(['http_code' => $httpCode, 'content' => $content]);

			if (
				$httpCode !== static::RESPONSE_HTTP_CODE_201
				&&
				$httpCode !== static::RESPONSE_HTTP_CODE_200
			)
			{
				$content = $this->decode($content);
				if (isset($content['errors']))
				{
					$error = implode("\n", $content['errors']);
				}
				else
				{
					$error = Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_RESPONSE_'.$httpCode);
					if (!$error)
					{
						$error = Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_CHECK_PRINT');
					}
				}

				return $result->addError(new Errors\Error($error));
			}
		}
		else
		{
			$result->addError(
				new Errors\Error(
					Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_SEND_QUERY')
				)
			);

			$error = new Errors\Error($errNumber.': '.$errString);
			Logger::addError($error->getMessage(), $this->getField('ID'));
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

		return stream_context_create([
			'ssl' => [
				'local_cert' => $this->pathToSslCertificate,
				'local_pk' => $this->pathToSslCertificateKey,
				'passphrase' => $this->getValueFromSettings('SECURITY', 'SSL_KEY_PASS'),
			]
		]);
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
		$queryResult = $this->send($url, $header);

		if (!$queryResult->isSuccess())
		{
			return $result->addErrors($queryResult->getErrors());
		}

		$data = $queryResult->getData();

		$response = $this->decode($data['content']);
		if ($response === false)
		{
			return $result->addError(new Errors\Error(Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_ERROR_CHECK_CHECK')));
		}

		return static::applyCheckResult($response);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectException
	 */
	public function validate() : Result
	{
		$result = parent::validate();
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->testConnection();
	}

	protected function buildValidateQuery()
	{
		return [
			'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
			'group' => $this->getField('NUMBER_KKM') ?: null,
			'key' => $this->getValueFromSettings('SECURITY', 'KEY_SIGN') ?: null
		];
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectException
	 */
	public function testConnection()
	{
		$url = $this->getUrl().'/check/';
		$data = $this->buildValidateQuery();
		$encodedData = $this->encode($data);

		$headers = $this->getPostQueryHeaders($url,	$encodedData);

		return $this->send($url, $headers, $encodedData);
	}

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
		$result = [];

		if (!$data['id'])
		{
			return $result;
		}

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($data['id']);

		$result['ID'] = $checkInfo['ID'];
		$result['CHECK_TYPE'] = $checkInfo['TYPE'];

		$check = CheckManager::getObjectById($checkInfo['ID']);
		$dateTime = new Main\Type\DateTime($data['processedAt'], 'Y-m-d\TH:i:s.u');
		$result['LINK_PARAMS'] = [
			Check::PARAM_REG_NUMBER_KKT => $data['deviceRN'],
			Check::PARAM_FISCAL_DOC_ATTR => $data['fp'],
			Check::PARAM_FISCAL_DOC_NUMBER => $data['documentNumber'],
			Check::PARAM_FISCAL_RECEIPT_NUMBER => $data['documentIndex'],
			Check::PARAM_FN_NUMBER => $data['fsNumber'],
			Check::PARAM_SHIFT_NUMBER => $data['shiftNumber'],
			Check::PARAM_DOC_SUM => (float)$checkInfo['SUM'],
			Check::PARAM_DOC_TIME => $dateTime->getTimestamp(),
			Check::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
		];

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
		if ($filePath === false)
		{
			return '';
		}

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
		$settings = [
			'SECURITY' => [
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY'),
				'ITEMS' => [
					'PKEY' => [
						'TYPE' => 'DATABASE_FILE',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'NO_DELETE' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_PKEY'),
					],
					'SSL_CERT' => [
						'TYPE' => 'DATABASE_FILE',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'NO_DELETE' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_CERT'),
					],
					'SSL_KEY' => [
						'TYPE' => 'DATABASE_FILE',
						'CLASS' => 'adm-designed-file',
						'REQUIRED' => 'Y',
						'NO_DELETE' => 'Y',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_KEY'),
					],
					'SSL_KEY_PASS' => [
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_SSL_KEY_PASS'),
					],
					'KEY_SIGN' => [
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SECURITY_KEY_SIGN'),
					],
				]
			]
		];

		$settings['SERVICE'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SERVICE'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'INN' => [
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SERVICE_INN_LABEL')
				]
			]
		];

		$settings['CLIENT'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_CLIENT'),
			'ITEMS' => [
				'INFO' => [
					'TYPE' => 'ENUM',
					'VALUE' => 'NONE',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_CLIENT_INFO'),
					'OPTIONS' => [
						'DEFAULT' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_CLIENT_DEFAULT'),
						'PHONE' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_CLIENT_PHONE'),
						'EMAIL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_CLIENT_EMAIL'),
					]
				],
			]
		];

		$settings['VAT'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_VAT'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'NOT_VAT' => [
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => 6
				]
			]
		];

		if (Main\Loader::includeModule('catalog'))
		{
			$dbRes = Catalog\VatTable::getList(['filter' => ['ACTIVE' => 'Y']]);
			$vatList = $dbRes->fetchAll();
			if ($vatList)
			{
				$defaultVatList = [
					0 => self::CODE_VAT_0,
					10 => self::CODE_VAT_10,
					20 => self::CODE_VAT_20
				];
				foreach ($vatList as $vat)
				{
					$value = '';
					if (isset($defaultVatList[(int)$vat['RATE']]))
						$value = $defaultVatList[(int)$vat['RATE']];

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = [
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
						'VALUE' => $value
					];
				}
			}
		}

		$settings['TAX'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SNO'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'SNO' => [
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_SNO_LABEL'),
					'VALUE' => 0,
					'OPTIONS' => [
						0 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_OSN'),
						1 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_UI'),
						2 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_UIO'),
						3 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_ENVD'),
						4 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_ESN'),
						5 => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SNO_PATENT')
					]
				]
			]
		];

		$settings['INTERACTION'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_INTERACTION'),
			'ITEMS' => [
				'MODE_HANDLER' => [
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_SETTINGS_MODE_HANDLER_LABEL'),
					'OPTIONS' => [
						static::HANDLER_MODE_ACTIVE => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_MODE_ACTIVE'),
						static::HANDLER_MODE_TEST => Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_MODE_TEST'),
					]
				]
			]
		];

		return $settings;
	}

	/**
	 * @param Main\HttpRequest $request
	 * @return array
	 */
	public static function extractSettingsFromRequest(Main\HttpRequest $request)
	{
		global $APPLICATION;

		$settings = parent::extractSettingsFromRequest($request);
		$files = $request->getFile('SETTINGS');

		foreach (static::getSettings()['SECURITY']['ITEMS'] as $fieldId => $field)
		{
			if ($field['TYPE'] === 'DATABASE_FILE'
				&& $files['error']['SECURITY'][$fieldId] === 0
				&& $files['tmp_name']['SECURITY'][$fieldId]
			)
			{
				$content = $APPLICATION->GetFileContent($files['tmp_name']['SECURITY'][$fieldId]);
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
