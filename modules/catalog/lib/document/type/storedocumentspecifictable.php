<?php

namespace Bitrix\Catalog\Document\Type;

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;

/*
 * This class' children are meant to be used to work with a specific document type.
 * Each document type has its own user fields set, so they need to have their own separate ORM classes.
 * If you need to select all documents/a set of documents regardless of their type,
 * use \Bitrix\Catalog\StoreDocumentTable; however, do note that it doesn't support user fields.
 */
abstract class StoreDocumentSpecificTable extends StoreDocumentTable
{
	abstract public static function getType(): string;

	public static function getUfId()
	{
		return 'CAT_STORE_DOCUMENT_' . static::getType();
	}

	public static function setDefaultScope($query)
	{
		$query->where('DOC_TYPE', static::getType());
	}

	public static function add(array $data)
	{
		$data['DOC_TYPE'] = static::getType();
		return parent::add($data);
	}

	public static function update($primary, array $data)
	{
		$result = new UpdateResult();

		$documentType = self::getByPrimary($primary, ['select' => ['DOC_TYPE']])->fetch();
		if (!$documentType)
		{
			$result->addError(new Error(Loc::getMessage('STORE_DOCUMENT_SPECIFIC_TABLE_DOC_NOT_FOUND_ERROR')));

			return $result;
		}

		if ($documentType['DOC_TYPE'] !== static::getType())
		{
			$result->addError(new Error(Loc::getMessage('STORE_DOCUMENT_SPECIFIC_TABLE_WRONG_DOC_TYPE_ERROR')));

			return $result;
		}

		if (isset($data['DOC_TYPE']))
		{
			unset($data['DOC_TYPE']);
		}

		return parent::update($primary, $data);
	}

	public static function delete($primary)
	{
		$result = new DeleteResult();

		$documentType = self::getByPrimary($primary, ['select' => ['DOC_TYPE']])->fetch();
		if (!$documentType)
		{
			$result->addError(new Error(Loc::getMessage('STORE_DOCUMENT_SPECIFIC_TABLE_DOC_NOT_FOUND_ERROR')));

			return $result;
		}

		if ($documentType['DOC_TYPE'] !== static::getType())
		{
			$result->addError(new Error(Loc::getMessage('STORE_DOCUMENT_SPECIFIC_TABLE_WRONG_DOC_TYPE_ERROR')));

			return $result;
		}

		return parent::delete($primary);
	}
}
