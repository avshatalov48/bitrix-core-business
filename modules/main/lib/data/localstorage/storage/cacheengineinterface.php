<?php
namespace Bitrix\Main\Data\LocalStorage\Storage;

interface CacheEngineInterface
{
	public function read(&$vars, $baseDir, $initDir, $filename, $ttl);
	public function write($vars, $baseDir, $initDir, $filename, $ttl);
}