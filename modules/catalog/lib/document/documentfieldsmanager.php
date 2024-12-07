<?php

namespace Bitrix\Catalog\Document;

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;

class DocumentFieldsManager
{
	public static function getRequiredFields(string $docType): array
	{
		try
		{
			$requiredFields = Json::decode(Option::get('catalog', 'store_document_required_fields_' . $docType)) ?: [];
		}
		catch (\Exception $e)
		{
			$requiredFields = [];
		}

		return $requiredFields;
	}

	public static function addRequiredField(string $docType, string $fieldName): Result
	{
		$result = new Result();
		$allowedFields = self::getAllowedRequiredSettingFieldsByDocumentId($docType);
		if (!in_array($fieldName, $allowedFields, true))
		{
			$result->addError(new Error('Field "' . $fieldName . '" is not available'));

			return $result;
		}

		$requiredFields = self::getRequiredFields($docType);
		if (in_array($fieldName, $requiredFields, true))
		{
			$result->addError(new Error('Field "' . $fieldName . '" already required'));

			return $result;
		}

		$requiredFields[] = $fieldName;
		self::saveRequiredFields($docType, $requiredFields);

		return $result;
	}

	public static function deleteRequiredField(string $docType, string $fieldName): Result
	{
		$result = new Result();

		$requiredFields = self::getRequiredFields($docType);
		$fieldIndex = array_search($fieldName, $requiredFields, true);
		if ($fieldIndex === false)
		{
			$result->addError(new Error('Field "' . $fieldName . '" already not required'));

			return $result;
		}

		unset($requiredFields[$fieldIndex]);
		$requiredFields = array_values($requiredFields);
		self::saveRequiredFields($docType, $requiredFields);

		return $result;
	}

	private static function saveRequiredFields(string $docType, array $requiredFields): void
	{
		Option::set('catalog', 'store_document_required_fields_' . $docType, Json::encode($requiredFields));
	}

	private static function getAllowedRequiredSettingFieldsByDocumentId(string $documentType): array
	{
		return match ($documentType) {
			StoreDocumentTable::TYPE_ARRIVAL => [
				'TITLE',
				'DOC_NUMBER',
				'DATE_DOCUMENT',
				'ITEMS_ORDER_DATE',
				'ITEMS_RECEIVED_DATE',
				'DOCUMENT_FILES'
			],
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT => ['TITLE'],
			StoreDocumentTable::TYPE_DEDUCT,
			StoreDocumentTable::TYPE_MOVING => [
				'TITLE',
				'DOC_NUMBER',
				'DATE_DOCUMENT'
			],
			default => [],
		};
	}
}
