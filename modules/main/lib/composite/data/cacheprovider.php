<?
namespace Bitrix\Main\Composite\Data;

use Bitrix\Main;

abstract class CacheProvider
{
	abstract public function isCacheable();
	abstract public function setUserPrivateKey();
	abstract public function getCachePrivateKey();
	abstract public function onBeforeEndBufferContent();
}

class_alias("Bitrix\\Main\\Composite\\Data\\CacheProvider", "Bitrix\\Main\\Data\\StaticCacheProvider");
