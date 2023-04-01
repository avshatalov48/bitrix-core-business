<?php
namespace Bitrix\Iblock\Url\AdminPage;

use Bitrix\Main;
use	Bitrix\Iblock;

abstract class BaseBuilder
{
	public const TYPE_AUTODETECT = 'AUTO';

	public const TYPE_ID = 'BASE';
	protected const TYPE_WEIGHT = null;
	protected const PATH_PREFIX = '';

	public const PAGE_ELEMENT_LIST = 'elementList';
	public const PAGE_ELEMENT_DETAIL = 'elementDetail';
	public const PAGE_ELEMENT_COPY = 'elementCopy';
	public const PAGE_ELEMENT_SAVE = 'elementSave';
	public const PAGE_ELEMENT_SEARCH = 'elementSearch';
	public const PAGE_ELEMENT_SEO = 'elementSeo';
	public const PAGE_SECTION_LIST = 'sectionList';
	public const PAGE_SECTION_DETAIL = 'sectionDetail';
	public const PAGE_SECTION_COPY = 'sectionCopy';
	public const PAGE_SECTION_SAVE = 'sectionSave';
	public const PAGE_SECTION_SEARCH = 'sectionSearch';
	public const PAGE_SECTION_SEO = 'sectionSeo';
	public const PAGE_CATALOG_SEO = 'catalogSeo';

	public const ENTITY_SECTION = 'section';
	public const ENTITY_ELEMENT = 'element';

	protected const SLIDER_PATH_VARIABLE = 'slider_path';

	/** @var Main\HttpRequest */
	protected $request;
	/** @var string */
	protected $id;
	/** @var int */
	protected $weight;
	/** @var string */
	protected $languageId;

	/** @var int */
	protected $iblockId;
	/** @var array */
	protected $iblock;
	/** @var string */
	protected $iblockListMode;
	/** @var bool */
	protected $iblockListMixed;

	/** @var string */
	protected $prefix;

	protected $urlParams = [];

	protected $compiledUrlParams = '';

	protected $config = [];

	protected $urlTemplates = [];

	protected $templateVariables = [];
	/** @var bool */
	protected $sliderMode;

	public function __construct()
	{
		$this->request = Main\Context::getCurrent()->getRequest();

		$this->initSettings();
		$this->initConfig();
		$this->resetIblock();
		$this->initIblockListMode();
		$this->initUrlTemplates();
	}

	public function __destruct()
	{
		$this->request = null;
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
		if ($this->iblockId !== $iblockId)
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
	}

