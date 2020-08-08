<?php
namespace Bitrix\Iblock\Url\AdminPage;

use Bitrix\Main,
	Bitrix\Iblock;

abstract class BaseBuilder
{
	public const TYPE_AUTODETECT = 'AUTO';

	public const TYPE_ID = 'BASE';
	protected const TYPE_WEIGHT = null;
	protected const PATH_PREFIX = '';

	protected const PAGE_ELEMENT_LIST = 'elementList';
	protected const PAGE_ELEMENT_DETAIL = 'elementDetail';
	protected const PAGE_ELEMENT_COPY = 'elementCopy';
	protected const PAGE_ELEMENT_SAVE = 'elementSave';
	protected const PAGE_ELEMENT_SEARCH = 'elementSearch';
	protected const PAGE_SECTION_LIST = 'sectionList';
	protected const PAGE_SECTION_DETAIL = 'sectionDetail';
	protected const PAGE_SECTION_COPY = 'sectionCopy';
	protected const PAGE_SECTION_SAVE = 'sectionSave';
	protected const PAGE_SECTION_SEARCH = 'sectionSearch';

	protected $id = null;

	protected $weight = null;

	protected $languageId = null;

	/** @var int */
	protected $iblockId = null;
	/** @var array */
	protected $iblock = null;
	/** @var string */
	protected $iblockListMode = null;
	/** @var bool */
	protected $iblockListMixed = null;

	/** @var string */
	protected $prefix = null;

	protected $urlParams = [];

	protected $compiledUrlParams = '';

	protected $config = [];

	protected $urlTemplates = [];

	protected $templateVariables = [];

	public function __construct()
	{
		$this->initSettings();
		$this->initConfig();
		$this->resetIblock();
		$this->initIblockListMode();
		$this->initUrlTemplates();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getWeight(): ?int
	{
		return $this->weight;
	}

	public function setLanguageId(string $languageId): void
	{
		$this->languageId = $languageId;
		$this->setTemplateVariable('#LANGUAGE_ID#', $this->languageId);
		$this->setTemplateVariable('#LANGUAGE#', $this->getLanguageParam());
		$this->setTemplateVariable('#BASE_PARAMS#', $this->getBaseParams());
	}

	public function getLanguageId(): string
	{
		return $this->languageId;
	}

	public function setIblockId(int $iblockId): void
	{
		$this->resetIblock();
		if ($iblockId > 0)
		{
			$iblock = \CIBlock::GetArrayByID($iblockId);
			if (!empty($iblock) && is_array($iblock))
			{
				$this->iblockId = $iblockId;
				$this->iblock = $iblock;
			}
			unset($iblock);
		}
		$this->initIblockListMode();
		$this->initUrlTemplates();
		$this->setTemplateVariable('#IBLOCK_ID#', (string)$this->iblockId);
		$this->setTemplateVariable('#BASE_PARAMS#', $this->getBaseParams());
	}

	public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
		$this->setTemplateVariable('#PATH_PREFIX#', $this->prefix);
	}

	public function setUrlParams(array $list): void
	{
		$this->urlParams = array_filter($list, [__CLASS__, 'clearNull']);
		$this->compiledUrlParams = $this->compileUrlParams($this->urlParams);
	}

	public function getCompiledParams(array $params): string
	{
		return $this->compileUrlParams($params);
	}

	public function isIblockListMixed(): bool
	{
		$this->initIblockListMode();
		return $this->iblockListMixed;
	}

	abstract public function use(): bool;

