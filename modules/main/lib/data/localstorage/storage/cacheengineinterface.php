<?php
namespace Bitrix\Main\Data\LocalStorage\Storage;

interface CacheEngineInterface
{
	public function read(&$allVars, $baseDir, $initDir, $filename, $TTL);
	public function write($allVars, $baseDir, $initDir, $filename, $TTL);
}