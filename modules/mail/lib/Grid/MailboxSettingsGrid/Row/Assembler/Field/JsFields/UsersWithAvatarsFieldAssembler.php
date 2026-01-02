<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

use Bitrix\Main\Grid\Settings;
use Bitrix\Mail\Helper\User\User;

class UsersWithAvatarsFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'UsersWithAvatarsField';

	private string $dataKey;

	public function __construct(array $columnIds, string $dataKey, Settings $settings)
	{
		parent::__construct($columnIds, $settings);
		$this->dataKey = $dataKey;
	}

	/**
	 * @return array{users: User[]}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		$usersData = $rawValue[$this->dataKey] ?? [];

		if (empty($usersData))
		{
			return [
				'users' => [],
			];
		}

		return [
			'users' => $usersData,
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$usersData = $data[$this->dataKey] ?? [];

		if (empty($usersData))
		{
			return '';
		}

		$names = array_column($usersData, 'name');

		return implode(', ', array_filter($names));
	}
}
