<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class BusinessValuePersonDomain extends Base
{

	public function getFields()
	{
		return [
			'PERSON_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'DOMAIN'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			]
		];
	}
}