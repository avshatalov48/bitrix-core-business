<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Sale\Result;

/**
 * Class Externalizer
 * @package Bitrix\Catalog\Rest
 * externalizer for working with named lists | answers
 */
final class Externalizer extends ModificationFieldsBase
	implements Arrayable
{
	public function __construct(ViewManager $manager, $data=[])
	{
		$this->format = self::TO_WHITE_LIST | self::TO_CAMEL | self::SORTING_KEYS;

		parent::__construct($manager, $data);
	}

	public function process()
	{
		$r = new Result();

		$data = $this->getData();
		$id = $this->getIdList($data);

		$data = $data[$id];
		if($this->format & self::TO_WHITE_LIST)
		{
			$data = $this->externalize($data);
		}

		if($this->format & self::TO_CAMEL)
		{
			$data = static::convertKeysToCamelCase([$id=>$data]);
		}

		if($this->format & self::SORTING_KEYS)
		{
			$data = static::multiSortKeysArray($data);
		}

		return $r->setData(['data'=>$data]);
	}

	public function toArray()
	{
		return $this->process()->getData()['data'];
	}

	static public function multiSortKeysArray(array $data)
	{
		ksort($data, SORT_NATURAL);

		foreach ($data as $k=>&$item)
		{
			if(is_array($item))
				$item = static::multiSortKeysArray($item);
		}

		return $data;
	}

	/**
	 * @param $data
	 * @return array
	 * the externalizer works only with a NOT named list. list id is not supported
	 */
	private function externalize($fields)
	{
		$name = $this->getName();
		$controller = $this->getController();
		$view = $this->getView($controller);

		if($name == 'getfields'){}
		elseif($name == 'delete'){}
		elseif($name == 'get'
			|| $name == 'add'
			|| $name == 'update')
		{
			$fields = $view->externalizeFieldsGet($fields);
		}
		elseif($name == 'list')
		{
			$fields = $view->externalizeListFields($fields);
		}
		else
		{
			$fields = $view->externalizeResult($name, $fields);
		}

		return $fields;
	}

	/**
	 * @param $data
	 * @return int|null|string
	 * array key is required
	 */
	private function getIdList($data)
	{
		return key($data);
	}

	static public function convertKeysToCamelCase($fields)
	{
		return Converter::toJson()
			->process($fields);
	}

	public function getPage(Page $page)
	{
		$id = static::convertKeysToCamelCase($page->getId());
		return new Page($id, $this->toArray()[$id], $page->getTotalCount());
	}
}