	public function getSectionListUrl(?int $parentId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_LIST),
			$this->getListVariables(self::PAGE_SECTION_LIST, $parentId, $options, $additional)
		);
	}

	public function getSectionDetailUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_DETAIL),
			$this->getDetailVariables(self::PAGE_SECTION_DETAIL, (int)$entityId, $options, $additional)
		);
	}

	public function getSectionSaveUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_SAVE),
			$this->getDetailVariables(self::PAGE_SECTION_SAVE, (int)$entityId, $options, $additional)
		);
	}

	public function getSectionSearchUrl(array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_SEARCH),
			$this->getExtendedVariables($options, $additional)
		);
	}

	public function getElementListUrl(?int $parentId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_LIST),
			$this->getListVariables(self::PAGE_ELEMENT_LIST, $parentId, $options, $additional)
		);
	}

	public function getElementDetailUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_DETAIL),
			$this->getDetailVariables(self::PAGE_ELEMENT_DETAIL, (int)$entityId, $options, $additional)
		);
	}

	public function getElementCopyUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_COPY),
			$this->getDetailVariables(self::PAGE_ELEMENT_COPY, (int)$entityId, $options, $additional)
		);
	}

	public function getElementSaveUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_SAVE),
			$this->getDetailVariables(self::PAGE_ELEMENT_SAVE, (int)$entityId, $options, $additional)
		);
	}

	public function getElementSearchUrl(array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_SEARCH),
			$this->getExtendedVariables($options, $additional)
		);
	}

	public function getBaseParams(): string
	{
		return 'IBLOCK_ID='.$this->iblockId
			.'&type='.urlencode($this->iblock['IBLOCK_TYPE_ID'])
			.'&lang='.urlencode($this->languageId);
	}

	public function getLanguageParam(): string
	{
		return 'lang='.urlencode($this->languageId);
	}

	protected function initSettings(): void
	{
		$this->id = static::TYPE_ID;
		$this->weight = static::TYPE_WEIGHT;
		$this->setLanguageId(LANGUAGE_ID);
		$this->setPrefix(static::PATH_PREFIX);
	}

	protected function initConfig(): void
	{

	}

	protected static function clearNull($value): bool
	{
		return $value !== null;
	}

	protected function resetIblock(): void
	{
		$this->iblockId = null;
		$this->iblock = null;
		$this->iblockListMode = null;
	}

	protected function initIblockListMode(): void
	{
		if ($this->iblockListMode !== null)
		{
			return;
		}
		$listMode = '';
		if ($this->iblockId !== null)
		{
			$listMode = (string)$this->iblock['LIST_MODE'];
		}
		if (
			$listMode != Iblock\IblockTable::LIST_MODE_SEPARATE
			&& $listMode != Iblock\IblockTable::LIST_MODE_COMBINED
		)
		{
			$listMode = ((string)Main\Config\Option::get('iblock', 'combined_list_mode') == 'Y'
				? Iblock\IblockTable::LIST_MODE_COMBINED
				: Iblock\IblockTable::LIST_MODE_SEPARATE
			);
		}
		$this->iblockListMode = $listMode;
		unset($listMode);
		$this->iblockListMixed = ($this->iblockListMode === Iblock\IblockTable::LIST_MODE_COMBINED);
	}

	protected function compileUrlParams(array $params): string
	{
		$result = '';
		$this->compileParamsLevel($result, '', $params);
		return $result;
	}

	protected function compileParamsLevel(string &$result, string $prefix, array $params): void
	{
		$params = array_filter($params, [__CLASS__, 'clearNull']);
		if (empty($params))
		{
			return;
		}
		foreach ($params as $key => $value)
		{
			if ($prefix === '' && is_numeric($key))
			{
				continue;
			}
			$index = ($prefix !== '' ? $prefix.'['.$key.']' : $key);
			if (is_array($value))
			{
				$this->compileParamsLevel($result, $index, $value);
			}
			else
			{
				$result .= '&'.urlencode($index).'='.urlencode((string)$value);
			}
		}
		unset($index, $key, $value);
	}

	protected function getParentFilter(?int $parentId): string
	{
		$result = '';
		if ($parentId !== null)
		{
			if ($parentId === -1)
			{
				$result = $this->compileUrlParams([
					'find_section_section' => $parentId
				]);
			}
			elseif ($parentId >= 0)
			{
				$result = $this->compileUrlParams([
					'find_section_section' => $parentId,
					'SECTION_ID' => $parentId,
					'apply_filter' => 'Y'
				]);
			}
		}

		return $result;
	}

	protected function extendUrl(array $options = [], string $additional = ''): string
	{
		$result = $this->compiledUrlParams;
		$compiledOptions = $this->compileUrlParams($options);
		if ($compiledOptions !== '')
		{
			$result .= $compiledOptions;
		}
		unset($compiledOptions);

		if ($additional !== '')
		{
			$result .= $additional;
		}
		return $result;
	}

	abstract protected function initUrlTemplates(): void;

	protected function getUrlTemplate(string $templateId): ?string
	{
		return (isset($this->urlTemplates[$templateId])
			? $this->urlTemplates[$templateId]
			: null
		);
	}

	protected function fillUrlTemplate(?string $template, array $replaces): string
	{
		if ($template === null)
		{
			return '';
		}
		if (empty($replaces))
		{
			return $template;
		}
		return str_replace(array_keys($replaces), array_values($replaces), $template);
	}

	protected function setTemplateVariable(string $name, string $value): void
	{
		$this->templateVariables[$name] = $value;
	}

	protected function getExtendedVariables(array $options = [], string $additional = ''): array
	{
		$replaces = $this->templateVariables;
		$replaces['#ADDITIONAL_PARAMETERS#'] = $this->extendUrl($options, $additional);
		return $replaces;
	}

	protected function getListVariables(string $page, ?int $parentId, array $options = [], string $additional = ''): array
	{
		$replaces = $this->getExtendedVariables($options, $additional);
		$replaces['#PARENT_ID#'] = (string)$parentId;
		$replaces['#PARENT_FILTER#'] = $this->getParentFilter($parentId);
		return $replaces;
	}

	protected function getDetailVariables(string $page, int $entityId, array $options = [], string $additional = ''): array
	{
		$replaces = $this->getExtendedVariables($options, $additional);
		$replaces['#ENTITY_ID#'] = (string)$entityId;
		return $replaces;
	}

	protected function getCopyAction(): string
	{
		return '&action=copy';
	}
}