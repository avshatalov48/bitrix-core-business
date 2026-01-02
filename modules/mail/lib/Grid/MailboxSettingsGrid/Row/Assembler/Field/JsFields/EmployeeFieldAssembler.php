<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

use Bitrix\Main\Grid\Settings;

class EmployeeFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'EmployeeField';

	private string $dataKey;

	public function __construct(array $columnIds, string $dataKey, Settings $settings)
	{
		parent::__construct($columnIds, $settings);
		$this->dataKey = $dataKey;
	}

	/**
	 * @return array{
	 *     id: int,
	 *     name: string,
	 *     avatar: array{
	 *         src: string,
	 *         width: int,
	 *         height: int,
	 *         size: int,
	 *     },
	 *     pathToProfile: string,
	 * }
	 */
	protected function getRenderParams(array $rawValue): array
	{
		$userData = $rawValue[$this->dataKey] ?? [];

		if (empty($userData))
		{
			return [
				[],
			];
		}

		return [
			...$userData,
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$userData = $data[$this->dataKey] ?? [];

		return $userData['name'] ?? '';
	}
}
