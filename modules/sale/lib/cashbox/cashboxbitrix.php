<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Main\Localization;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\PriceMaths;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBitrix
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBitrix extends Cashbox
{
	const TYPE_Z_REPORT = 1;

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$result = array();

		$data = $check->getDataForCheck();

		foreach ($data['payments'] as $payment)
		{
			$result['payments'][] = array(
				'type' => $this->getValueFromSettings('PAYMENT_TYPE', $payment['type']),
				'value' => $payment['sum']
			);
		}

		$typeMap = $this->getCheckTypeMap();
		if (isset($typeMap[$data['type']]))
			$result['type'] = $typeMap[$data['type']];
		else
			return array();

		$result['uuid'] = static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']);
		$result['zn'] = $this->getField('NUMBER_KKM');
		$result['items'] = array();
		foreach ($data['items'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			if ($vat === null)
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');

			$value = array(
				'name' => $item['name'],
				'price' => $item['base_price'],
				'quantity' => $item['quantity'],
				'VAT' => (int)$vat
			);

			if (isset($item['discount']) && is_array($item['discount']))
			{
				$discountValue = PriceMaths::roundPrecision($item['base_price']*$item['quantity']) - $item['sum'];
				$value['discount'] = $discountValue;

				$discountType = $item['discount']['discount_type'] === 'P' ? 1 : 0;
				$value['discount_type'] = $discountType;
			}

			$result['items'][] = $value;
		}
		$result['client'] = $data['client_email'];

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];
		$result['timestamp'] = (string)$dateTime->getTimestamp();

		return $result;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function buildZReportQuery($id)
	{
		$dateTime = new Main\Type\DateTime();

		return array(
			'type' => static::TYPE_Z_REPORT,
			'uuid' => static::buildUuid(static::UUID_TYPE_REPORT, $id),
			'timestamp' => (string)$dateTime->getTimestamp(),
			'zn' => $this->getField('NUMBER_KKM')
		);
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_TITLE');
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public static function getCashboxList(array $data)
	{
		$result = array();

		if (isset($data['kkm']) && is_array($data['kkm']))
		{
			$factoryNum = array();
			foreach ($data['kkm'] as $kkm)
				$factoryNum[] = $kkm['zn'];

			$cashboxList = Manager::getListFromCache();
			foreach ($cashboxList as $item)
			{
				if (in_array($item['NUMBER_KKM'], $factoryNum))
					$result[$item['NUMBER_KKM']] = $item;
			}

			foreach ($data['kkm'] as $kkm)
			{
				if (!isset($result[$kkm['zn']]))
				{
					$result[$kkm['zn']] = array(
						'NUMBER_KKM' => $kkm['zn'],
						'NUMBER_FN' => $kkm['fn'],
						'HANDLER' => '\\'.get_called_class(),
						'CACHE' => $kkm['cache'],
						'INCOME' => $kkm['reg_income'],
						'NZ_SUM' => $kkm['nz_sum']
					);
				}

				$result[$kkm['zn']]['PRESENTLY_ENABLED'] = ($kkm['status'] === 'ok') ? 'Y' : 'N';
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public static function applyPrintResult(array $data)
	{
		$processedIds = array();

		foreach ($data['kkm'] as $kkm)
		{
			if (isset($kkm['printed']) && is_array($kkm['printed']))
			{
				foreach ($kkm['printed'] as $item)
				{
					$uuid = static::parseUuid($item['uuid']);

					$result = null;
					if ($uuid['type'] === static::UUID_TYPE_CHECK)
					{
						$result = static::applyCheckResult($item);
					}
					elseif ($uuid['type'] === static::UUID_TYPE_REPORT)
					{
						$result = static::applyZReportResult($item);
					}

					if ($result !== null)
					{
						if ($result->isSuccess())
						{
							$processedIds[] = $item['uuid'];
						}
						else
						{
							$errors = $result->getErrors();
							foreach ($errors as $error)
							{
								if ($error instanceof Errors\Error)
								{
									$processedIds[] = $item['uuid'];
									break;
								}
							}
						}
					}
				}
			}
		}

		return $processedIds;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function extractCheckData(array $data)
	{
		$uuid = self::parseUuid($data['uuid']);
		$result = array(
			'ID' => $uuid['id'],
			'TYPE' => $uuid['type'],
		);

		if ($data['code'] !== 0 && isset($data['message']))
		{
			$errorMsg = Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_ERR'.$data['code']);
			if (!$errorMsg)
				$errorMsg = $data['message'];

			$errorType = static::getErrorType($data['code']);

			$result['ERROR'] = array(
				'CODE' => $data['code'],
				'MESSAGE' => $errorMsg,
				'TYPE' => ($errorType === Errors\Error::TYPE) ? Errors\Error::TYPE : Errors\Warning::TYPE
			);
		}

		$result['LINK_PARAMS'] = static::getCheckLinkParams($data);

		return $result;
	}

	/**
	 * @param $data
	 * @return array
	 */
	private static function getCheckLinkParams($data)
	{
		$linkParams = static::parseQrParam($data['qr']);

		$uuid = self::parseUuid($data['uuid']);
		$check = CheckManager::getObjectById($uuid['id']);
		if ($check)
		{
			$linkParams[Check::PARAM_CALCULATION_ATTR] = $check::getCalculatedSign();
		}

		if (isset($data['rn']))
		{
			$linkParams[Check::PARAM_REG_NUMBER_KKT] = $data['rn'];
		}

		return $linkParams;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function extractZReportData(array $data)
	{
		$uuid = self::parseUuid($data['uuid']);
		$result = array(
			'ID' => $uuid['id'],
			'TYPE' => $uuid['type'],
		);

		if ($data['code'] !== 0 && isset($data['message']))
		{
			$errorMsg = Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_ERR'.$data['code']);
			if (!$errorMsg)
				$errorMsg = $data['message'];

			$errorType = static::getErrorType($data['code']);
			if ($errorType == null)
				$errorType = Errors\Warning::TYPE;

			$result['ERROR'] = array('MESSAGE' => $errorMsg, 'CODE' => $data['code'], 'TYPE' => $errorType);
		}

		$result['CASH_SUM'] = $data['payments_cache'];
		$result['CASHLESS_SUM'] = $data['reg_income'] - $data['payments_cache'];
		$result['CUMULATIVE_SUM'] = $data['nz_sum'];
		$result['RETURNED_SUM'] = $data['reg_return'];
		$result['LINK_PARAMS'] = static::parseQrParam($data['qr']);

		return $result;
	}

	/**
	 * @return array
	 */
	private function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 1,
			SellReturnCashCheck::getType() => 2,
			SellReturnCheck::getType() => 2
		);
	}

	/**
	 * @param $qr
	 * @return array
	 */
	private static function parseQrParam($qr)
	{
		$result = array();
		$params = explode('&', $qr);
		if ($params)
		{
			foreach ($params as $param)
			{
				list($key, $value) = explode('=', $param);
				switch ($key)
				{
					case 'fn' :
						$key = Check::PARAM_FN_NUMBER;
						break;
					case 'fp' :
						$key = Check::PARAM_FISCAL_DOC_ATTR;
						break;
					case 'i' :
						$key = Check::PARAM_FISCAL_DOC_NUMBER;
						break;
					case 't' :
						$key = Check::PARAM_DOC_TIME;
						$dateTime = new Main\Type\DateTime($value, 'Ymd\THis');
						$value = (string)$dateTime->getTimestamp();
						break;
					case 's' :
						$key = Check::PARAM_DOC_SUM;
						break;
					case 'n' :
						$key = Check::PARAM_CALCULATION_ATTR;
						break;
					default:
						continue;
				}

				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * @param $errorCode
	 * @throws Main\NotImplementedException
	 * @return int
	 */
	protected static function getErrorType($errorCode)
	{
		$errors = array(-3800, -3803, -3804, -3805, -3816, -3807, -3896, -3897, -4026);
		if (in_array($errorCode, $errors))
			return Errors\Error::TYPE;

		$warnings = array();
		if (in_array($errorCode, $warnings))
			return Errors\Warning::TYPE;

		return null;
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = array();

		$kkmList = static::getSupportedKkmModels();
		if (isset($kkmList[$modelId]))
		{
			$defaultSettings = $kkmList[$modelId]['SETTINGS'];

			if (isset($defaultSettings['PAYMENT_TYPE']))
			{
				$settings['PAYMENT_TYPE'] = array(
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_P_TYPE'),
					'REQUIRED' => 'Y',
					'ITEMS' => array()
				);

				$systemPaymentType = array(
					Check::PAYMENT_TYPE_CASH,
					Check::PAYMENT_TYPE_CASHLESS,
				);
				foreach ($systemPaymentType as $type)
				{
					$settings['PAYMENT_TYPE']['ITEMS'][$type] = array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_P_TYPE_LABEL_'.ToUpper($type)),
						'VALUE' => $defaultSettings['PAYMENT_TYPE'][$type]
					);
				}
			}


			$settings['VAT'] = array(
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_VAT'),
				'REQUIRED' => 'Y',
				'ITEMS' => array(
					'NOT_VAT' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_VAT_LABEL_NOT_VAT'),
						'VALUE' => 4
					)
				)
			);

			if (Main\Loader::includeModule('catalog'))
			{
				$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
				$vatList = $dbRes->fetchAll();
				if ($vatList)
				{
					foreach ($vatList as $vat)
					{
						$value = '';
						if (isset($defaultSettings['VAT'][(int)$vat['RATE']]))
							$value = $defaultSettings['VAT'][(int)$vat['RATE']];

						$settings['VAT']['ITEMS'][(int)$vat['ID']] = array(
							'TYPE' => 'STRING',
							'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
							'VALUE' => $value
						);
					}
				}
			}
		}

		$hours = array();
		for ($i = 0; $i < 24; $i++)
		{
			$value = ($i < 10) ? '0'.$i : $i;
			$hours[$i] = $value;
		}

		$minutes = array();
		for ($i = 0; $i < 60; $i+=5)
		{
			$value = ($i < 10) ? '0'.$i : $i;
			$minutes[$i] = $value;
		}

		$settings['Z_REPORT'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_Z_REPORT'),
			'ITEMS' => array(
				'TIME' => array(
					'TYPE' => 'DELIVERY_MULTI_CONTROL_STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_Z_REPORT_LABEL'),
					'ITEMS' => array(
						'H' => array(
							'TYPE' => 'ENUM',
							'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_Z_REPORT_LABEL_H'),
							'VALUE' => 23,
							'OPTIONS' => $hours
						),
						'M' => array(
							'TYPE' => 'ENUM',
							'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_Z_REPORT_LABEL_M'),
							'VALUE' => 30,
							'OPTIONS' => $minutes
						),
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

		if ($settings['PAYMENT_TYPE'])
		{
			/* hack is for difference between real values of payment cashbox's settings and user view (diff is '-1') */
			foreach ($settings['PAYMENT_TYPE'] as $i => $payment)
			{
				if ((int)$payment)
					$settings['PAYMENT_TYPE'][$i] = (int)$payment - 1;
				else
					$settings['PAYMENT_TYPE'][$i] = 0;
			}
		}

		return $settings;
	}

	/**
	 * @return array
	 */
	public static function getGeneralRequiredFields()
	{
		$generalRequiredFields = parent::getGeneralRequiredFields();

		$map = CashboxTable::getMap();
		$generalRequiredFields['KKM_ID'] = $map['KKM_ID']['title'];
		$generalRequiredFields['NUMBER_KKM'] = $map['NUMBER_KKM']['title'];

		return $generalRequiredFields;
	}

	/**
	 * @return array
	 */
	public static function getSupportedKkmModels()
	{
		return array(
			'atol' => array(
				'NAME' => 'ATOL',
				'SETTINGS' => array(
					'VAT' => array(
						'NOT_VAT' => 4,
						0 => 1,
						10 => 2,
						18 => 3
					),
					'PAYMENT_TYPE' => array(
						Check::PAYMENT_TYPE_CASH => 0,
						Check::PAYMENT_TYPE_CASHLESS => 3,
					)
				)
			),
		);
	}
}
