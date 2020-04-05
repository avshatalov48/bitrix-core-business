<?php

namespace Bitrix\Bizproc;

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
			'TEMPLATE' => array(
				'data_type' => 'string',
				'fetch_data_modification' => array(__CLASS__, "getFetchModificatorsForTemplateField"),
			),
			'PARAMETERS' => array(
				'data_type' => 'string',
				'fetch_data_modification' => array(__CLASS__, "getFetchModificatorsForParametersField"),
			),
			'VARIABLES' => array(
				'data_type' => 'string',
				'fetch_data_modification' => array(__CLASS__, "getFetchModificatorsForVariablesField"),
			),
			'CONSTANTS' => array(
				'data_type' => 'string',
				'fetch_data_modification' => array(__CLASS__, "getFetchModificatorsForConstantsField"),
			),
			'MODIFIED' => array(
				'data_type' => 'datetime'
			),
			'IS_MODIFIED' => array(
				'data_type' => 'boolean',
				'values' => array('Y', 'N')
			),
			'USER_ID' => array(
				'data_type' => 'integer'
			),
			'SYSTEM_CODE' => array(
				'data_type' => 'string'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('Y', 'N')
			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.USER_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getFetchModificatorsForTemplateField()
	{
		return array(
			array(__CLASS__, "getFromSerializedForm"),
		);
	}

	/**
	 * @return array
	 */
	public static function getFetchModificatorsForParametersField()
	{
		return array(
			array(__CLASS__, "getFromSerializedForm"),
		);
	}

	/**
	 * @return array
	 */
	public static function getFetchModificatorsForVariablesField()
	{
		return array(
			array(__CLASS__, "getFromSerializedForm"),
		);
	}

	/**
	 * @return array
	 */
	public static function getFetchModificatorsForConstantsField()
	{
		return array(
			array(__CLASS__, "getFromSerializedForm"),
		);
	}

	public static function getFromSerializedForm($value)
	{
		static $useCompression;
		if ($useCompression === null)
		{
			$useCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
		}

		if (strlen($value) > 0)
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