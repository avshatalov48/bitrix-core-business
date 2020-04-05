<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\PropertyIndex;

class Dictionary
{
	protected $iblockId = 0;
	protected $cache = array();
	protected static $exists = array();

	/**
	 * @param integer $iblockId Information block identifier.
	 */
	public function __construct($iblockId)
	{
		$this->iblockId = intval($iblockId);
	}

	/**
	 * Returns information block identifier.
	 *
	 * @return integer
	 */
	public function getIblockId()
	{
		return $this->iblockId;
	}

	/**
	 * Internal method to get database table name for storing values.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return "b_iblock_".$this->iblockId."_index_val";
	}

	/**
	 * Checks if dictionary exists in the database.
	 * Returns true on success.
	 *
	 * @return boolean
	 */
	public function isExists()
	{
		if (!isset(self::$exists[$this->iblockId]))
		{
			$connection = \Bitrix\Main\Application::getConnection();
			self::$exists[$this->iblockId] = $connection->isTableExists($this->getTableName());
		}

		return self::$exists[$this->iblockId];
	}

	/**
	 * Returns validators for VALUE field.
	 * This is an internal method for eAccelerator compatibility.
	 *
	 * @return array[]\Bitrix\Main\Entity\Validator\Base
	 */
	public static function validateValue()
	{
		return array(
			new \Bitrix\Main\Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * Creates new dictionary for information block.
	 * You have to be sure that dictionary does not exists.
	 *
	 * @return void
	 */
	public function create()
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$connection->createTable($this->getTableName(), array(
			"ID" => new \Bitrix\Main\Entity\IntegerField("ID", array(
				'primary' => true,
				'unique' => true,
				'required' => true,
			)),
			"VALUE" => new \Bitrix\Main\Entity\StringField("VALUE", array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateValue'),
			)),
		), array("ID"), array("ID"));

		$connection->createIndex($this->getTableName(), 'IX_'.$this->getTableName().'_0', array("VALUE"), array("VALUE" => 200));

		$this->cache = array();
		self::$exists[$this->iblockId] = true;
	}

	/**
	 * Deletes existing dictionary in the database.
	 * You have to check that dictionary exists before calling this method.
	 *
	 * @return void
	 */
	public function drop()
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$connection->dropTable($this->getTableName());

		$this->cache = array();
		self::$exists[$this->iblockId] = false;
	}

	/**
	 * Returns unique number presentation of the string.
	 *
	 * @param string  $value           Value for dictionary lookup.
	 * @param boolean $addWhenNotFound Add new value to the dictionary if none found.
	 *
	 * @return int
	 */
	public function getStringId($value, $addWhenNotFound = true)
	{
		if (!isset($this->cache[$value]))
		{
			$connection = \Bitrix\Main\Application::getConnection();

			$sqlHelper  = $connection->getSqlHelper();
			$valueId    = $connection->queryScalar("SELECT ID FROM ".$this->getTableName()." WHERE VALUE = '".$sqlHelper->forSql($value)."'");
			if ($valueId === null)
			{
				if ($addWhenNotFound)
				{
					$valueId = $connection->add($this->getTableName(), array(
						"VALUE" => $value,
					));
				}
				else
				{
					$valueId = 0;
				}
			}

			$this->cache[$value] = intval($valueId);
		}

		return $this->cache[$value];
	}

	/**
	 * Returns string by its identifier in the dictionary.
	 *
	 * @param integer $valueId Value identifier for dictionary lookup.
	 *
	 * @return string
	 */
	public function getStringById($valueId)
	{
		$valueId = (int)$valueId;
		if ($valueId <= 0)
			return "";

		$connection  = \Bitrix\Main\Application::getConnection();
		$stringValue = $connection->queryScalar("SELECT VALUE FROM ".$this->getTableName()." WHERE ID = ".$valueId);
		return ($stringValue === null ? "" : $stringValue);
	}

	/**
	 * Returns array of string by its identifier in the dictionary.
	 *
	 * @param array $valueIDs Value identifier for dictionary lookup.
	 *
	 * @return array
	 */
	public function getStringByIds($valueIDs)
	{
		$result = [];
		if (empty($valueIDs) || !is_array($valueIDs))
			return $result;
		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($valueIDs, true);
		if (empty($valueIDs))
			return $result;

		$connection  = \Bitrix\Main\Application::getConnection();

		$result = array_fill_keys($valueIDs, '');

		$rs = $connection->query("SELECT ID, VALUE FROM ".$this->getTableName()." WHERE ID IN(".implode(',',$valueIDs).")");
		while ($row = $rs->fetch())
		{
			$result[$row['ID']] = $row['VALUE'];
		}

		return $result;
	}
}
