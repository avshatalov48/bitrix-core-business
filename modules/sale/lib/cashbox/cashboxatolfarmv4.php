<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\Result;
use Bitrix\Catalog;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxAtolFarmV4
 * @package Bitrix\Sale\Cashbox
 */
class CashboxAtolFarmV4 extends CashboxAtolFarm
{
	const SERVICE_URL = 'https://online.atol.ru/possystem/v4';

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$serviceEmail = $this->getValueFromSettings('SERVICE', 'EMAIL');
		if (!$serviceEmail)
		{
			$serviceEmail = static::getDefaultServiceEmail();
		}

		$result = array(
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'service' => array(
				'callback_url' => $this->getCallbackUrl(),
			),
			'receipt' => array(
				'client' => array(),
				'company' => array(
					'email' => $serviceEmail,
					'sno' => $this->getValueFromSettings('TAX', 'SNO'),
					'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
					'payment_address' => $this->getValueFromSettings('SERVICE', 'P_ADDRESS'),
				),
				'payments' => array(),
				'items' => array(),
				'total' => (float)$data['total_sum']
			)
		);

		$email = $data['client_email'] ?: '';

		$phone = \NormalizePhone($data['client_phone']);
		if (is_string($phone))
		{
			if ($phone[0] === '7')
				$phone = substr($phone, 1);
		}
		else
		{
			$phone = '';
		}

		$clientInfo = $this->getValueFromSettings('CLIENT', 'INFO');
		if ($clientInfo === 'PHONE')
		{
			$result['receipt']['client'] = ['phone' => $phone];
		}
		elseif ($clientInfo === 'EMAIL')
		{
			$result['receipt']['client'] = ['email' => $email];
		}
		else
		{
			$result['receipt']['client'] = [];

			if ($email)
			{
				$result['receipt']['client']['email'] = $email;
			}

			if ($phone)
			{
				$result['receipt']['client']['phone'] = $phone;
			}
		}

		$paymentTypeMap = $this->getPaymentTypeMap();
		foreach ($data['payments'] as $payment)
		{
			$result['receipt']['payments'][] = array(
				'type' => $paymentTypeMap[$payment['type']],
				'sum' => (float)$payment['sum']
			);
		}

		$checkTypeMap = $this->getCheckTypeMap();
		$paymentObjectMap = $this->getPaymentObjectMap();
		foreach ($data['items'] as $i => $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			if ($vat === null)
				$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');

			$result['receipt']['items'][] = array(
				'name' => $item['name'],
				'price' => (float)$item['price'],
				'sum' => (float)$item['sum'],
				'quantity' => $item['quantity'],
				'payment_method' => $checkTypeMap[$check::getType()],
				'payment_object' => $paymentObjectMap[$item['payment_object']],
				'vat' => array(
					'type' => $vat
				),
			);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 'commodity',
			Check::PAYMENT_OBJECT_SERVICE => 'service',
			Check::PAYMENT_OBJECT_JOB => 'job',
			Check::PAYMENT_OBJECT_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_PAYMENT => 'payment',
		];
	}

	/**
	 * @return array
	 */
	private function getPaymentTypeMap()
	{
		return array(
			Check::PAYMENT_TYPE_CASH => 4,
			Check::PAYMENT_TYPE_CASHLESS => 1,
			Check::PAYMENT_TYPE_ADVANCE => 2,
			Check::PAYMENT_TYPE_CREDIT => 3,
		);
	}

	/**
	 * @return string
	 */
	private static function getDefaultServiceEmail()
	{
		return Main\Config\Option::get('main', 'email_from');
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_V4_TITLE');
	}

	/**
	 * @return array
	 */
	protected function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 'full_payment',
			SellReturnCashCheck::getType() => 'full_payment',
			SellReturnCheck::getType() => 'full_payment',
			AdvancePaymentCheck::getType() => 'advance',
			AdvanceReturnCashCheck::getType() => 'advance',
			AdvanceReturnCheck::getType() => 'advance',
			PrepaymentCheck::getType() => 'prepayment',
			PrepaymentReturnCheck::getType() => 'prepayment',
			PrepaymentReturnCashCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCashCheck::getType() => 'full_prepayment',
			CreditCheck::getType() => 'credit',
			CreditReturnCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'credit_payment',
		);
	}

	/**
	 * @param $operation
	 * @param $token
	 * @param array $queryData
	 * @return string
	 * @throws Main\SystemException
	 */
	protected function getUrl($operation, $token, array $queryData = array())
	{
		$groupCode = $this->getField('NUMBER_KKM');

		if ($operation === static::OPERATION_CHECK_REGISTRY)
		{
			return static::SERVICE_URL.'/'.$groupCode.'/'.$queryData['CHECK_TYPE'].'?token='.$token;
		}
		elseif ($operation === static::OPERATION_CHECK_CHECK)
		{
			return static::SERVICE_URL.'/'.$groupCode.'/report/'.$queryData['EXTERNAL_UUID'].'?token='.$token;
		}

		throw new Main\SystemException();
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = parent::getSettings($modelId);
		unset($settings['PAYMENT_TYPE']);

		$settings['SERVICE']['ITEMS']['EMAIL'] = array(
			'TYPE' => 'STRING',
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SERVICE_EMAIL_LABEL'),
			'VALUE' => static::getDefaultServiceEmail()
		);

		return $settings;
	}

	/**
	 * @param array $checkData
	 * @return Result
	 */
	protected function validate(array $checkData)
	{
		$result = new Result();

		if ($checkData['receipt']['client']['email'] === '' && $checkData['receipt']['client']['phone'] === '')
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_ERR_EMPTY_PHONE_EMAIL')));
		}

		foreach ($checkData['receipt']['items'] as $item)
		{
			if ($item['vat'] === null)
			{
				$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_ERR_EMPTY_TAX')));
				break;
			}
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public static function isSupportedFFD105()
	{
		return true;
	}
}