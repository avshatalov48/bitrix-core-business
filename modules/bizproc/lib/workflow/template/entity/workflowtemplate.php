<?php

namespace Bitrix\Bizproc\Workflow\Template\Entity;

use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Main;

class WorkflowTemplateTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_workflow_template';
	}

	public static function getObjectClass()
	{
		return Tpl::class;
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		$serializeCallback = [__CLASS__, 'toSerializedForm'];
		$unserializeCallback = [__CLASS__, 'getFromSerializedForm'];

		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'MODULE_ID' => [
				'data_type' => 'string',
			],
			'ENTITY' => [
				'data_type' => 'string',
			],
			'DOCUMENT_TYPE' => [
				'data_type' => 'string',
			],
			'DOCUMENT_STATUS' => [
				'data_type' => 'string',
			],
			'AUTO_EXECUTE' => [
				'data_type' => 'integer',
			],
			'NAME' => [
				'data_type' => 'string',
			],
			'DESCRIPTION' => [
				'data_type' => 'string',
			],
			'TEMPLATE' => (
				(new Main\ORM\Fields\ArrayField('TEMPLATE'))
					->configureSerializeCallback($serializeCallback)
					->configureUnserializeCallback($unserializeCallback)
			),
			'PARAMETERS' => (
				(new Main\ORM\Fields\ArrayField('PARAMETERS'))
					->configureSerializeCallback($serializeCallback)
					->configureUnserializeCallback($unserializeCallback)
			),
			'VARIABLES' => (
				(new Main\ORM\Fields\ArrayField('VARIABLES'))
					->configureSerializeCallback($serializeCallback)
					->configureUnserializeCallback($unserializeCallback)
			),
			'CONSTANTS' => (
				(new Main\ORM\Fields\ArrayField('CONSTANTS'))
					->configureSerializeCallback($serializeCallback)
					->configureUnserializeCallback($unserializeCallback)
			),
			'MODIFIED' => [
				'data_type' => 'datetime',
			],
			'IS_MODIFIED' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'USER_ID' => [
				'data_type' => 'integer',
			],
			'SYSTEM_CODE' => [
				'data_type' => 'string',
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'ORIGINATOR_ID' => [
				'data_type' => 'string',
			],
			'ORIGIN_ID' => [
				'data_type' => 'string',
			],
			'USER' => [
				'data_type' => Main\UserTable::class,
				'reference' => [
					'=this.USER_ID' => 'ref.ID',
				],
				'join_type' => 'LEFT',
			],
			'IS_SYSTEM' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'SORT' => [
				'data_type' => 'integer',
				'default_value' => 10,
			],
		];
	}

	public static function getFromSerializedForm($value)
	{
		if (!empty($value))
		{
			if (self::shouldUseCompression())
			{
				$value1 = @gzuncompress($value);
				if ($value1 !== false)
				{
					$value = $value1;
				}
			}

			$value = unserialize($value, ['allowed_classes' => false]);
			if (!is_array($value))
			{
				$value = [];
			}
		}
		else
		{
			$value = [];
		}

		return $value;
	}

	public static function toSerializedForm($value)
	{
		if (empty($value))
		{
			return null;
		}

		$buffer = serialize($value);
		if (self::shouldUseCompression())
		{
			$buffer = gzcompress($buffer, 9);
		}

		return $buffer;
	}

	public static function getIdsByDocument(array $documentType): array
	{
		$documentType = \CBPHelper::ParseDocumentId($documentType);
		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentType[0],
				'=ENTITY' => $documentType[1],
				'=DOCUMENT_TYPE' => $documentType[2],
			],
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	/** @inheritdoc */
	public static function update($primary, array $data)
	{
		throw new Main\NotImplementedException("Use CBPTemplateLoader class.");
	}

	/** @inheritdoc */
	public static function delete($primary)
	{
		throw new Main\NotImplementedException("Use CBPTemplateLoader class.");
	}

	private static function shouldUseCompression(): bool
	{
		static $useCompression;
		if ($useCompression === null)
		{
			$useCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
		}

		return $useCompression;
	}
}