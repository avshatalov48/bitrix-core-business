<?php

namespace Bitrix\Catalog\Access\Permission\Catalog;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Main\Loader;

/**
 * The object for applying the rights of the catalog to the rights of the iblocks.
 *
 * @see \Bitrix\Catalog\Access\Permission\Catalog\IblockCatalogPermissionsSaver for details and example.
 */
class IblockCatalogPermissions
{
	private bool $canRead = false;
	private bool $canWrite = false;
	private bool $canFullAccess = false;
	/**
	 * List short access codes aka `U1`, `G2`, ...
	 *
	 * @see \Bitrix\Main\Access\AccessCode
	 * @var array
	 */
	private array $accessCodes;
	private array $deleteAccessCodes;

	/**
	 * @param array $accessCodes
	 * @param array $deleteAccessCodes
	 */
	public function __construct(array $accessCodes, array $deleteAccessCodes = [])
	{
		Loader::requireModule('iblock');

		$this->accessCodes = $accessCodes;
		$this->deleteAccessCodes = $deleteAccessCodes;
	}

	/**
	 * Set catalog rights.
	 *
	 * @param array $rights in format `[['id' => 'permissionId', 'value' => '...'], ...]`
	 *
	 * @return void
	 */
	public function setRights(array $rights): void
	{
		foreach ($rights as $item)
		{
			if (!is_array($item))
			{
				continue;
			}

			$id = (int)($item['id'] ?? 0);
			if (empty($id))
			{
				continue;
			}

			$value = (int)($item['value'] ?? 0);
			if (empty($value))
			{
				continue;
			}

			if (
				$id === PermissionDictionary::CATALOG_PRODUCT_READ
			)
			{
				$this->canRead = true;
			}
			elseif (
				$id === PermissionDictionary::CATALOG_PRODUCT_ADD
				|| $id === PermissionDictionary::CATALOG_PRODUCT_EDIT
				|| $id === PermissionDictionary::CATALOG_PRODUCT_DELETE
			)
			{
				$this->canWrite = true;
			}
			elseif ($id === PermissionDictionary::CATALOG_SETTINGS_ACCESS)
			{
				$this->canFullAccess = true;
			}

			if ($this->canRead && $this->canWrite && $this->canFullAccess)
			{
				break;
			}
		}
	}

	/**
	 * Can read access to iblock.
	 *
	 * @return bool
	 */
	public function getCanRead(): bool
	{
		return $this->canRead;
	}

	/**
	 * Can write access to iblock.
	 *
	 * @return bool
	 */
	public function getCanWrite(): bool
	{
		return $this->canWrite;
	}

	/**
	 * Can full access to iblock.
	 *
	 * @return bool
	 */
	public function getCanFullAccess(): bool
	{
		return $this->canFullAccess;
	}

	/**
	 * Actual access codes.
	 *
	 * @return  array
	 */
	public function getAccessCodes(): array
	{
		return $this->accessCodes;
	}

	/**
	 * Deleted access codes.
	 *
	 * @return array
	 */
	public function getDeleteAccessCodes(): array
	{
		return $this->deleteAccessCodes;
	}
}
