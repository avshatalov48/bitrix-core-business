<?php

namespace Bitrix\MessageService\Providers;
use \Bitrix\Main\Result;

interface Registrar
{
	public function isRegistered(): bool;
	public function isConfirmed(): bool;
	public function register(array $fields): Result;
	public function confirmRegistration(array $fields): Result;
	public function sendConfirmationCode(): Result;
	public function sync(): Registrar;
	public function getCallbackUrl(): string;
	public function getOwnerInfo(): array;
	public function getExternalManageUrl(): string;

}