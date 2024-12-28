<?php

namespace Bitrix\Im\V2\Recent\Initializer;

interface Stage
{
	public function getItems(string $pointer, int $limit): InitialiazerResult;
	public function sendPullAfterInsert(array $items): void;
	public function getSource(): Source;
	public static function getType(): StageType;
}
