<?php
namespace Bitrix\Main\DB;

class PgsqlResult extends Result
{
	/** @var \PgSql\Result */
	protected $resource;

	/** @var \Bitrix\Main\ORM\Fields\ScalarField[]  */
	private $resultFields = null;

	/**
	 * Returns the number of rows in the result.
	 *
	 * @return integer
	 */
	public function getSelectedRowsCount()
	{
		return pg_num_rows($this->resource);
	}

	/**
	 * Returns the number of fields in the result.
	 * 
	 * @return int
	 */
	public function getFieldsCount(): int
	{
		return pg_num_fields($this->resource);
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

			if (
				$this->connection
				&& (is_resource($this->resource) || is_object($this->resource))
			)
			{
				$fields = pg_num_fields($this->resource);
				if ($fields)
				{
					$helper = $this->connection->getSqlHelper();
					for ($i = 0; $i < $fields; $i++)
					{
						$fieldName = mb_strtoupper(pg_field_name($this->resource, $i));
						$fieldType = pg_field_type($this->resource, $i);
						$this->resultFields[$fieldName] = $helper->getFieldByColumnType($fieldName, $fieldType);
					}
				}
			}
		}

		return $this->resultFields;
	}

	protected $byteaFields = false;

	/**
	 * Returns next result row or false.
	 *
	 * @return array|false
	 */
	protected function fetchRowInternal()
	{
		$result = pg_fetch_assoc($this->resource);
		if ($result)
		{
			if ($this->byteaFields === false)
			{
				$this->byteaFields = [];
				$fieldNum = 0;
				foreach ($result as $fieldName => $_)
				{
					$fieldType = pg_field_type($this->resource, $fieldNum);
					if ($fieldType === 'bytea')
					{
						$this->byteaFields[$fieldName] = $fieldType;
					}
					$fieldNum++;
				}
			}

			if ($this->byteaFields)
			{
				foreach ($this->byteaFields as $fieldName => $fieldType)
				{
					$result[$fieldName] = pg_unescape_bytea($result[$fieldName]);
				}
			}

			return array_change_key_case($result, \CASE_UPPER);
		}
		else
		{
			return $result;
		}
	}
}
