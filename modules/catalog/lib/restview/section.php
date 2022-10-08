<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Section extends Base
{

	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'DESCRIPTION'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'DESCRIPTION_TYPE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'IBLOCK_SECTION_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'IBLOCK_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
		];
	}

	public function checkFieldsList($arguments): Result
	{
		$r = new Result();

		$error=[];

		if(count($error)>0)
			$r->addError(new Error('Required select fields: '.implode(', ', $error)));

		if(!isset($arguments['filter']['IBLOCK_ID']))
			$r->addError(new Error('Required filter fields: iblockId'));

		return $r;
	}
}