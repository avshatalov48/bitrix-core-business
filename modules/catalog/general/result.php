<?
use Bitrix\Catalog;

class CCatalogResult extends CDBResult
{
	/** @var null|Catalog\Model\Entity $entity */
	private $entity = null;

	private $fields = array();
	private $resultKeys = array();
	private $erasedKeys = array();

	public function __construct($entity, $result = null)
	{
		parent::__construct($result);

		$this->entity = new $entity;

		$this->resultKeys = array();

		$this->fields = $this->entity->getCachedFieldList();
		if (!empty($this->fields))
			$this->resultKeys = array_fill_keys($this->fields, true);
	}

	public function setResult($result)
	{
		parent::__construct($result);
	}

	public function prepareSelect(array $select)
	{
		$this->erasedKeys = array();
		if (
			empty($select)
			|| (is_string($select) && $select == '*')
			|| (is_array($select) && in_array('*', $select))
		)
			return $select;
		foreach ($this->fields as $field)
		{
			$index = array_search($field, $select);
			if ($index !== false)
				continue;

			$select[] = $field;
			$this->erasedKeys[$field] = true;
		}
		unset($index, $field);

		return $select;
	}

	public function Fetch()
	{
		$row = parent::Fetch();

		if (!isset($this) || !is_object($this))
			return $row;

		if (empty($row))
		{
			$this->erasedKeys = array();
			return $row;
		}

		if (empty($this->fields))
			return $row;

		if (isset($row['ID']))
		{
			$this->entity->setCacheItem($row['ID'], $row);
			if (!empty($this->erasedKeys))
				$row = array_diff_key($row, $this->erasedKeys);
		}

		return $row;
	}
}