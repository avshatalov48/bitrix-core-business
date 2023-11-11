<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

class CDBResult extends CAllDBResult
{
	protected function FetchRow()
	{
		$result = pg_fetch_assoc($this->result);
		if ($result)
		{
			return array_change_key_case($result, CASE_UPPER);
		}
		return $result;
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
		if (is_resource($this->result))
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
