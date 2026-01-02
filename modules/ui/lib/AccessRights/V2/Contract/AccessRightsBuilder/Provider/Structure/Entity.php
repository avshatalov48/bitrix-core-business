<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure;

interface Entity
{
	public function getId(): string;

	public function getTitle(): string;

	/**
	 * @return Permission[]
	 */
	public function getPermissions(): array;
}
