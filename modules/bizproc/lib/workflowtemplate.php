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
				'data_type' => 'string'
			),
			'PARAMETERS' => array(
				'data_type' => 'string'
			),
			'VARIABLES' => array(
				'data_type' => 'string'
			),
			'CONSTANTS' => array(
				'data_type' => 'string'
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
