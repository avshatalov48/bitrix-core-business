<?php

namespace Bitrix\Iblock\Filter\DataProvider\Settings;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Filter\Settings;

class ElementSettings extends Settings
{
	private int $iblockId;
	private bool $showSections = true;
	private bool $showXmlId = false;

	public function __construct(array $params)
	{
		parent::__construct($params);

		if (!isset($params['IBLOCK_ID']))
		{
			throw new ArgumentException('Params array must be contains "IBLOCK_ID" value');
		}

		$this->iblockId = $params['IBLOCK_ID'];

		if (isset($params['SHOW_SECTIONS']))
		{
			$this->showSections = $params['SHOW_SECTIONS'];
		}

		if (isset($params['SHOW_XML_ID']))
		{
			$this->showXmlId = $params['SHOW_XML_ID'];
		}
	}

	public function getIblockId(): int
	{
		return $this->iblockId;
	}

	public function isShowSections(): bool
	{
		return $this->showSections;
	}

	public function isShowXmlId(): bool
	{
		return $this->showXmlId;
	}
}
