<?php

namespace Bitrix\Sale\Exchange\OneC;
use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Exchange\ISettings;
use Bitrix\Sale\Exchange\ISettingsExport;
use Bitrix\Sale\Exchange\ISettingsImport;
use Bitrix\Sale\Payment;


/**
 * Class ConverterDocumentPayment
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentPayment extends Converter
{
	/**
	 * @return array
	 */
	protected function getFieldsInfo()
	{
		return PaymentDocument::getFieldsInfo();
	}

	/**
	 * @param $documentImport
	 * @return array
	 * @throws ArgumentException
	 */
	public function resolveParams($documentImport)
	{
		if(!($documentImport instanceof DocumentBase))
			throw new ArgumentException("Document must be instanceof DocumentBase");

		$result = array();

		$params = $documentImport->getFieldValues();

		$availableFields = array_merge(Payment::getAvailableFields(), array('CASH_BOX_CHECKS'));

		/** TODO: huck */
		rsort($availableFields);

		foreach ($availableFields as $k)
		{
			switch($k)
			{
				case 'ID_1C':
				case 'VERSION_1C':
					if(isset($params[$k]))
						$fields[$k] = $params[$k];
					break;
				case 'SUM':
					if(isset($params['AMOUNT']))
						$fields[$k] = $params['AMOUNT'];
					break;
				case 'COMMENTS':
					if(isset($params['COMMENT']))
						$fields[$k] = $params['COMMENT'];
					break;
				case 'PAY_VOUCHER_DATE':
					if(isset($params['REK_VALUES']['1C_PAYED_DATE']))
						$fields[$k] = $params['REK_VALUES']['1C_PAYED_DATE'];
					break;
				case 'PAY_VOUCHER_NUM':
					if(isset($params['REK_VALUES']['1C_PAYED_NUM']))
						$fields[$k] = $params['REK_VALUES']['1C_PAYED_NUM'];
					break;
				case 'PAID':
					$payed='';
					$cancel='';

					if(isset($params['REK_VALUES']['1C_PAYED']))
						$payed = $params['REK_VALUES']['1C_PAYED'];
					if(isset($params['REK_VALUES']['CANCEL']))
						$cancel = $params['REK_VALUES']['CANCEL'];

					if($payed == 'Y')
						$fields[$k] = 'Y';
					elseif($cancel == 'Y')
						$fields[$k] = 'N';
					break;
				case 'IS_RETURN':
					if(isset($params['REK_VALUES']['1C_RETURN']))
					{
						$value = $params['REK_VALUES']['1C_RETURN'];
						if($value == 'Y')
							$fields[$k] = 'Y';
					}
					break;
				case 'PAY_RETURN_COMMENT':
					if(isset($params['REK_VALUES']['1C_RETURN_REASON']))
						$fields[$k] = $params['REK_VALUES']['1C_RETURN_REASON'];
					break;
				case 'PAY_SYSTEM_ID':
					$fields[$k] = $this->getPaySystemId($params['REK_VALUES']);
					break;
				case 'CASH_BOX_CHECKS':
					if(is_array($params[$k]))
					{
						foreach($params[$k] as $property=>$value)
						{
							switch($property)
							{
								case 'ID':
									$cashBoxChecks[$property] = $value;
									break;
								case 'CASHBOX_URL':
									$cashBoxChecks['LINK_PARAMS']['URL'] = $value;
									break;
								case 'CASHBOX_FISCAL_SIGN':
									$cashBoxChecks['LINK_PARAMS']['FISCAL_SIGN'] = $value;
									break;
								case 'CASHBOX_REG_NUMBER_KKT':
									$cashBoxChecks['LINK_PARAMS']['REG_NUMBER_KKT'] = $value;
							}
						}
					}
					break;
			}
		}

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['CASH_BOX_CHECKS'] = isset($cashBoxChecks)? $cashBoxChecks:array();

		return $result;
	}

	/**
	 * @param $fields
	 * @return int
	 */
	public function getPaySystemId($fields)
	{
		$paySystemId = 0;
		if(isset($fields['PAY_SYSTEM_ID']))
		{
			$paySystemId = $fields['PAY_SYSTEM_ID'];
		}

		if($paySystemId<=0)
		{
			if(isset($fields['PAY_SYSTEM_ID_DEFAULT']))
			{
				$paySystemId = $fields['PAY_SYSTEM_ID_DEFAULT'];
			}
		}
		/** @var ImportSettings $settings */
		$settings = $this->getSettings();

		if($paySystemId<=0)
		{
			$paySystemId = $settings->paySystemIdFor($this->getEntityTypeId());
		}

		if($paySystemId<=0)
		{
			$paySystemId = $settings->paySystemIdDefaultFor($this->getEntityTypeId());
		}

		return $paySystemId;
	}

	/**
	 * @param Payment|null $payment
	 * @param array $fields
	 */
	static public function sanitizeFields($payment=null, array &$fields, ISettings $settings)
	{
		if(!empty($payment) && !($payment instanceof Payment))
			throw new ArgumentException("Entity must be instanceof Payment");

		foreach($fields as $k=>$v)
		{
			switch($k)
			{
				case 'AMOUNT':
					if(!empty($payment) && $payment->isPaid())
					{
						unset($fields['SUM']);
					}
					break;
				case 'PAY_SYSTEM_ID':
					if(!empty($payment))
					{
						unset($fields['PAY_SYSTEM_ID']);
					}
					break;
			}
		}

		if(empty($payment))
		{
			/** @var ISettingsImport $settings */
			$fields['CURRENCY'] = $settings->getCurrency();
		}
		unset($fields['ID']);
	}

	public function externalize(array $fields)
	{
		$result = array();

		$traits = $fields['TRAITS'];
		$businessValue = $fields['BUSINESS_VALUE'];
		$checks = $fields['CASH_BOX_CHECKS'] ?? [];

		$availableFields = $this->getFieldsInfo();

		/** @var ISettingsExport $settings */
		$settings = $this->getSettings();

		foreach ($availableFields as $k=>$v)
		{
			$value='';
			switch ($k)
			{
				case 'ID':
					$value = ($traits['ID_1C']<>'' ? $traits['ID_1C']:$traits['ID']);
					break;
				case 'NUMBER':
					$value = $traits['ID'];
					break;
				case 'TIME':
				case 'DATE':
					$value = $traits['DATE_BILL'];
					break;
				case 'OPERATION':
					$value = DocumentBase::resolveDocumentTypeName($this->getDocmentTypeId());
					break;
				case 'ROLE':
					$value = DocumentBase::getLangByCodeField('SELLER');
					break;
				case 'CURRENCY':
					$replaceCurrency = $settings->getReplaceCurrency();
					$value = mb_substr($replaceCurrency <> ''? $replaceCurrency : $traits[$k], 0, 3);
					break;
				case 'CURRENCY_RATE':
					$value = self::CURRENCY_RATE_DEFAULT;
					break;
				case 'AMOUNT':
					$value = $traits['SUM'];
					break;
				case 'VERSION':
					$value = $traits['VERSION'];
					break;
				case 'NUMBER_BASE':// ?????
					$value = $traits['ORDER_ID'];
					break;
				case 'COMMENT':
					$value = $traits['COMMENTS'];
					break;
				case 'CASH_BOX_CHECKS':
					if (!empty($checks))
					{
						$value = $this->externalizeCashBoxChecksFields(current($checks), $v);
					}
					break;
				case 'REK_VALUES':
					$value=array();
					foreach($v['FIELDS'] as $name=>$fieldInfo)
					{
						$valueRV='';
						switch($name)
						{
							case '1C_PAYED_DATE':
								$valueRV = $traits['DATE_PAID'];
								break;
							case '1C_PAYED_NUM':
								$valueRV = $traits['PAY_VOUCHER_NUM'];
								break;
							case 'CANCEL':
								$valueRV = 'N';
								break;
							case 'PAY_SYSTEM_ID':
								$valueRV = $traits['PAY_SYSTEM_ID'];
								break;
							case 'PAY_SYSTEM':
								$valueRV = $traits['PAY_SYSTEM_NAME'];
								break;
							case 'PAY_PAID':
								$valueRV = $traits['PAID'];
								break;
							case 'PAY_RETURN':
								$valueRV = $traits['IS_RETURN'];
								break;
							case 'PAY_RETURN_REASON':
								$valueRV = $traits['PAY_RETURN_COMMENT'];
								break;
							case 'SITE_NAME':
								$valueRV = '['.$traits['LID'].'] '.static::getSiteNameByLid($traits['LID']);
								break;
							case 'REKV':
								$value = array_merge($value, $this->externalizeRekv($businessValue[$name], $fieldInfo));
								break;
						}
						if(!in_array($name, array('REKV')))
						{
							$value[] = $this->externalizeRekvValue($name, $valueRV, $fieldInfo);
						}
					}
					break;
			}
			if(!in_array($k, array('REK_VALUES', 'CASH_BOX_CHECKS')))
			{
				$this->externalizeField($value, $v);
			}

			$result[$k] = $value;
		}
		$result = $this->modifyTrim($result);
		return $result;
	}

	/**
	 * @param $fields
	 * @param array $fieldsInfo
	 * @return array
	 */
	protected function externalizeCashBoxChecksFields($fields, array $fieldsInfo)
	{
		$result = array();
		foreach($fieldsInfo['FIELDS'] as $name=>$fieldInfo)
		{
			$value='';
			switch($name)
			{
				case 'ID':
					$value = $fields['ID'];
					$this->externalizeField($value, $fieldInfo);
					$result[$name] = $value;
					break;
				case 'PROP_VALUES':
					foreach ($fieldInfo['FIELDS'] as $nameProp=>$fieldInfoProp)
					{
						switch ($nameProp)
						{
							case 'ID':
								$value = 'PRINT_CHECK';
								break;
							case 'VALUE':
								$value = 'Y';
								break;
						}
						$this->externalizeField($value, $fieldInfoProp);
						$result[$name][$nameProp] = $value;
					}
					break;
			}
		}

		return $result;
	}
}