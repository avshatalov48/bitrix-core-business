<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Main\Result;

/**
 * Interface RepositoryContract
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface RepositoryContract
{
	// ToDo by primary with spread ...?
	/**
	 * @param int $id
	 * @return mixed
	 */
	public function getEntityById(int $id);

	/**
	 * @param $params array|\ArrayAccess
	 * @return mixed
	 */
	public function getEntitiesBy($params);

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity ...$entities
	 * @return \Bitrix\Main\Result
	 */
	public function save(BaseEntity ...$entities): Result;

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity ...$entities
	 * @return \Bitrix\Main\Result
	 */
	public function delete(BaseEntity ...$entities): Result;
}