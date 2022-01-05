<?php

namespace Bitrix\Rest\Configuration\DataProvider\Disk;

use Bitrix\Disk\ProxyType;
use Bitrix\Disk\Security\FakeSecurityContext;
use Bitrix\Disk\Security\SecurityContext;
use Bitrix\Main\Loader;

if (!Loader::includeModule('disk'))
{
	return false;
}

class ProxyDiskType extends ProxyType\Base
{
	/**
	 * Gets security context (access provider) for user.
	 * Attention! File/Folder can use anywhere and SecurityContext have to check rights anywhere (any module).
	 *
	 * @param mixed $user User which use for check rights.
	 *
	 * @return SecurityContext
	 */
	public function getSecurityContextByUser($user)
	{
		return new FakeSecurityContext(null);
	}

	/**
	 * Gets url which use for building url to listing folders, trashcan, etc.
	 * @return string
	 */
	public function getStorageBaseUrl()
	{
		return '';
	}

	/**
	 * Get image (avatar) of entity.
	 * Can be shown with entityTitle in different lists.
	 *
	 * @param int $width Image width.
	 * @param int $height Image height.
	 *
	 * @return string
	 */
	public function getEntityImageSrc($width, $height)
	{
		return '';
	}

	/**
	 * Potential opportunity to attach object to external entity
	 * @return bool
	 */
	public function canAttachToExternalEntity()
	{
		return false;
	}

	/**
	 * Tells if objects is allowed to index by module "Search".
	 * @return bool
	 */
	public function canIndexBySearch()
	{
		return false;
	}
}