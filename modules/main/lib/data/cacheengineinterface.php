<?php

namespace Bitrix\Main\Data;

interface CacheEngineInterface
{
	public function isAvailable();
	public function clean($baseDir, $initDir = false, $filename = false);
	public function read(&$vars, $baseDir, $initDir, $filename, $ttl);
	public function write($vars, $baseDir, $initDir, $filename, $ttl);
	public function isCacheExpired($path);
}