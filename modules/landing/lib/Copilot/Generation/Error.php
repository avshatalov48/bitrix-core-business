<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Landing\Copilot\Generation\Type\Errors;
use Bitrix\Main\Localization\Loc;

class Error
{
	private const LANG_PREFIX = 'LANDING_AI_SITE_ERROR_';
	private const DEFAULT_ERROR = 'LANDING_AI_SITE_ERROR';

	public string $code = '';
	public string $message = '';

	public function getMessage(): string
	{
		return "{$this->code}: {$this->message}";
	}

	/**
	 * Arraylize data
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->code,
			'message' => $this->message,
		];
	}

	/**
	 * Create object from array
	 * @param array $data
	 * @return Error
	 */
	public static function fromArray(array $data): Error
	{
		$dto = new self();
		$dto->code = $data['code'] ?? '';
		$dto->message = $data['message'] ?? '';

		return $dto;
	}

	/**
	 * Get Error DTO with default message by code
	 * @param Errors|null $code - get enumerated code, or null for default Error
	 * @return Error
	 */
	public static function createError(?Errors $code = null): self
	{
		$error = new Error();

		if ($code)
		{
			$error->code = $code->name;
			$error->message = Loc::getMessage(self::LANG_PREFIX . $code->value) ?? '';
		}
		else
		{
			$error->code = self::DEFAULT_ERROR;
		}

		return $error;
	}
}