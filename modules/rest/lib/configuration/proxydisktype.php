<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Rest\Configuration\DataProvider\Disk;
use Bitrix\Main\Loader;

if (!Loader::includeModule('disk'))
{
	return false;
}

/**
 * @deprecated use \Bitrix\Rest\Configuration\DataProvider\Disk\ProxyDiskType
 */
class ProxyDiskType extends Disk\ProxyDiskType
{

}
