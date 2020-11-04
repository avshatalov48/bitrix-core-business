<?php


namespace Bitrix\Sale\Exchange\Integration\Agent;

class Statistic
{
	static public function modify()
	{
		(new \Bitrix\Sale\Exchange\Integration\Manager\Statistic())
			->modify();

		return '\\Bitrix\\Sale\\Exchange\\Integration\\Agent\\Statistic::modify();';
	}
}