<?php
namespace Bitrix\Perfmon\Sql;

/**
 * Class BaseObject
 * Base class for all schema objects such as tables, columns, indexes, etc.
 * @package Bitrix\Perfmon\Sql
 */
abstract class BaseObject
{
	/** @var BaseObject|null */
	public $parent = null;
	public $name = '';
	public $body = '';
	protected $ciName = '';

	/**
	 * @param string $name Name of the table.
	 */
	public function __construct($name = '')
	{
		$this->name = (string)$name;
		$this->ciName = static::getCompareName($this->name);
	}

	/**
	 * Sets source code for object.
	 *
	 * @param string $body The body.
	 *
	 * @return BaseObject
	 */
	public function setBody($body)
	{
		$this->body = trim($body);
		return $this;
	}

	/**
	 * Sets parent for object.
	 * <p>
	 * For example Table for Column.
	 *
	 * @param BaseObject $parent Parent object.
	 *
	 * @return BaseObject
	 */
	public function setParent(BaseObject $parent = null)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Returns "unquoted" name of the object.
	 *
	 * @param array|string $name Name or array of names to unquote.
	 *
	 * @return array|string
	 */
	final public function getUnquotedName($name = null)
	{
		if ($name === null && $this->name)
		{
			return $this->getUnquotedName($this->name);
		}
		elseif (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$name[$key] = $this->getUnquotedName($value);
			}
		}
		elseif ($name[0] == '`')
		{
			$name = trim($name, '`');
		}
		elseif ($name[0] == '"')
		{
			$name = trim($name, '"');
		}
		elseif ($name[0] == '[')
		{
			$name = trim($name, '[]');
		}
		return $name;
	}

	/**
	 * Returns "lowercased" name of the object.
	 * <p>
	 * If name is not quoted then it made lowercase.
	 *
	 * @return string
	 */
	final public function getLowercasedName()
	{
		if ($this->name[0] == '`')
		{
			return $this->name;
		}
		elseif ($this->name[0] == '"')
		{
			return $this->name;
		}
		elseif ($this->name[0] == '[')
		{
			return $this->name;
		}
		else
		{
			return mb_strtolower($this->name);
		}
	}

	/**
	 * Returns "normalized" name of the table.
	 * <p>
	 * If name is not quoted then it made uppercase.
	 *
	 * @param string $name Table name.
	 * @return string
	 */
	final public static function getCompareName($name)
	{
		if ($name)
		{
			if ($name[0] == '`')
			{
				return substr($name, 1, -1);
			}
			elseif ($name[0] == '"')
			{
				return substr($name, 1, -1);
			}
			elseif ($name[0] == '[')
			{
				return substr($name, 1, -1);
			}
			else
			{
				return mb_strtoupper($name);
			}
		}
		else
		{
			return $name;
		}
	}

	/**
	 * Compares name of the table with given.
	 * <p>
	 * If name has no quotes when comparison is case insensitive.
	 *
	 * @param string $name Table name to compare.
	 * @return int
	 * @see strcmp
	 */
	final public function compareName($name)
	{
		return strcmp($this->ciName, static::getCompareName($name));
	}

	/**
	 * Return DDL or commentary for object creation.
	 *
	 * @param string $dbType Database type.
	 *
	 * @return array|string
	 */
	public function getCreateDdl($dbType = '')
	{
		return '// ' . get_class($this) . ':getCreateDdl not implemented';
	}

	/**
	 * Return DDL or commentary for object destruction.
	 *
	 * @param string $dbType Database type.
	 *
	 * @return array|string
	 */
	public function getDropDdl($dbType = '')
	{
		return '// ' . get_class($this) . ':getDropDdl not implemented';
	}

	/**
	 * Return DDL or commentary for object modification.
	 *
	 * @param BaseObject $target Target object.
	 * @param string $dbType Database type.
	 *
	 * @return array|string
	 */
	public function getModifyDdl(BaseObject $target, $dbType = '')
	{
		return '// ' . get_class($this) . ':getModifyDdl not implemented';
	}
}
