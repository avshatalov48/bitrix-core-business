<?php


namespace Bitrix\Sale\Exchange;

use Bitrix\Main\Error;
use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;
use Bitrix\Sale\Exchange\OneC;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/exchange/importonecpackage.php');

class ImportOneCContragentBase extends ImportOneCBase
{
	protected function resolveDocumentTypeId(array $fields)
	{
		return OneC\DocumentType::USER_PROFILE;
	}

	/**
	 * @param OneC\DocumentBase[] $documents
	 * @return Result
	 */
	protected function convert(array $documents)
	{
		$result = new Result();
		$list = array();

		foreach($documents as $document)
		{
			$list[] = $this->convertDocument($document);
		}

		if($result->isSuccess())
		{
			$result = $this->checkFields($list);
			if($result->isSuccess())
			{
				$result->setData($list);
			}
		}

		return $result;
	}

	/**
	 * @param UserImportBase[] $items
	 * @return mixed
	 */
	protected function import(array $items)
	{
		$result = new Result();

		foreach($items as $item)
		{
			if($item->getOwnerTypeId() == static::getUserProfileEntityTypeId())
			{
				$params = $item->getFieldValues();
				$fields = $params['TRAITS'];

				$personalTypeId = $params['TRAITS']['PERSON_TYPE_ID'] = $item->resolvePersonTypeId($fields);

				$property = $params['ORDER_PROPS'];
				if(!empty($property))
				{
					$params['ORDER_PROP'] = $item->getPropertyOrdersByConfig($personalTypeId, array(), $property);
				}

				unset($params['ORDER_PROPS']);
				$item->setFields($params);

				$r = $item->load($fields);

				if(intval($personalTypeId)<=0)
					$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_PERSONAL_TYPE_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_PERSONAL_TYPE_IS_EPMTY"));

				if($r->isSuccess())
				{
					$r = $this->modifyEntity($item);

					if(intval($item->getId())<=0)
						$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_USER_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_USER_IS_EPMTY"));
				}

				if(!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					break;
				}
			}
		}

		return $result;
	}

	public static function configuration()
	{
		parent::configuration();

		ManagerImport::registerInstance(static::getUserProfileEntityTypeId(), OneC\ImportSettings::getCurrent());
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function logger(array $items)
	{
		$xmlStreem = $this->getRawData();
		foreach ($items as $item)
		{
			if($item->hasLogging())
			{
				$item->getLogger()
					->setField('MESSAGE', $xmlStreem);
			}
		}
		return parent::logger($items);
	}

	protected function resolveOwnerEntityTypeId($typeId)
	{
		$entityTypeId = EntityType::UNDEFINED;

		switch ($typeId)
		{
			case OneC\DocumentType::USER_PROFILE:
				$entityTypeId = static::getUserProfileEntityTypeId();
				break;
		}
		return $entityTypeId;
	}

	static function getUserProfileEntityTypeId()
	{
		return EntityType::UNDEFINED;
	}
}