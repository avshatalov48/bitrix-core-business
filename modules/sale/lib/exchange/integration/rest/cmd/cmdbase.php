<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


abstract class CmdBase extends Base
{
	public function __construct()
	{
		parent::__construct();

		$this->page = $this->getCmdName();
	}

	abstract protected function getCmdName();
}