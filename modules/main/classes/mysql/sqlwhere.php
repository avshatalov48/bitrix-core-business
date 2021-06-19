<?php

class CSQLWhere extends CAllSQLWhere
{
	public function _Empty($field)
	{
		return "(".$field." IS NULL OR ".$field." = '')";
	}

	public function _NotEmpty($field)
	{
		return "(".$field." IS NOT NULL AND LENGTH(".$field.") > 0)";
	}
}
