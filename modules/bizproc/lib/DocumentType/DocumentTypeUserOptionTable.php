<?php

namespace Bitrix\Bizproc\DocumentType;

use Bitrix\Main\Error;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use CBPHelper;

/**
 * Class DocumentTypeUserOptionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DocumentTypeUserOption_Query query()
 * @method static EO_DocumentTypeUserOption_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DocumentTypeUserOption_Result getById($id)
 * @method static EO_DocumentTypeUserOption_Result getList(array $parameters = [])
 * @method static EO_DocumentTypeUserOption_Entity getEntity()
 * @method static \Bitrix\Bizproc\DocumentType\EO_DocumentTypeUserOption createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\DocumentType\EO_DocumentTypeUserOption_Collection createCollection()
 * @method static \Bitrix\Bizproc\DocumentType\EO_DocumentTypeUserOption wakeUpObject($row)
 * @method static \Bitrix\Bizproc\DocumentType\EO_DocumentTypeUserOption_Collection wakeUpCollection($rows)
 */
class DocumentTypeUserOptionTable extends DataManager
{
	public const PINNED = 1;

	public const PARAMETER_ERROR = 1;
	public const ADDING_ERROR = 2;
	public const DELETING_ERROR = 3;

	public static function getTableName(): string
	{
		return 'b_bp_document_type_user_option';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new ORM\Fields\StringField('MODULE_ID'))
				->configureRequired()
			,
			(new ORM\Fields\StringField('ENTITY'))
				->configureRequired()
			,
			(new ORM\Fields\StringField('DOCUMENT_TYPE'))
				->configureRequired()
			,
			(new ORM\Fields\IntegerField('USER_ID'))
				->configureRequired()
			,
			(new ORM\Fields\IntegerField('OPTION_CODE'))
				->configureRequired()
			,
		];
	}

	public static function isOption(int $option): bool
	{
		return in_array($option, [self::PINNED], true);
	}

	public static function addOption(array $documentType, int $userId, int $option): Result
	{
		$addResult = new Result();
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($documentType);

		if (empty($moduleId) || empty($entity) || empty($documentType) || $userId <= 0 || !static::isOption($option))
		{
			$addResult->addError(new Error('Some parameter is wrong.', self::PARAMETER_ERROR));
			return $addResult;
		}

		$data = [
			'MODULE_ID' => $moduleId,
			'ENTITY' => $entity,
			'DOCUMENT_TYPE' => $documentType,
			'USER_ID' => $userId,
			'OPTION_CODE' => $option,
		];

		$item = self::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $moduleId,
				'=ENTITY' => $entity,
				'=DOCUMENT_TYPE' => $documentType,
				'=USER_ID' => $userId,
				'=OPTION_CODE' => $option,
			],
		])->fetch();

		if (!$item)
		{
			$tableAddResult = self::add($data);
			if (!$tableAddResult->isSuccess())
			{
				$addResult->addError(new Error('Adding to table failed.', self::ADDING_ERROR));

				return $addResult;
			}

			return $addResult;
		}

		return $addResult;
	}

	public static function deleteOption(array $documentType, int $userId, int $option): Result
	{
		$deleteResult = new Result();

		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($documentType);

		if (empty($moduleId) || empty($entity) || empty($documentType) || $userId <= 0 || !static::isOption($option))
		{
			$deleteResult->addError(new Error('Some parameter is wrong.', self::PARAMETER_ERROR));

			return $deleteResult;
		}

		$item = self::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $moduleId,
				'=ENTITY' => $entity,
				'=DOCUMENT_TYPE' => $documentType,
				'=USER_ID' => $userId,
				'=OPTION_CODE' => $option,
			],
		])->fetch();

		if ($item)
		{
			$tableDeleteResult = self::delete($item);
			if (!$tableDeleteResult->isSuccess())
			{
				$deleteResult->addError(new Error('Deleting from table failed.', self::DELETING_ERROR));

				return $deleteResult;
			}
		}

		return $deleteResult;
	}
}
