<?php

namespace Bitrix\Im\V2\Recent\Initializer;

interface Source
{
	public function getSourceId(): ?int;
	public function getItems(string $pointer, int $limit): InitialiazerResult;
	public function getStage(): Stage;
	public function setIsFirstInit(bool $flag): Source;
	public static function getType(): SourceType;
}
