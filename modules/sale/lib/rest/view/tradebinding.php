<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class TradeBinding extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'ORDER_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'EXTERNAL_ORDER_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'TRADING_PLATFORM_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'PARAMS'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'TRADING_PLATFORM_XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
		];
	}

	protected function getRewriteFields(): array
	{
		return [
			'TRADING_PLATFORM_XML_ID'=>[
				'REFERENCE_FIELD'=>'TRADING_PLATFORM.XML_ID'
			]
		];
	}

	public function checkRequiredFieldsModify($fields): Result
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

	public function internalizeFieldsModify($fields, $fieldsInfo=[]): array
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY], ['ignoredFields'=>['ORDER_ID', 'EXTERNAL_ORDER_ID']]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE], 'skipFields'=>['ID']]]);

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