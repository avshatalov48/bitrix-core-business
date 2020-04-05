<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class PersonType extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'LID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'CODE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'ACTIVE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			]
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		if(isset($fields['PERSON_TYPE']))
		{
			$result['PERSON_TYPE'] = $this->internalizeFields($fields['PERSON_TYPE'], $fieldsInfo);
		}

		return $result;
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeListFields($fields);
	}
}