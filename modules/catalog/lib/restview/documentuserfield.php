<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Catalog\Document\StoreDocumentTableManager;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class DocumentUserField extends Base
{
	public function getFields()
	{
		return [
			'DOCUMENT_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'CANONICAL_NAME' => 'ID',
			],
			'DOCUMENT_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
				'CANONICAL_NAME' => 'DOC_TYPE',
			],
		];
	}

	public function internalizeFieldsUpdate($fields, $fieldsInfo = []): array
	{
		$documentType = $fields['DOCUMENT_TYPE'];
		$fieldsInfo = array_merge($fieldsInfo, $this->getFields(), $this->getFieldMapForType($documentType));

		return parent::internalizeFieldsUpdate($fields, $fieldsInfo);
	}

	public function internalizeFieldsList($arguments, $fieldsInfo = []): array
	{
		$documentType = $arguments['filter']['DOCUMENT_TYPE'];
		$fieldsInfo = array_merge($fieldsInfo, $this->getFields(), $this->getFieldMapForType($documentType));

		return parent::internalizeFieldsList($arguments, $fieldsInfo);
	}

	protected function internalizeExtendedTypeValue($value, $info): Result
	{
		$isDynamic = in_array(Attributes::DYNAMIC, $info['ATTRIBUTES'], true);
		$isMultiple = in_array(Attributes::MULTIPLE, $info['ATTRIBUTES'], true);
		if (empty($value) && $isDynamic && $isMultiple)
		{
			$value = [''];
		}

		if ($info['USER_FIELD_TYPE'] === 'file')
		{
			if ($isMultiple)
			{
				$internalizedValue = [];
				foreach ($value as $item)
				{
					$internalizedValue[] = $this->internalizeFileValue($item);
				}
				$value = $internalizedValue;
			}
			else
			{
				$value = $this->internalizeFileValue($value);
			}
		}

		return parent::internalizeExtendedTypeValue($value, $info);
	}

	public function externalizeFieldsGet($fields, $fieldsInfo = []): array
	{
		$documentType = $fields['DOC_TYPE'];
		$fieldsInfo = array_merge($fieldsInfo, $this->getFields(), $this->getFieldMapForType($documentType));

		return parent::externalizeFieldsGet($fields, $fieldsInfo);
	}

	public function externalizeListFields($list, $fieldsInfo = []): array
	{
		$documentType = $list[0]['DOC_TYPE'];
		$fieldsInfo = array_merge($fieldsInfo, $this->getFields(), $this->getFieldMapForType($documentType));

		return parent::externalizeListFields($list, $fieldsInfo);
	}

	private function getFieldMapForType($documentType)
	{
		global $USER_FIELD_MANAGER;

		$ufEntityId = StoreDocumentTableManager::getUfEntityIds()[$documentType] ?? '';
		if (!$ufEntityId)
		{
			return [];
		}

		$userFieldsDescriptions = $USER_FIELD_MANAGER->GetUserFields($ufEntityId, 0);

		$result = [];
		foreach ($userFieldsDescriptions as $userField)
		{
			$attributes = [Attributes::DYNAMIC];
			if ($userField['MULTIPLE'] === 'Y')
			{
				$attributes[] = Attributes::MULTIPLE;
			}
			$result['FIELD_' . $userField['ID']] = [
				'ATTRIBUTES' => $attributes,
				'CANONICAL_NAME' => $userField['FIELD_NAME'],
				'USER_FIELD_TYPE' => $userField['USER_TYPE_ID']
			];
		}

		return $result;
	}
}
