<?php

namespace Bitrix\Main\Data;

interface CacheEngineStatInterface
{
	public function getReadBytes();
	public function getWrittenBytes();
	public function getCachePath();
}