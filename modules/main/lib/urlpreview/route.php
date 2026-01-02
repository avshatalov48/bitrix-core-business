<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\Application;
use Bitrix\Main;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class RouteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Route_Query query()
 * @method static EO_Route_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Route_Result getById($id)
 * @method static EO_Route_Result getList(array $parameters = [])
 * @method static EO_Route_Entity getEntity()
 * @method static \Bitrix\Main\UrlPreview\EO_Route createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UrlPreview\EO_Route_Collection createCollection()
 * @method static \Bitrix\Main\UrlPreview\EO_Route wakeUpObject($row)
 * @method static \Bitrix\Main\UrlPreview\EO_Route_Collection wakeUpCollection($rows)
 */
class RouteTable extends DataManager
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
			'ID' => new Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'ROUTE' => new Fields\StringField('ROUTE', array(
				'required' => true,
				'unique' => true
			)),
			'MODULE' => new Fields\StringField('MODULE', array(
				'required' => true,
			)),
			'CLASS' => new Fields\StringField('CLASS', array(
				'required' => true,
			)),
			'PARAMETERS' => new Fields\TextField('PARAMETERS', array(
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
				if ($field instanceof Fields\ScalarField && !array_key_exists($field->getName(), $data))
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
