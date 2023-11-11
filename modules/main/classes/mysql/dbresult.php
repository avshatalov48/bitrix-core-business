<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

class CDBResultMysql extends CAllDBResult
{
	protected function FetchRow()
	{
		if (is_object($this->result))
		{
			return mysqli_fetch_assoc($this->result);
		}
		return false;
	}

	function SelectedRowsCount()
	{
		if ($this->nSelectedCount !== false)
		{
			return $this->nSelectedCount;
		}

		if (is_object($this->result))
		{
			return mysqli_num_rows($this->result);
		}
		else
		{
			return 0;
		}
	}

	function AffectedRowsCount()
	{
		if (isset($this) && is_object($this) && is_object($this->DB))
		{
			$this->DB->DoConnect();
			return mysqli_affected_rows($this->DB->db_Conn);
		}
		else
		{
			global $DB;
			$DB->DoConnect();
			return mysqli_affected_rows($DB->db_Conn);
		}
	}

	function FieldsCount()
	{
		if (is_object($this->result))
		{
			return mysqli_num_fields($this->result);
		}
		else
		{
			return 0;
		}
	}

	function FieldName($iCol)
	{
		$fieldInfo = mysqli_fetch_field_direct($this->result, $iCol);
		return $fieldInfo->name;
	}

	protected function GetRowsCount(): ?int
	{
		if (is_object($this->result))
		{
			return mysqli_num_rows($this->result);
		}

		return null;
	}

	protected function Seek(int $offset): void
	{
		mysqli_data_seek($this->result, $offset);
	}
}

class CDBResult extends CDBResultMysql
{
}
