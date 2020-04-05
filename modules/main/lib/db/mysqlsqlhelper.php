<?php
namespace Bitrix\Main\DB;

use Bitrix\Main\ORM;

class MysqlSqlHelper extends MysqlCommonSqlHelper
{
	/**
	 * Escapes special characters in a string for use in an SQL statement.
	 *
	 * @param string $value Value to be escaped.
	 * @param integer $maxLength Limits string length if set.
	 *
	 * @return string
	 */
	public function forSql($value, $maxLength = 0)
	{
		if ($maxLength > 0)
			$value = substr($value, 0, $maxLength);

		return mysql_real_escape_string($value, $this->connection->getResource());
	}

	/**
	 * Returns instance of a descendant from Entity\ScalarField
	 * that matches database type.
	 *
	 * @param string $name Database column name.
	 * @param mixed $type Database specific type.
	 * @param array $parameters Additional information.
	 *
	 * @return \Bitrix\Main\ORM\Fields\ScalarField
	 */
	public function getFieldByColumnType($name, $type, array $parameters = null)
	{
		switch ($type)
		{
			case "int":
				return new ORM\Fields\IntegerField($name);

			case "real":
				return new ORM\Fields\FloatField($name);

			case "datetime":
			case "timestamp":
				return new ORM\Fields\DatetimeField($name);

			case "date":
				return new ORM\Fields\DateField($name);
		}
		return new ORM\Fields\StringField($name);
	}
}