	public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
		$this->setTemplateVariable('#PATH_PREFIX#', $this->prefix);
	}

	public function getPrefix(): string
	{
		return $this->prefix;
	}

	public function setUrlParams(array $list): void
	{
		if ($this->isSliderMode())
		{
			$list += static::getSliderOptions();
		}
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

	public function setMixedIblockList(): void
	{
		$this->setIblockListMode(Iblock\IblockTable::LIST_MODE_COMBINED);
	}

	public function setSeparateIblockList(): void
	{
		$this->setIblockListMode(Iblock\IblockTable::LIST_MODE_SEPARATE);
	}

	public function preloadUrlData(string $entityType, array $entityIds): void
	{
		switch ($entityType)
		{
			case self::ENTITY_SECTION:
				$this->preloadSectionUrlData($entityIds);
				break;
			case self::ENTITY_ELEMENT:
				$this->preloadElementUrlData($entityIds);
				break;
		}
	}

	public function clearPreloadedUrlData(): void {}

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
			$this->getDetailVariables(self::PAGE_SECTION_DETAIL, $entityId, $options, $additional)
		);
	}

	public function getSectionSaveUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_SAVE),
			$this->getDetailVariables(self::PAGE_SECTION_SAVE, $entityId, $options, $additional)
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
			$this->getDetailVariables(self::PAGE_ELEMENT_DETAIL, $entityId, $options, $additional)
		);
	}

	public function getElementCopyUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_COPY),
			$this->getDetailVariables(self::PAGE_ELEMENT_COPY, $entityId, $options, $additional)
		);
	}

	public function getElementSaveUrl(?int $entityId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_SAVE),
			$this->getDetailVariables(self::PAGE_ELEMENT_SAVE, $entityId, $options, $additional)
		);
	}

	public function getElementSearchUrl(array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_SEARCH),
			$this->getExtendedVariables($options, $additional)
		);
	}

	public function getCatalogSeoUrl(array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_CATALOG_SEO),
			$this->getExtendedVariables($options, $additional)
		);
	}

	public function getElementSeoUrl(int $productId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_ELEMENT_SEO),
			$this->getDetailSeoVariables($productId, $options, $additional)
		);
	}

	public function getSectionSeoUrl(int $sectionId, array $options = [], string $additional = ''): string
	{
		return $this->fillUrlTemplate(
			$this->getUrlTemplate(self::PAGE_SECTION_SEO),
			$this->getSectionSeoVariables($sectionId, $options, $additional)
		);
	}

	public function getContextMenuItems(string $pageType, array $items = [], array $options = []): ?array
	{
		return null;
	}

	public function getBaseParams(): string
	{
		if ($this->iblockId === null)
		{
			return '';
		}

		return 'IBLOCK_ID='.$this->iblockId
			.'&type='.urlencode($this->iblock['IBLOCK_TYPE_ID'])
			.'&lang='.urlencode($this->languageId);
	}

	public function getUrlParams(array $options = [], string $additional = ''): string
	{
		return $this->getBaseParams().$this->extendUrl($options, $additional);
	}

	public function getLanguageParam(): string
	{
		return 'lang='.urlencode($this->languageId);
	}

	public function getUrlBuilderIdParam(): string
	{
		return 'urlBuilderId='.urlencode($this->id);
	}

	public function setSliderMode(bool $mode): void
	{
		$this->sliderMode = $mode;
	}

	public function isSliderMode(): bool
	{
		return $this->sliderMode;
	}

	public function getDetailPageSlider(): string
	{
		$path = $this->getSliderPath();
		if (!$this->checkSliderPath($path))
		{
			return '';
		}
		$path = \CUtil::JSEscape($path);

		return '<script>'
			. 'window.history.replaceState({}, \'\', \'' . $path . '\');' . "\n"
			. 'BX.ready(function () {' . "\n"
			. '	BX.SidePanel.Instance.open(' . "\n"
			. '		\'' . $path . '\'' . "\n"
			. '	);' . "\n"
			. '});' . "\n"
			. '</script>'
		;
	}

	public function showDetailPageSlider(): void
	{
		echo $this->getDetailPageSlider();
	}

	protected function checkCurrentPage(array $urlList): bool
	{
		$currentPage = $this->request->getRequestedPage();
		foreach ($urlList as $url)
		{
			if (strncmp($currentPage, $url, strlen($url)) === 0)
			{
				return true;
			}
		}

		return false;
	}

	protected function initSettings(): void
	{
		$this->id = static::TYPE_ID;
		$this->weight = static::TYPE_WEIGHT;
		$this->setLanguageId(LANGUAGE_ID);
		$this->setPrefix(static::PATH_PREFIX);
		$this->setSliderMode($this->request->get('IFRAME') === 'Y');
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
			$listMode = (Main\Config\Option::get('iblock', 'combined_list_mode') === 'Y'
				? Iblock\IblockTable::LIST_MODE_COMBINED
				: Iblock\IblockTable::LIST_MODE_SEPARATE
			);
		}
		$this->iblockListMode = $listMode;
		$this->iblockListMixed = ($this->iblockListMode === Iblock\IblockTable::LIST_MODE_COMBINED);
	}

	protected function setIblockListMode(string $listMode): void
	{
		if (
			$listMode === Iblock\IblockTable::LIST_MODE_SEPARATE
			|| $listMode === Iblock\IblockTable::LIST_MODE_COMBINED
		)
		{
			$this->iblockListMode = $listMode;
			$this->iblockListMixed = ($this->iblockListMode === Iblock\IblockTable::LIST_MODE_COMBINED);
			$this->initUrlTemplates();
		}
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

	protected function getEntityFilter(?int $entityId): string
	{
		$result = '';
		if ($entityId !== null && $entityId >= 0)
		{
			$result = $this->compileUrlParams([
				'ID' => $entityId,
			]);
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
		return ($this->urlTemplates[$templateId] ?? null);
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

	protected function getTemplateVariables(): array
	{
		return $this->templateVariables;
	}

	protected function getExtendedVariables(array $options = [], string $additional = ''): array
	{
		$replaces = $this->getTemplateVariables();
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

	protected function getDetailVariables(string $page, ?int $entityId, array $options = [], string $additional = ''): array
	{
		$replaces = $this->getExtendedVariables($options, $additional);
		$replaces['#ENTITY_ID#'] = (string)$entityId;
		$replaces['#ENTITY_FILTER#'] = $this->getEntityFilter($entityId);
		return $replaces;
	}

	protected function getDetailSeoVariables(?int $entityId, array $options = [], string $additional = ''): array
	{
		$replaces = $this->getExtendedVariables($options, $additional);
		$replaces['#PRODUCT_ID#'] = (string)$entityId;

		return $replaces;
	}

	protected function getSectionSeoVariables(?int $sectionId, array $options = [], string $additional = ''): array
	{
		$replaces = $this->getExtendedVariables($options, $additional);
		$replaces['#SECTION_ID#'] = (string)$sectionId;

		return $replaces;
	}

	protected function getCopyAction(): string
	{
		return '&action=copy';
	}

	protected function preloadSectionUrlData(array $sectionIds): void {}

	protected function preloadElementUrlData(array $elementIds): void {}

	protected static function getSliderOptions(): array
	{
		return [
			'IFRAME' => 'Y',
			'IFRAME_TYPE' => 'SIDE_SLIDER',
		];
	}

	protected function getSliderPath(): ?string
	{
		return $this->request->get(self::SLIDER_PATH_VARIABLE);
	}

	public function getSliderPathOption(string $path): ?array
	{
		if ($path === '')
		{
			return null;
		}

		return [
			self::SLIDER_PATH_VARIABLE => $path,
		];
	}

	public function getSliderPathString(string $path): string
	{
		if ($path === '')
		{
			return '';
		}

		return self::SLIDER_PATH_VARIABLE . '=' . $path;
	}

	protected function checkSliderPath(?string $path): bool
	{
		if ($path === null)
		{
			$path = $this->getSliderPath();
		}
		if ($path === null || $path === '')
		{
			return false;
		}

		$prepared = [];
		foreach ($this->getSliderPathTemplates() as $mask)
		{
			if (preg_match($mask, $path, $prepared))
			{
				return true;
			}
		}

		return false;
	}

	protected function getSliderPathTemplates(): array
	{
		return [];
	}

	/**
	 * Open settings page of IBlock context
	 *
	 * <i>Example: for catalog IBlock it should open settings of catalog</i>
	 * @return void
	 */
	public function openSettingsPage(): void
	{
	}

	/**
	 * Subscribe to save settings events depending on the context
	 *
	 * @return void
	 */
	public function subscribeOnAfterSettingsSave(): void
	{
	}
}
