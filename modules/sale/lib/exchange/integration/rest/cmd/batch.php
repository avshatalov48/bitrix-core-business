<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class Batch extends Base
{
	const HALT_DESABLE = 0;
	const CMD_PAGE = 'batch';

	public function __construct()
	{
		parent::__construct();
		$this->page = static::CMD_PAGE;
	}

	public function fill()
	{
		$this->query->set('halt', static::HALT_DESABLE);
		return $this;
	}
}