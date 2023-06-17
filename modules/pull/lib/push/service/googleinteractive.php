<?php

namespace Bitrix\Pull\Push\Service;

class GoogleInteractive extends Google
{
	function __construct()
	{
		parent::__construct();
		$this->allowEmptyMessage = true;
	}

}
