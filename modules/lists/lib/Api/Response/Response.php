<?php

namespace Bitrix\Lists\Api\Response;

class Response extends \Bitrix\Main\Result
{
	/**
	 * @return int|string
	 */
	public function getPermission(): int | string
	{
		return $this->data['permission'] ?? \CListPermissions::ACCESS_DENIED;
	}

	/**
	 * @param int|string $permission
	 * @return $this
	 */
	public function setPermission(int | string $permission): static
	{
		$this->data['permission'] = $permission;

		return $this;
	}

	/**
	 * @param Response $response
	 * @return $this
	 */
	public function fillFromResponse(Response $response): static
	{
		$this
			->setPermission($response->getPermission())
			->addErrors($response->getErrors())
		;

		return $this;
	}
}
