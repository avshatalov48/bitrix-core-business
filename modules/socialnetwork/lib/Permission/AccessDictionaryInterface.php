<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Permission;

interface AccessDictionaryInterface
{
	public function create(): string;
	public function update(): string;
	public function delete(): string;
}