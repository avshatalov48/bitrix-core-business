<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Registry;

/**
 * Class CorrectionCheck
 * @package Bitrix\Sale\Cashbox
 */
abstract class CorrectionCheck extends AbstractCheck
{
	public const CORRECTION_TYPE_SELF = 'self';
	public const CORRECTION_TYPE_INSTRUCTION = 'instruction';

	private const CHECK_CURRENCY_RUB = 'RUB';

	protected $correction = [];

	public function __construct()
	{
		parent::__construct();

		$this->fields['ENTITY_REGISTRY_TYPE'] = Registry::REGISTRY_TYPE_ORDER;
		$this->fields['CURRENCY'] = self::CHECK_CURRENCY_RUB;
	}

	/**
	 * @return Main\ORM\Data\AddResult|Main\ORM\Data\UpdateResult
	 * @throws \Exception
	 */
	public function save()
	{
		$isNew = (int)$this->fields['ID'] === 0;

		$result = parent::save();
		if (!$result->isSuccess())
		{
			return $result;
		}

		if ($isNew)
		{
			$r = Internals\CashboxCheckCorrectionTable::add([
				'CHECK_ID' => $this->fields['ID'],
				'CORRECTION_TYPE' => $this->correction['CORRECTION_TYPE'],
				'DOCUMENT_NUMBER' => $this->correction['DOCUMENT_NUMBER'],
				'DOCUMENT_DATE' => $this->correction['DOCUMENT_DATE'],
				'DESCRIPTION' => $this->correction['DESCRIPTION'],
				'CORRECTION_PAYMENT' => $this->correction['CORRECTION_PAYMENT'],
				'CORRECTION_VAT' => $this->correction['CORRECTION_VAT'],
			]);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	public function getDataForCheck()
	{
		$result = [
			'type' => static::getType(),
			'unique_id' => $this->getField('ID'),
			'date_create' => new Main\Type\DateTime(),
			'calculated_sign' => static::getCalculatedSign()
		];

		$data = $this->extractData();
		if ($data)
		{
			$result['correction_info'] = [
				'type' => $data['CORRECTION_TYPE'],
				'document_number' => $data['DOCUMENT_NUMBER'],
				'document_date' => $data['DOCUMENT_DATE'],
				'description' => $data['DESCRIPTION'],
				'total_sum' => $data['TOTAL_SUM'],
			];

			if (isset($data['PAYMENTS']))
			{
				$result['payments'] = [];

				foreach ($data['PAYMENTS'] as $payment)
				{
					$result['payments'][] = [
						'type' => $payment['TYPE'],
						'sum' => $payment['SUM'],
					];
				}
			};

			if (isset($data['VATS']))
			{
				$result['vats'] = [];

				foreach ($data['VATS'] as $vat)
				{
					$result['vats'][] = [
						'type' => $vat['TYPE'],
						'sum' => $vat['SUM'],
					];
				}
			}
		}

		return $result;
	}

	protected function extractDataInternal()
	{
		$result = [
			'CORRECTION_TYPE' => $this->correction['CORRECTION_TYPE'],
			'DOCUMENT_NUMBER' => $this->correction['DOCUMENT_NUMBER'],
			'DOCUMENT_DATE' => $this->correction['DOCUMENT_DATE'],
			'DESCRIPTION' => $this->correction['DESCRIPTION'],
			'PAYMENTS' => $this->correction['CORRECTION_PAYMENT'],
			'TOTAL_SUM' => 0
		];

		if ($this->correction['CORRECTION_VAT'])
		{
			$result['VATS'] = [];

			foreach ($this->correction['CORRECTION_VAT'] as $vat)
			{
				$result['VATS'][] = [
					'TYPE' => $this->getVatIdByVatRate($vat['TYPE']),
					'SUM' => $vat['SUM'],
				];
			}
		}

		if ($this->correction['CORRECTION_PAYMENT'])
		{
			foreach ($this->correction['CORRECTION_PAYMENT'] as $payment)
			{
				$result['TOTAL_SUM'] += $payment['SUM'];
			}
		}

		return $result;
	}

	public function setAvailableCashbox(array $cashboxList)
	{
		foreach ($cashboxList as $item)
		{
			$cashbox = Cashbox::create($item);
			if (!$cashbox || !$cashbox->isCorrection())
			{
				throw new Main\SystemException('Cashbox '.$cashbox::getName().' is not supported correction check');
			}
		}

		parent::setAvailableCashbox($cashboxList);
	}

	/**
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentException
	 */
	public function setCorrectionField($name, $value)
	{
		if (!$this->isCorrectionFieldAvailable($name))
		{
			throw new Main\ArgumentException('Incorrect field '.$name);
		}

		if ($name === 'DOCUMENT_DATE')
		{
			$value = new Main\Type\Date($value);
		}

		$this->correction[$name] = $value;
	}

	/**
	 * @param $fields
	 * @throws Main\ArgumentException
	 */
	public function setCorrectionFields($fields)
	{
		foreach ($fields as $name => $value)
		{
			$this->setCorrectionField($name, $value);

			if ($name === 'CORRECTION_PAYMENT')
			{
				$this->fields['SUM'] = $this->calculateSumByPayments($value);
			}
		}
	}

	private function calculateSumByPayments(array $payments)
	{
		$result = 0;

		foreach ($payments as $item)
		{
			$result += $item['SUM'];
		}

		return $result;
	}

	/**
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getCorrectionFields()
	{
		if ($this->correction)
		{
			return $this->correction;
		}

		if ($this->getField('ID') > 0)
		{
			$dbRes = Internals\CashboxCheckCorrectionTable::getList([
				'select' => static::getAvailableCorrectionFields(),
				'filter' => [
					'=CHECK_ID' => $this->getField('ID')
				]
			]);
			if ($data = $dbRes->fetch())
			{
				return $data;
			}
		}

		return [];
	}

	/**
	 * @return array
	 */
	private function getAvailableCorrectionFields()
	{
		$fields = array_keys(Internals\CashboxCheckCorrectionTable::getMap());

		return array_filter(
			$fields,
			function ($name)
			{
				return !in_array($name, ['CHECK', 'ID', 'CHECK_ID']);
			}
		);
	}

	/**
	 * @param $name
	 * @return bool
	 */
	private function isCorrectionFieldAvailable($name)
	{
		$fields = $this->getAvailableCorrectionFields();

		return in_array($name, $fields);
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_NONE;
	}
}
