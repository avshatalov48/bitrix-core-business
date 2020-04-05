<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/sqlwhere.php");

class CSQLWhere extends CAllSQLWhere
{
	function _Empty($field)
	{
		return "(".$field." IS NULL OR ".$field." = '')";
	}
	function _NotEmpty($field)
	{
		return "(".$field." IS NOT NULL AND LENGTH(".$field.") > 0)";
	}
}
