<?php

namespace Bitrix\Main\DB;

class MysqliResult extends Result
{
	/** @var \mysqli_result */
	protected $resource;

	/** @var \Bitrix\Main\ORM\Fields\ScalarField[]  */
	private $resultFields = null;

	/**
	 * @param resource $result Database-specific query result.
	 * @param Connection|null $dbConnection Connection object.
	 * @param \Bitrix\Main\Diag\SqlTrackerQuery|null $trackerQuery Helps to collect debug information.
	 */
	public function __construct($result, Connection $dbConnection = null, \Bitrix\Main\Diag\SqlTrackerQuery $trackerQuery = null)
	{
		parent::__construct($result, $dbConnection, $trackerQuery);
	}

	/**
	 * Returns the number of rows in the result.
	 *
	 * @return integer
	 */
	public function getSelectedRowsCount()
	{
		return $this->resource->num_rows;
	}

	/**
	 * Returns an array of fields according to columns in the result.
	 *
	 * @return \Bitrix\Main\ORM\Fields\ScalarField[]
	 */
	public function getFields()
	{
		if ($this->resultFields == null)
		{
			$this->resultFields = array();
			if (is_object($this->resource))
			{
				$fields = $this->resource->fetch_fields();
				if ($fields && $this->connection)
				{
					$helper = $this->connection->getSqlHelper();
					foreach ($fields as $field)
					{
						$this->resultFields[$field->name] = $helper->getFieldByColumnType($field->name ?: '(empty)', $field->type);
					}
				}
			}
		}

		return $this->resultFields;
	}

	/**
	 * Returns next result row or false.
	 *
	 * @return array|false
	 */
	protected function fetchRowInternal()
	{
		return $this->resource->fetch_assoc();
	}
}
