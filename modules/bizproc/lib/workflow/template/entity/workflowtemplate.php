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
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'MODULE_ID' => array(
				'data_type' => 'string'
			),
			'ENTITY' => array(
				'data_type' => 'string'
			),
			'DOCUMENT_TYPE' => array(
				'data_type' => 'string'
			),
			'DOCUMENT_STATUS' => array(
				'data_type' => 'string'
			),
			'AUTO_EXECUTE' => array(
				'data_type' => 'integer'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'TEMPLATE' => (new Main\ORM\Fields\ArrayField('TEMPLATE'))
				->configureUnserializeCallback([__CLASS__, "getFromSerializedForm"]),
			'PARAMETERS' => (new Main\ORM\Fields\ArrayField('PARAMETERS'))
				->configureUnserializeCallback([__CLASS__, "getFromSerializedForm"]),
			'VARIABLES' =>  (new Main\ORM\Fields\ArrayField('VARIABLES'))
				->configureUnserializeCallback([__CLASS__, "getFromSerializedForm"]),
			'CONSTANTS' => (new Main\ORM\Fields\ArrayField('CONSTANTS'))
				->configureUnserializeCallback([__CLASS__, "getFromSerializedForm"]),
			'MODIFIED' => array(
				'data_type' => 'datetime'
			),
			'IS_MODIFIED' => array(
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
			),
			'USER_ID' => array(
				'data_type' => 'integer'
			),
			'SYSTEM_CODE' => array(
				'data_type' => 'string'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
			),
			'ORIGINATOR_ID' => array(
				'data_type' => 'string'
			),
			'ORIGIN_ID' => array(
				'data_type' => 'string'
			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.USER_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
			'IS_SYSTEM' => array(
				'data_type' => 'boolean',
				'values' => ['N', 'Y']
			),
			'SORT' => array(
				'data_type' => 'integer',
				'default_value' => 10
			),
		);
	}

	public static function getFromSerializedForm($value)
	{
		static $useCompression;
		if ($useCompression === null)
		{
			$useCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
		}

		if ($value <> '')
		{
			if ($useCompression)
			{
				$value1 = @gzuncompress($value);
				if ($value1 !== false)
					$value = $value1;
			}

			$value = unserialize($value);
			if (!is_array($value))
				$value = array();
		}
		else
		{
			$value = array();
		}
		return $value;
	}

	public static function getIdsByDocument(array $documentType): array
	{
		$documentType = \CBPHelper::ParseDocumentId($documentType);
		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentType[0],
				'=ENTITY' => $documentType[1],
				'=DOCUMENT_TYPE' => $documentType[2]
			]
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	/** @inheritdoc */
	public static function add(array $data)
	{
		throw new Main\NotImplementedException("Use CBPTemplateLoader class.");
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
}