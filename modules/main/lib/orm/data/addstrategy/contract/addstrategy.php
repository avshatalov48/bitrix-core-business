<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Contract;

use Bitrix\Main\ORM\Data\AddStrategy\AddedData;
use Bitrix\Main\ORM\Data\AddStrategy\AddedMultiData;

interface AddStrategy
{
	public function add(array $dbFields): AddedData;

	public function addMulti(array $multiDbFields): AddedMultiData;
}
