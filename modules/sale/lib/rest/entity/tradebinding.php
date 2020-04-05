<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class TradeBinding extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'EXTERNAL_ORDER_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'TRADING_PLATFORM_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'PARAMS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'TRADING_PLATFORM_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
		];
	}

	protected function getRewritedFields()
	{
		return [
			'TRADING_PLATFORM_XML_ID'=>[
				'REFERENCE_FIELD'=>'TRADING_PLATFORM.XML_ID'
			]
		];
	}

	public function checkRequiredFieldsModify($fields)
	{
		$r = new Result();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID', 'EXTERNAL_ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

		foreach ($fields['ORDER']['TRADE_BINDINGS'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[tradeBindings]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}
		}
		return $r;
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], ['ignoredFields'=>['ORDER_ID', 'EXTERNAL_ORDER_ID']]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		if(isset($fields['ORDER']['TRADE_BINDINGS']))
		{
			foreach ($fields['ORDER']['TRADE_BINDINGS'] as $k=>$item)
			{
				$result['ORDER']['TRADE_BINDINGS'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);
			}
		}

		return $result;
	}
}