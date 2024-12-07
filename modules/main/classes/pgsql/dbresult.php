<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

class CDBResult extends CAllDBResult
{
	protected $byteaFields = false;

	protected function FetchRow()
	{
		if ($this->result)
		{
			$result = pg_fetch_assoc($this->result);
			if ($result)
			{
				if ($this->byteaFields === false)
				{
					$this->byteaFields = [];
					$fieldNum = 0;
					foreach ($result as $fieldName => $_)
					{
						$fieldType = pg_field_type($this->result, $fieldNum);
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

				return array_change_key_case($result, CASE_UPPER);
			}
		}
		return false;
	}

	public function SelectedRowsCount()
	{
		return ($this->result ? pg_num_rows($this->result) : 0);
	}

	public function AffectedRowsCount()
	{
		return pg_affected_rows($this->result);
	}

	public function FieldsCount()
	{
		return pg_num_fields($this->result);
	}

	public function FieldName($iCol)
	{
		return mb_strtoupper(pg_field_name($this->result, $iCol));
	}

	protected function GetRowsCount(): ?int
	{
		if ($this->result)
		{
			return pg_num_rows($this->result);
		}

		return null;
	}

	protected function Seek(int $offset): void
	{
		pg_result_seek($this->result, $offset);
	}
}
