<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Sale\Result;

/**
 * Class Externalizer
 * @package Bitrix\Catalog\Rest
 * экстерналайзер для работы с именованными списками|ответами
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
			$data = $this->convertKeysToCamelCase([$id=>$data]);
		}

		if($this->format & self::SORTING_KEYS)
		{
			$data = $this->multiSortKeysArray($data);
		}

		return $r->setData(['data'=>$data]);
	}

	public function toArray()
	{
		return $this->process()->getData()['data'];
	}

	protected function multiSortKeysArray(array $data)
	{
		ksort($data, SORT_NATURAL);

		foreach ($data as $k=>&$item)
		{
			if(is_array($item))
				$item = $this->multiSortKeysArray($item);
		}

		return $data;
	}

	/**
	 * @param $data
	 * @return array
	 * экстерналайзер работает только с НЕ именнованым списком. id списка не передается
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
	 * обязательное именование (ключ в массиве) списка или результата в ответе
	 */
	private function getIdList($data)
	{
		return key($data);
	}

	protected function convertKeysToCamelCase($fields)
	{
		$controller = $this->getController();
		$view = $this->getView($controller);

		return $view->convertKeysToCamelCase($fields);
	}

	public function getPage(Page $page)
	{
		$id = $this->convertKeysToCamelCase($page->getId());
		return new Page($id, $this->toArray()[$id], $page->getTotalCount());
	}
}