<?php
namespace Bitrix\Rest\Api;

use Bitrix\Rest\RestException;
use Bitrix\Rest\UserFieldProxy;

class UserField extends UserFieldProxy
{
	private static $nameFullPrefix = 'UF_USR_';
	private const ENTITY_ID = 'USER';
	private const ALLOWED_FIELD_PROP_LIST = [
		'FIELD_NAME',
		'USER_TYPE_ID',
		'XML_ID',
		'MULTIPLE',
		'SHOW_FILTER',
		'SORT',
		'LABEL',
		'LIST_FILTER_LABEL',
		'LIST_COLUMN_LABEL',
		'EDIT_FORM_LABEL',
		'ERROR_MESSAGE',
		'HELP_MESSAGE',
		'SETTINGS',
		'LIST',
	];
	public const SCOPE_USER_USERFIELD = 'user.userfield';
	protected $namePrefix = 'USR';

	public static function getTargetEntityId()
	{
		return static::ENTITY_ID;
	}

	public static function addRest($query, $n, \CRestServer $server)
	{
		$fields = [];

		$query = array_change_key_case($query, CASE_UPPER);
		if (isset($query['FIELDS']) && is_array($query['FIELDS']))
		{
			$fields = static::checkFields($query['FIELDS']);
		}

		$instance = new static(static::getTargetEntityId());

		return $instance->add($fields);
	}

	public static function updateRest($query, $n, \CRestServer $server)
	{
		$query = array_change_key_case($query, CASE_UPPER);
		$id = (int)($query['ID'] ?? 0);
		if ($id <= 0)
		{
			throw new RestException('ID is not defined or invalid.');
		}

		if (!static::checkAccessField($id))
		{
			throw new RestException('Access denied.');
		}

		$fields = [];
		if (isset($query['FIELDS']) && is_array($query['FIELDS']))
		{
			$fields = static::checkFields($query['FIELDS']);
		}

		$instance = new static(static::getTargetEntityId());

		return $instance->update($id, $fields);
	}

	public static function deleteRest($query, $n, \CRestServer $server)
	{
		$query = array_change_key_case($query, CASE_UPPER);
		$id = (int)($query['ID'] ?? 0);
		if ($id <= 0)
		{
			throw new RestException('ID is not defined or invalid.');
		}

		if (!static::checkAccessField($id))
		{
			throw new RestException('Access denied.');
		}

		$instance = new static(static::getTargetEntityId());

		return $instance->delete($id);
	}

	public static function getListRest($query, $n, \CRestServer $server)
	{
		$order = [];
		$filter = [];
		$query = array_change_key_case($query, CASE_UPPER);
		if (isset($query['ORDER']) && is_array($query['ORDER']))
		{
			$order = $query['ORDER'];
		}
		if (isset($query['FILTER']) && is_array($query['FILTER']))
		{
			$filter = $query['FILTER'];
		}

		$instance = new static(static::getTargetEntityId());
		$result = $instance->getList($order, $filter);

		if (is_array($result))
		{
			unset($result['total']);
			foreach ($result as $key => $item)
			{
				if (mb_strpos($item['FIELD_NAME'], static::$nameFullPrefix) !== 0)
				{
					unset($result[$key]);
				}
			}
			$result = array_values($result);
			$result['total'] = count($result);
		}

		return $result;
	}

	private static function checkFields(array $fields) : array
	{
		return array_intersect_key($fields, array_fill_keys(self::ALLOWED_FIELD_PROP_LIST, true));
	}

	private static function checkAccessField($fieldId)
	{
		$result = false;
		if ($fieldId > 0)
		{
			$entity = new \CUserTypeEntity();
			$res = $entity->getList(
				[],
				[
					'ENTITY_ID' => static::getTargetEntityId(),
					'ID' => $fieldId
				]
			);

			if ($field = $res->fetch())
			{
				if (mb_strpos($field['FIELD_NAME'], static::$nameFullPrefix) === 0)
				{
					$result = true;
				}
			}
		}

		return $result;
	}
}