<?php

namespace Bitrix\Im\V2\Recent\Initializer;

interface Stage
{
	public const GAP_TIME = 60;
	public const MIN_GAP_TIME = 1;
	public const WITHOUT_GAP_TIME = 0;

	public function getItems(InitialiazerResult $result): InitialiazerResult;
	public function sendPullAfterInsert(array $items): void;
	public function setGapTime(int $gapTime = self::GAP_TIME): Stage;
	public static function getType(): StageType;
}
