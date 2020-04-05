<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class Payment extends Base
{
	public function getFields()
	{
		return [
			'PAY_SYSTEM_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PAY_SYSTEM_IS_CASH'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ACCOUNT_NUMBER'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Immutable,
					Attributes::Required
				]
			],
			'PAID'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_PAID'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'EMP_PAID_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PAY_SYSTEM_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::Required]//for builder
			],
			'PS_STATUS'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'PS_STATUS_CODE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PS_STATUS_DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PS_STATUS_MESSAGE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PS_SUM'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'PS_CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PS_RESPONSE_DATE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'PAY_VOUCHER_NUM'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PAY_VOUCHER_DATE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'DATE_PAY_BEFORE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'DATE_BILL'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'SUM'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PAY_SYSTEM_NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'COMPANY_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PAY_RETURN_NUM'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PRICE_COD'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PAY_RETURN_DATE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'EMP_RETURN_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PAY_RETURN_COMMENT'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'EMP_RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DATE_RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'IS_RETURN'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'COMMENTS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'UPDATED_1C'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'ID_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'VERSION_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'EXTERNAL_PAYMENT'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'PS_INVOICE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'MARKED'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'REASON_MARKED'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DATE_MARKED'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_MARKED_ID'=>[
				'TYPE'=>self::TYPE_INT
			]
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		if(isset($fields['ORDER']['ID']))
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];

		if(isset($fields['ORDER']['PAYMENTS']))
		{
			foreach ($fields['ORDER']['PAYMENTS'] as $k=>$item)
			{
				$result['ORDER']['PAYMENTS'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);
			}
		}

		return $result;
	}

	protected function getRewritedFields()
	{
		return [
			'PAY_SYSTEM_IS_CASH'=>[
				'REFERENCE_FIELD'=>'PAY_SYSTEM.IS_CASH'
			],
			'PAY_SYSTEM_XML_ID'=>[
				'REFERENCE_FIELD'=>'PAY_SYSTEM.XML_ID'
			]
		];
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'getorderid'
			|| $name == 'getpaymentsystemid'
			|| $name == 'getpaymentsystemname'
			|| $name == 'getpersontypeid'
			|| $name == 'getsum'
			|| $name == 'getsumpaid'
			|| $name == 'isinner'
			|| $name == 'ismarked'
			|| $name == 'isnarked'
			|| $name == 'ispaid'
			|| $name == 'isreturn'
			|| $name == 'setpaid'
			|| $name == 'setreturn'
		){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeListFields($fields);
	}

	public function checkFieldsModify($fields)
	{
		$r = new Result();

		$emptyFields = [];
		if(!isset($fields['ORDER']['ID']))
		{
			$emptyFields[] = '[order][id]';
		}
		if(!isset($fields['ORDER']['PAYMENTS']) || !is_array($fields['ORDER']['PAYMENTS']))
		{
			$emptyFields[] = '[order][payments][]';
		}

		if(count($emptyFields)>0)
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}
		else
		{
			$r = parent::checkFieldsModify($fields);
		}

		return $r;
	}

	public function checkRequiredFieldsModify($fields)
	{
		$r = new Result();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

		foreach ($fields['ORDER']['PAYMENTS'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[payments]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}
		}
		return $r;
	}
}