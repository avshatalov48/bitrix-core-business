<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector;

use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\DB\Result as DbResult;
use Bitrix\Sender\Recipient;

class Result
{
	/** @var \Bitrix\Main\DB\Result $resource */
	public $resource;

	/** @var \CAllDBResult $resourceCDBResult */
	public $resourceCDBResult;

	/** fields filter */
	protected $fields;

	/** additional fields */
	protected $additionalFields;

	/** disallowed fields */
	protected $fieldsDisallowed = ['NAME', 'USER_ID'];

	/** @var  integer $dataTypeId Data type ID. */
	protected $dataTypeId = Recipient\Type::EMAIL;

	/**
	 * @param array|\Bitrix\Main\DB\Result|\CAllDBResult $resource
	 */
	public function __construct($resource)
	{
		if(is_array($resource))
		{
			$isSingleArray = false;
			$arrayKeyList = array_keys($resource);
			foreach($arrayKeyList as $key)
			{
				if(is_string($key))
				{
					$isSingleArray = true;
					break;
				}
			}

			if($isSingleArray)
			{
				$resource = array($resource);
			}

			$this->resource = new ArrayResult($resource);
		}
		elseif($resource instanceof DbResult)
		{
			$this->resource = $resource;
		}
		elseif($resource instanceof \CAllDBResult)
		{
			$this->resourceCDBResult = $resource;
		}

		$this->fieldsDisallowed = array_merge(
			$this->fieldsDisallowed,
			Recipient\Type::getCodes()
		);
	}

	/**
	 * Get data type ID.
	 *
	 * @return integer
	 */
	public function getDataTypeId()
	{
		return $this->dataTypeId;
	}

	/**
	 * Set data type ID.
	 *
	 * @param integer $dataTypeId Data type ID.
	 * @return void
	 */
	public function setDataTypeId($dataTypeId)
	{
		$this->dataTypeId = $dataTypeId;
	}

	/**
	 * @param array $fields
	 */
	public function setFilterFields(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * @return array
	 */
	public function getFilterFields()
	{
		return $this->fields;
	}

	/**
	 * @param array $additionalFields
	 */
	public function setAdditionalFields(array $additionalFields)
	{
		$this->additionalFields = $additionalFields;
	}

	/**
	 * @return array|null
	 */
	public function fetchPlain()
	{
		$result = null;
		if($this->resource)
		{
			$result = $this->resource->fetch();
		}
		elseif($this->resourceCDBResult)
		{
			$result = $this->resourceCDBResult->Fetch();
		}

		return (is_array($result) && count($result) > 0) ? $result : null;
	}

	/**
	 * @return array|null
	 */
	public function fetch()
	{
		$result = $this->fetchPlain();
		if($result)
		{
			$result = $this->fetchModifierFields($result);
		}

		return ($result && count($result) > 0) ? $result : null;
	}

	protected function fetchModifierFields(array $result)
	{
		$fieldsList = array();
		foreach($result as $key => $value)
		{
			if(is_object($value))
			{
				$value = (string) $value;
				$result[$key] = $value;
			}

			if(in_array($key, $this->fieldsDisallowed))
			{
				continue;
			}

			if($this->fields && in_array($key, $this->fields))
			{
				$fieldsList[$key] = $value;
			}

			unset($result[$key]);
		}

		if($this->additionalFields)
		{
			$fieldsList = $fieldsList + $this->additionalFields;
		}

		if(count($fieldsList) > 0)
		{
			$result['FIELDS'] = $fieldsList;
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function getSelectedRowsCount()
	{
		if($this->resource)
		{
			return $this->resource->getSelectedRowsCount();
		}
		elseif($this->resourceCDBResult)
		{
			if(!($this->resourceCDBResult instanceof \CDBResultMysql))
			{
				$this->resourceCDBResult->NavStart(0);
			}

			return $this->resourceCDBResult->SelectedRowsCount();
		}

		return 0;
	}

	/**
	 * Get query sql.
	 *
	 * @return string|null
	 */
	public function getQuerySql()
	{
		if (!$this->resource)
		{
			return null;
		}

		if (!$this->resource->getTrackerQuery())
		{
			return null;
		}

		return $this->resource->getTrackerQuery()->getSql();
	}
}