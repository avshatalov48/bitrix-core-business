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
	implements IPrintImmediately, ICheckable, ITestConnection, ICorrection
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
			CreditPaymentReturnCheck::getType() => 7,
			CreditPaymentReturnCashCheck::getType() => 7,
		];
	}

	/**
	 * @return array
	 */
	private function getCalculatedSignMap()
	{
		return [
			Check::CALCULATED_SIGN_INCOME => 1,
			Check::CALCULATED_SIGN_CONSUMPTION => 3
		];
	}

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		return $this->buildCheckQueryByCheckData(
			$this->getCheckData($check),
			($check->getType() === 'sellreturn')
		);
	}

	/**
	 * @param AbstractCheck $check
	 * @return array
	 */
	protected function getCheckData(AbstractCheck $check): array
	{
		return $check->getDataForCheck();
	}

	/**
	 * @param array $checkData
	 * @param bool $isSellReturn
	 * @return array
	 */
	protected function buildCheckQueryByCheckData(array $checkData, bool $isSellReturn): array
	{
		$calculatedSignMap = $this->getCalculatedSignMap();

		$result = [
			'id' => static::buildUuid(static::UUID_TYPE_CHECK, $checkData['unique_id']),
			'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
			'group' => $this->getField('NUMBER_KKM') ?: null,
			'key' => $this->getValueFromSettings('SECURITY', 'KEY_SIGN') ?: null,
			'content' => [
				'type' => $calculatedSignMap[$checkData['calculated_sign']],
				'positions' => [],
				'checkClose' => [
					'payments' => [],
					'taxationSystem' => $this->getValueFromSettings('TAX', 'SNO'),
				],
				'customerContact' => $this->getCustomerContact($checkData),
			],
			'meta' => self::PARTNER_CODE_BITRIX
		];

		foreach ($checkData['items'] as $item)
		{
			$result['content']['positions'][] = $this->buildPosition($checkData, $item, $isSellReturn);
		}

		$paymentTypeMap = $this->getPaymentTypeMap();
		foreach ($checkData['payments'] as $payment)
		{
			$result['content']['checkClose']['payments'][] = [
				'type' => $paymentTypeMap[$payment['type']],
				'amount' => $payment['sum'],
			];
		}

		return $result;
	}

	/**
	 * @param array $checkData
	 * @param array $item
	 * @param bool $isSellReturn
	 * @return array
	 */
	protected function buildPosition(array $checkData, array $item, bool $isSellReturn): array
	{
		$result = [
			'text' => $this->buildPositionText($item),
			'quantity' => $this->buildPositionQuantity($item),
			'price' => $this->buildPositionPrice($item),
			'tax' => $this->buildPositionTax($checkData, $item),
			'paymentMethodType' => $this->buildPositionPaymentMethodType($checkData),
			'paymentSubjectType' => $this->buildPositionPaymentSubjectType($item),
		];

		if (isset($item['nomenclature_code']))
		{
			$result['nomenclatureCode'] = $this->buildPositionNomenclatureCode($item);
		}

		return $result;
	}

	/**
	 * @param array $item
	 * @return string
	 */
	protected function buildPositionText(array $item)
	{
		return mb_substr($item['name'], 0, self::MAX_TEXT_LENGTH);
	}

	/**
	 * @param array $item
	 * @return mixed
	 */
	protected function buildPositionQuantity(array $item)
	{
		return $item['quantity'];
	}

	/**
	 * @param array $item
	 * @return mixed
	 */
	protected function buildPositionPrice(array $item)
	{
		return $item['price'];
	}

	/**
	 * @param array $checkData
	 * @return int|mixed
	 */
	protected function buildPositionPaymentMethodType(array $checkData)
	{
		$checkType = $this->getCheckTypeMap();

		return $checkType[$checkData['type']];
	}

	/**
	 * @param array $item
	 * @return int|mixed
	 */
	protected function buildPositionPaymentSubjectType(array $item)
	{
		$paymentObjectMap = $this->getPaymentObjectMap();

		return $paymentObjectMap[$item['payment_object']];
	}

	/**
	 * @param array $checkData
	 * @param $item
	 * @return int|mixed
	 */
	protected function buildPositionTax(array $checkData, $item)
	{
		$vat = $this->getValueFromSettings('VAT', $item['vat']);
		if ($vat === null)
		{
			$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
		}

		return $this->mapVatValue($checkData['type'], $vat);
	}

	/**
	 * @param array $item
	 * @return string
	 */
	private function buildPositionNomenclatureCode(array $item)
	{
		return base64_encode($item['nomenclature_code']);
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
	protected function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 1,
			Check::PAYMENT_OBJECT_EXCISE => 2,
			Check::PAYMENT_OBJECT_JOB => 3,
			Check::PAYMENT_OBJECT_SERVICE => 4,
			Check::PAYMENT_OBJECT_PAYMENT => 10,
			Check::PAYMENT_OBJECT_GAMBLING_BET => 5,
			Check::PAYMENT_OBJECT_GAMBLING_PRIZE => 6,
			Check::PAYMENT_OBJECT_LOTTERY => 7,
			Check::PAYMENT_OBJECT_LOTTERY_PRIZE => 8,
			Check::PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY => 9,
			Check::PAYMENT_OBJECT_AGENT_COMMISSION => 11,
			Check::PAYMENT_OBJECT_COMPOSITE => 12,
			Check::PAYMENT_OBJECT_ANOTHER => 13,
			Check::PAYMENT_OBJECT_PROPERTY_RIGHT => 14,
			Check::PAYMENT_OBJECT_NON_OPERATING_GAIN => 15,
			Check::PAYMENT_OBJECT_SALES_TAX => 17,
			Check::PAYMENT_OBJECT_RESORT_FEE => 18,
			Check::PAYMENT_OBJECT_DEPOSIT => 19,
			Check::PAYMENT_OBJECT_EXPENSE => 20,
			Check::PAYMENT_OBJECT_PENSION_INSURANCE_IP => 21,
			Check::PAYMENT_OBJECT_PENSION_INSURANCE => 22,
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE_IP => 23,
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE => 24,
			Check::PAYMENT_OBJECT_SOCIAL_INSURANCE => 25,
			Check::PAYMENT_OBJECT_CASINO_PAYMENT => 26,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE => 2,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE => 2,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING => 1,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING => 1,
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
	 * @throws Main\ObjectException
	 */
	public function printImmediately(Check $check)
	{
		return $this->registerCheck(
			$this->getUrl().'/documents/',
			$this->buildCheckQuery($check)
		);
	}

	/**
	 * @param $url
	 * @param $data
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectException
	 */
	protected function registerCheck($url, $data)
	{
		$result = new Result();

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
	 * @throws Main\ObjectException
	 */
	public function check(Check $check)
	{
		$url = $this->getUrl();
		$url .= '/documents/'.$this->getValueFromSettings('SERVICE', 'INN').'/status/'.$check->getField('EXTERNAL_UUID');

		return $this->checkInternal($url);
	}

	/**
	 * @param $url
	 * @return Result
	 * @throws Main\ObjectException
	 */
	protected function checkInternal($url)
	{
		$result = new Result();

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

		if (static::hasMeasureSettings())
		{
			$settings['MEASURE'] = static::getMeasureSettings();
		}

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
	 * @return bool
	 */
	protected static function hasMeasureSettings(): bool
	{
		return false;
	}

	/**
	 * @return array
	 */
	protected static function getMeasureSettings(): array
	{
		$measureItems = [
			'DEFAULT' => [
				'TYPE' => 'STRING',
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_MEASURE_SUPPORT_SETTINGS_DEFAULT_VALUE'),
				'VALUE' => 0,
			]
		];
		if (Main\Loader::includeModule('catalog'))
		{
			$measuresList = \CCatalogMeasure::getList();
			while ($measure = $measuresList->fetch())
			{
				$measureItems[$measure['CODE']] = [
					'TYPE' => 'STRING',
					'LABEL' => $measure['MEASURE_TITLE'],
					'VALUE' => MeasureCodeToTag2108Mapper::getTag2108Value($measure['CODE']),
				];
			}
		}

		return [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_MEASURE_SUPPORT_SETTINGS'),
			'ITEMS' => $measureItems,
		];
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
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.05;
	}

	/**
	 * @param CorrectionCheck $check
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectException
	 */
	public function printCorrectionImmediately(CorrectionCheck $check)
	{
		return $this->registerCheck(
			$this->getUrl() . $this->getCorrectionUrlPath(),
			$this->buildCorrectionCheckQuery($check)
		);
	}

	/**
	 * @return string
	 */
	protected function getCorrectionUrlPath(): string
	{
		return '/corrections/';
	}

	/**
	 * @param CorrectionCheck $check
	 * @return array
	 * @throws Main\ObjectException
	 */
	public function buildCorrectionCheckQuery(CorrectionCheck $check)
	{
		$data = $this->getCheckData($check);

		$calculatedSignMap = $this->getCalculatedSignMap();

		$result = [
			'id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
			'group' => $this->getField('NUMBER_KKM') ?: null,
			'key' => $this->getValueFromSettings('SECURITY', 'KEY_SIGN') ?: null,
			'content' => [
				'type' => $calculatedSignMap[$data['calculated_sign']],
				'correctionType' => $this->getCorrectionTypeMap($data['correction_info']['type']),
				'causeDocumentDate' => $this->getCorrectionCauseDocumentDate($data['correction_info']),
				'causeDocumentNumber' => $this->getCorrectionCauseDocumentNumber($data['correction_info']),
				'totalSum' => $this->getCorrectionTotalSum($data['correction_info']),
				'taxationSystem' => $this->getValueFromSettings('TAX', 'SNO')
			],
		];

		foreach ($data['payments'] as $payment)
		{
			if ($payment['type'] === Check::PAYMENT_TYPE_CASH)
			{
				$result['content']['cashSum'] = (float)$payment['sum'];
			}
			else
			{
				$result['content']['eCashSum'] = (float)$payment['sum'];
			}
		}

		$vats = $this->getVatsByCheckData($data);
		if (is_array($vats))
		{
			foreach ($vats as $vat)
			{
				$result['content'][$vat['code']] = $vat['value'];
			}
		}

		return $result;
	}

	/**
	 * @param $correctionInfo
	 * @return string
	 */
	protected function getCorrectionCauseDocumentDate($correctionInfo)
	{
		$documentDate = new Main\Type\DateTime($correctionInfo['document_date']);

		return $documentDate->format('Y-m-d\TH:i:s');
	}

	/**
	 * @param $correctionInfo
	 * @return mixed
	 */
	protected function getCorrectionCauseDocumentNumber($correctionInfo)
	{
		return $correctionInfo['document_number'];
	}

	/**
	 * @param $correctionInfo
	 * @return mixed
	 */
	protected function getCorrectionTotalSum($correctionInfo)
	{
		return $correctionInfo['total_sum'];
	}

	/**
	 * @param array $data
	 * @return array|null
	 */
	protected function getVatsByCheckData(array $data): ?array
	{
		if (!isset($data['vats']) || !is_array($data['vats']) || empty($data['vats']))
		{
			return null;
		}

		$result = [];
		foreach ($data['vats'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['type']);
			if (is_null($vat) || $vat === '')
			{
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
			}

			switch ($vat)
			{
				case self::CODE_VAT_0:
					$vatKey = '3Sum';
					break;
				case self::CODE_VAT_10:
					$vatKey = '2Sum';
					break;
				case self::CODE_VAT_20:
					$vatKey = '1Sum';
					break;
				default:
					$vatKey = '4Sum';
					break;
			}

			$result[] = [
				'code' => $this->getVatKeyPrefix() . $vatKey,
				'value' => $item['sum']
			];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getVatKeyPrefix(): string
	{
		return 'tax';
	}

	/**
	 * @param $type
	 * @return int
	 */
	protected function getCorrectionTypeMap($type)
	{
		$map = [
			CorrectionCheck::CORRECTION_TYPE_INSTRUCTION => 1,
			CorrectionCheck::CORRECTION_TYPE_SELF => 0
		];

		return $map[$type] ?? 0;
	}

	/**
	 * @param CorrectionCheck $check
	 * @return Result
	 * @throws Main\ObjectException
	 */
	public function checkCorrection(CorrectionCheck $check)
	{
		return $this->checkInternal(
			$this->getUrl()
			. $this->getCorrectionUrlPath()
			. $this->getValueFromSettings('SERVICE', 'INN')
			. '/status/'
			. $check->getField('EXTERNAL_UUID')
		);
	}
}
