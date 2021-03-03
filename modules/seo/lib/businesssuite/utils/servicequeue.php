<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Seo\BusinessSuite\Internals;

final class ServiceQueue
{
	/** @var self[] $queue*/
	private static $instancePool = [];

	/** @var array $head */
	private $head;

	private $values;

	public static function getInstance(string $type) : self
	{
		if(!array_key_exists($type,static::$instancePool))
		{
			static::$instancePool[$type] = new static($type);
		}
		return static::$instancePool[$type];
	}

	private function __construct(string $type)
	{
		$this->values = Internals\ServiceQueueTable::getList([
			'select' => ['ID','SERVICE_TYPE','CLIENT_ID','TYPE'],
			'filter' => ['=TYPE'=> $type ],
			'order' => ['SORT' => 'DESC']
		]);
		$this->head = $this->getHead();
	}
	private function __clone()
	{}

	public function getHead()
	{
		return $this->head = $this->head? $this->head : $this->values->fetch();
	}
	public function removeHead()
	{
		if($this->head)
		{
			Internals\ServiceQueueTable::delete($this->head['ID']);
			unset($this->head);
		}
	}
}