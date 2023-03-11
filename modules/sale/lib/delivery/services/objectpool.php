<?
namespace Bitrix\Sale\Delivery\Services;

/**
 * Class ObjectPool
 * @package Bitrix\Sale\Delivery\Services
 * @internal
 */
final class ObjectPool
{
	protected $usage = array();
	/** @var Base[] $objects */
	protected $objects = array();
	protected $maxObjectsCount = 0;

	public function __construct($maxObjectsCount = 0)
	{
		$this->maxObjectsCount = $maxObjectsCount;
	}

	public function getObject(array $fields)
	{
		$result = null;
		$index = $this->createIndex($fields);

		if(!isset($this->objects[$index]))
		{
			if($this->maxObjectsCount > 0 && count($this->objects) > $this->maxObjectsCount)
				$this->deleteOutdatedObject();

			$result = $this->createObject($index, $fields);
		}
		else
		{
			$result = $this->objects[$index];
			unset($this->usage[array_search($index, $this->usage)]);
		}

		array_push($this->usage, $index);
		return $result;
	}

	protected function createObject($index, array $fields)
	{
		$this->objects[$index] = Manager::createObject($fields);
		return $this->objects[$index];
	}

	protected function deleteOutdatedObject()
	{
		reset($this->usage);
		unset($this->objects[current($this->usage)]);
		unset($this->usage[key($this->usage)]);
	}

	protected function createIndex(array $fields)
	{
		return intval($fields['ID']) > 0 ? intval($fields['ID']) : md5(serialize($fields));
	}
}
