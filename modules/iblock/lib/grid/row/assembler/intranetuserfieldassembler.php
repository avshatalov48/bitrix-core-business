<?php

namespace Bitrix\Iblock\Grid\Row\Assembler;

use Bitrix\Main\Grid\Row\Assembler\Field\UserFieldAssembler;

/**
 * @todo move to `intranet` module.
 */
final class IntranetUserFieldAssembler extends UserFieldAssembler
{
	private string $profilePathTemplate;

	public function __construct(array $columnIds, string $profilePathTemplate)
	{
		parent::__construct($columnIds);

		$this->profilePathTemplate = $profilePathTemplate;
	}

	private function getProfilePath(int $userId): string
	{
		return str_replace('#ID#', $userId, $this->profilePathTemplate);
	}

	protected function loadUserName(int $userId): string
	{
		$userName = parent::loadUserName($userId);
		if (!empty($userName))
		{
			$userName = htmlspecialcharsbx($userName);
			$profilePath = htmlspecialcharsbx($this->getProfilePath($userId));

			return "<a href=\"{$profilePath}\" target='_blank' bx-tooltip-user-id='{$userId}'>{$userName}</a>";
		}

		return '';
	}
}
