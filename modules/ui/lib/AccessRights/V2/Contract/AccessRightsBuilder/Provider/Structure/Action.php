<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure;

interface Action
{
	public function getId(): string;

	public function getTitle(): string;
}
