<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ScalarField;

class RouteTable extends Entity\DataManager
{

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_urlpreview_route';
	}

	/**
	 * Returns entity map definition
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'ROUTE' => new Entity\StringField('ROUTE', array(
				'required' => true,
				'unique' => true
			)),
			'MODULE' => new Entity\StringField('MODULE', array(
				'required' => true,
			)),
			'CLASS' => new Entity\StringField('CLASS', array(
				'required' => true,
			)),
			'PARAMETERS' => new Entity\TextField('PARAMETERS', array(
				'serialized' => true,
			)),
		);
	}

	/**
	 * Returns first record filtered by $route value
	 *
	 * @param string $route Route template.
	 * @return array|false
	 * @throws Main\ArgumentException
	 */
	public static function getByRoute($route)
	{
		$parameters = array(
			'select' => array('*'),
			'filter' => array(
				'=ROUTE' => $route,
			)
		);

		return static::getList($parameters)->fetch();
	}

	/**
	 * Adds route to route table. If route record already exists, updates it.
	 *
	 * @param array $data Record to be merged to the table.
	 * @return AddResult
	 */
	public static function merge(array $data)
	{
		$result = new AddResult();

		try
		{
			// set fields with default values
			foreach (static::getEntity()->getFields() as $field)
			{
				if ($field instanceof ScalarField && !array_key_exists($field->getName(), $data))
				{
					$defaultValue = $field->getDefaultValue();

					if ($defaultValue !== null)
					{
						$data[$field->getName()] = $field->getDefaultValue();
					}
				}
			}

			static::checkFields($result, null, $data);

			// use save modifiers
			foreach ($data as $fieldName => $value)
			{
				$field = static::getEntity()->getField($fieldName);
				$data[$fieldName] = $field->modifyValueBeforeSave($value, $data);
			}

			$helper = Application::getConnection()->getSqlHelper();
			$insertData = $data;
			$updateData = $data;
			unset($updateData['ROUTE']);
			$merge = $helper->prepareMerge(
				static::getTableName(),
				array("ROUTE"),
				$insertData,
				$updateData
			);

			if ($merge[0] != "")
			{
				Application::getConnection()->query($merge[0]);
				$id = Application::getConnection()->getInsertedId();
				if($id == 0)
				{
					$updatedRecord = static::getByRoute($data['ROUTE']);
					$id = $updatedRecord['ID'];
				}
				$result->setId($id);
				$result->setData($data);
			}
			else
			{
				$result->addError(new Main\Error('Error constructing query'));
			}
		}
		catch (\Exception $e)
		{
			// check result to avoid warning
			$result->isSuccess();

			throw $e;
		}

		return $result;
	}
}