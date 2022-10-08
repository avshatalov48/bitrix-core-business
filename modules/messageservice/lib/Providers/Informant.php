<?php

namespace Bitrix\MessageService\Providers;

interface Informant
{
	public function isConfigurable(): bool;
	public function getType(): string;
	public function getId(): string;
	public function getName(): string;
	public function getShortName(): string;
	public function getManageUrl(): string;

}
