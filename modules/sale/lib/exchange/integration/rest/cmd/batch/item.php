<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd\Batch;


use Bitrix\Sale\Exchange\Integration\Rest\Cmd;

class Item
{
	protected $internalIndex;
	protected $cmd;

	public function __construct(Cmd\Base $cmd)
	{
		$this->cmd = $cmd;
	}

	public static function create(Cmd\Base $cmd)
	{
		return new static($cmd);
	}

	public function setInternalIndex($internalIndex)
	{
		$this->internalIndex = $internalIndex;
		return $this;
	}

	public function getInternalIndex()
	{
		return $this->internalIndex;
	}

	public function getCmd()
	{
		return $this->cmd;
	}
}