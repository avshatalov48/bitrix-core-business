<?php


namespace Bitrix\Catalog\Controller;


use Bitrix\Catalog\RoundingTable;
use Bitrix\Main\Result;

final class Enum extends Controller
{
	public function getRoundTypesAction()
	{
		$r = [];
		$list = RoundingTable::getRoundTypes(true);

		foreach($list as $id=>$name)
		{
			$r[] = ['ID'=>$id, 'NAME'=>$name];
		}

		return ['ENUM'=>$r];
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		return new Result();
	}
}