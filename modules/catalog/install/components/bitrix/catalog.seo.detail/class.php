<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Iblock\InheritedProperty\BaseTemplate;
use Bitrix\Iblock\InheritedProperty\IblockTemplates;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Iblock\InheritedProperty\ElementTemplates;
use Bitrix\Iblock\InheritedProperty\SectionTemplates;
use Bitrix\Iblock\Template;
use Bitrix\Iblock\IblockTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogSeoDetail extends \CBitrixComponent implements Controllerable, Errorable
{
	protected const MODE_CATALOG = 'MODE_CATALOG';
	protected const MODE_SECTION = 'MODE_SECTION';
	protected const MODE_ELEMENT = 'MODE_ELEMENT';
	protected const TYPE_SECTION = 'S';
	protected const TYPE_ELEMENT = 'E';
	protected const TYPE_MANAGEMENT = 'M';
	protected const JS_SELECT_ACTION = 'BX.Catalog.SeoDetail.onSelectTemplate';

	private string $mode;
	private int $entityId = 0;
	private array $entityFields = [];
	private int $iblockId = 0;

	use ErrorableImplementation;

	/**
	 * @param $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @return string[]
	 */
	protected function listKeysSignedParameters()
	{
		return [
			'PRODUCT_ID',
			'IBLOCK_ID',
			'SECTION_ID',
			'MODE',
		];
	}

	/**
	 * @return void
	 */
	public function executeComponent(): void
	{
		Toolbar::deleteFavoriteStar();
		if (!$this->checkModules() || !$this->checkReadPermissions() || !$this->fillRequiredParameters())
		{
			$this->showErrors();

			return;
		}

		$this->setTitle();
		Toolbar::addButton(
			new Buttons\Button([
				'color' => Buttons\Color::LIGHT_BORDER,
				'text' => Loc::getMessage('CSD_SEO_HELP_TITLE'),
				'onclick' => new Buttons\JsHandler('BX.Catalog.SeoDetail.openSeoHelpPage'),
			]),
			ButtonLocation::AFTER_TITLE
		);

		$this->arResult['CARD_SETTINGS'] = $this->getCardSettings();

		$this->includeComponentTemplate();
	}

	/**
	 * Return example of hint by changed parameters
	 *
	 * @param string $templateId
	 * @param array $template
	 *
	 * @return string
	 */
	public function getHintAction(string $templateId, array $template): string
	{
		if (!$this->checkModules() || !$this->fillRequiredParameters() || !$this->checkModifyPermission())
		{
			return '';
		}

		$values = $this->getValues([$templateId => $this->prepareStringTemplate($template)]);

		return $values[$templateId]['hint'] ?? '';
	}

	/**
	 * Set option for hiding info messages into block fields
	 *
	 * @param string $messageId
	 *
	 * @return bool
	 */
	public function hideInfoMessageAction(string $messageId): bool
	{
		if (!$this->checkModules() || !$this->fillRequiredParameters() || !$this->checkReadPermissions())
		{
			return false;
		}

		$infoMessageIds = [];
		foreach ($this->getSchemeFields() as $scheme)
		{
			if (isset($scheme['MESSAGE']) && !empty($scheme['MESSAGE']['ID']))
			{
				$infoMessageIds[] = $scheme['MESSAGE']['ID'];
			}
		}

		if (!in_array($messageId, $infoMessageIds, true))
		{
			return false;
		}

		$hiddenInfoBlocks = $this->getHiddenInfoBlockIds();
		$hiddenInfoBlocks[] = $messageId;

		\CUserOptions::SetOption('catalog.seo.detail', 'hiddenInfoBlocks', Json::encode($hiddenInfoBlocks));

		return true;
	}

	/**
	 * Save seo template values
	 *
	 * @param array $values
	 * @return bool
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function saveAction(array $values): bool
	{
		if (!$this->checkModules() || !$this->fillRequiredParameters() || !$this->checkModifyPermission())
		{
			return false;
		}

		$fileCodes = [
			'SECTION_PICTURE_FILE_NAME',
			'SECTION_DETAIL_PICTURE_FILE_NAME',
			'ELEMENT_PREVIEW_PICTURE_FILE_NAME',
			'ELEMENT_DETAIL_PICTURE_FILE_NAME'
		];


		$needClearCache = false;
		if (isset($values['SEO_CLEAR_VALUES']))
		{
			$needClearCache = ($values['SEO_CLEAR_VALUES']['clearCache'] === 'Y');

			unset($values['SEO_CLEAR_VALUES']);
		}

		foreach ($values as $code => $value)
		{
			if ($this->getMode() !== self::MODE_CATALOG && $value['inherited'] !== 'N')
			{
				$values[$code] = '';
			}
			elseif (in_array($code, $fileCodes, true))
			{
				$values[$code] = $this->prepareStringTemplate($value);
			}
			else
			{
				$values[$code] = $value['template'];
			}
		}

		$fieldCodes = [];
		foreach ($this->getSchemeFields() as $section)
		{
			$fieldCodes[] = array_keys($section['FIELDS']);
		}
		$values = array_intersect_key($values, array_flip(array_merge(...$fieldCodes)));

		$iblockTemplates = $this->getIblockTemplates();
		$iblockTemplates->set($values);

		if ($needClearCache && $iblockTemplates->getValuesEntity())
		{
			$iblockTemplates->getValuesEntity()->clearValues();
		}

		return true;
	}

	/**
	 * @param array $value
	 * @return string
	 */
	protected function prepareStringTemplate(array $value): string
	{
		$formattedField = [
			'TEMPLATE' => $value['template'],
			'LOWER' => ($value['lowercase'] === 'Y') ? 'Y' : 'N',
			'TRANSLIT' => ($value['transliterate'] === 'Y') ? 'Y' : 'N',
			'SPACE' => $value['whitespaceCharacter'] ?? ' ',
		];

		return Template\Helper::convertArrayToModifiers($formattedField);
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function checkModules(): bool
	{
		if (!Loader::includeModule('iblock'))
		{
			$this->errorCollection[] = new Error(
				Loc::getMessage(
					'CSD_SEO_MODULE_IS_NOT_INSTALLED',
					['#MODULE_NAME#' => 'iblock']
				)
			);

			return false;
		}

		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new Error(
				Loc::getMessage(
					'CSD_SEO_MODULE_IS_NOT_INSTALLED',
					['#MODULE_NAME#' => 'catalog']
				)
			);

			return false;
		}

		return true;
	}

	/**
	 * Fill required entity fields for building templates
	 *
	 * @return bool
	 */
	protected function fillRequiredParameters(): bool
	{
		$mode = $this->arParams['MODE'];
		$iblockId = (int)$this->arParams['IBLOCK_ID'];
		if ($mode === self::MODE_ELEMENT)
		{
			$element = null;
			$productId = (int)$this->arParams['PRODUCT_ID'];
			if ($productId > 0)
			{
				$iterator = \CIBlockElement::GetList(
					[],
					[
						'IBLOCK_ID' => $iblockId,
						'ID' => $productId,
						'ACTIVE' => 'Y',
						'ACTIVE_DATE' => 'Y',
						'CHECK_PERMISSIONS' => 'Y',
						'MIN_PERMISSION' => 'R',
					],
					false,
					false,
					['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT']
				);

				$element = $iterator->Fetch();
			}

			if (!$element)
			{
				$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_EMPTY_PRODUCT')));

				return false;
			}

			$this->entityId = $productId;
			$this->entityFields = $element;
		}
		elseif ($mode === self::MODE_SECTION)
		{
			$section = null;
			$sectionId = (int)$this->arParams['SECTION_ID'];
			if ($sectionId > 0)
			{
				$iterator = CIBlockSection::GetList(
					[],
					[
						'IBLOCK_ID' => $iblockId,
						'SECTION_ID' => $sectionId,
					],
					false,
					['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE', 'DESCRIPTION']
				);

				$section = $iterator->Fetch();
			}
			if (!$section)
			{
				$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_EMPTY_SECTION')));

				return false;
			}

			$this->entityId = $sectionId;
			$this->entityFields = $section;
		}
		else
		{
			$mode = self::MODE_CATALOG;
			$iblockIterator = IblockTable::getList([
				'select' => ['ID', 'NAME', 'CODE'],
				'filter' => ['=ID' => $iblockId]
			]);

			$fields = $iblockIterator->fetch();
			if (!$fields)
			{
				$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_EMPTY_CATALOG')));

				return false;
			}

			$this->entityFields = $fields;
		}

		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if ($productFactory)
		{
			$this->iblockId = $iblockId;
		}
		else
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_EMPTY_CATALOG')));

			return false;
		}

		$this->mode = $mode;

		return true;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 */
	protected function checkReadPermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_ACCESS_DENIED')));

			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 */
	protected function checkModifyPermission(): bool
	{
		if ($this->getMode() === self::MODE_CATALOG)
		{
			$editRule = AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS);
		}
		else
		{
			$editRule = AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_EDIT);
		}

		if (!$editRule || !$this->checkReadPermissions())
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CSD_SEO_ERROR_EDIT_IS_PROHIBIT')));

			return false;
		}

		return true;
	}

	/**
	 * @return void
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			$this->includeErrorComponent($error->getMessage());
		}
	}

	/**
	 * @param string $errorMessage
	 * @param string|null $description
	 * @return void
	 */
	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
				'IS_HTML' => 'Y',
			]
		);
	}

	/**
	 * @return BaseTemplate
	 */
	protected function getIblockTemplates(): BaseTemplate
	{
		if ($this->getMode() === self::MODE_SECTION)
		{
			return new SectionTemplates($this->getIblockId(), $this->getEntityId());
		}

		if ($this->getMode() === self::MODE_ELEMENT)
		{
			return new ElementTemplates($this->getIblockId(), $this->getEntityId());
		}

		return new IblockTemplates($this->getIblockId());
	}

	/**
	 * @param array|null $externalTemplates
	 * @return array
	 */
	protected function getValues(array $externalTemplates = null): array
	{
		$iblockTemplates = $this->getIblockTemplates();
		$values = $iblockTemplates->getValuesEntity();
		$entity = $values->createTemplateEntity();
		$entity->setFields($this->entityFields);
		$templates = $iblockTemplates->findTemplates();

		if ($externalTemplates)
		{
			foreach ($externalTemplates as $code => $template)
			{
				$templates[$code] = $templates[$code] ?? ['INHERITED' => 'N'];
				$templates[$code]['TEMPLATE'] = $template;
			}
		}

		$values = [];
		foreach ($this->getSchemeFields() as $section)
		{
			foreach ($section['FIELDS'] as $code => $field)
			{
				$values[$code] = [
					'template' => '',
					'isExistedAttributes' => $this->isFileNameField($code),
					'lowercase' => 'N',
					'transliterate' => 'N',
					'whitespaceCharacter' => '',
					'inherited' => 'Y',
					'hint' => '',
					'clearCache' => 'N'
				];
			}
		}

		foreach ($templates as $code => $template)
		{
			$formatted = Template\Helper::convertModifiersToArray($template);
			$values[$code] = [
				'template' => $formatted['TEMPLATE'],
				'isExistedAttributes' => $values[$code]['isExistedAttributes'],
				'lowercase' => ($formatted['LOWER'] === 'Y') ? 'Y' : 'N',
				'transliterate' => ($formatted['TRANSLIT'] === 'Y') ? 'Y' : 'N',
				'whitespaceCharacter' =>
					strlen($formatted['SPACE']) > 1
						? mb_substr($formatted['SPACE'], 0, 1)
						: $formatted['SPACE']
				,
				'inherited' => ($template['INHERITED'] !== 'N') ? 'Y' : 'N',
				'hint' => \Bitrix\Main\Text\HtmlFilter::encode(
					\Bitrix\Iblock\Template\Engine::process($entity, $template["TEMPLATE"])
				),
			];
		}

		return $values;
	}

	/**
	 * @return string
	 */
	protected function getMenuId(): string
	{
		return 'catalogSeoDetailTemplateMenu';
	}

	/**
	 * @return void
	 */
	protected function setTitle(): void
	{
		global $APPLICATION;
		$title = Loc::getMessage('CSD_SEO_CATALOG_EDITOR_TITLE', []);
		if ($this->mode === self::MODE_ELEMENT)
		{
			$title = Loc::getMessage(
				'CSD_SEO_PRODUCT_EDITOR_TITLE',
				['#PRODUCT_NAME#' => htmlspecialcharsbx($this->entityFields['NAME'])]
			);
		}
		elseif ($this->mode === self::MODE_SECTION)
		{
			$title = Loc::getMessage(
				'CSD_SEO_SECTION_EDITOR_TITLE',
				['#SECTION_NAME#' => htmlspecialcharsbx($this->entityFields['NAME'])]
			);
		}

		$APPLICATION->setTitle($title);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 */
	protected function getCardSettings(): array
	{
		$formId = uniqid('seo_editor');
		$containerId = "{$formId}_container";

		return [
			'schemeFields' => $this->getSchemeFields(),
			'menuItems' => $this->getMenuItemMap(),
			'formId' => $formId,
			'containerId' => $containerId,
			'signedParameters' => $this->getSignedParameters(),
			'componentName' => $this->getName(),
			'values' => $this->getValues(),
			'mode' => $this->getMode(),
			'readOnly' => !$this->checkModifyPermission(),
		];
	}

	/**
	 * @return array[]
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getSchemeFields(): array
	{
		$map = [
			'SEO_FOR_SECTIONS_PAGE_TITLE' => [
				'ID' => 'SEO_FOR_SECTIONS_PAGE_TITLE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_SECTIONS'),
				'TYPE' => self::TYPE_SECTION,
				'MESSAGE' =>
					$this->getMode() === self::MODE_CATALOG
						? [
							'DESCRIPTION' => Loc::getMessage('CSD_SEO_FOR_SECTIONS_INFO_DESCRIPTION_MODE_CATALOG'),
							'HEADER' => Loc::getMessage('CSD_SEO_FOR_SECTIONS_INFO_HEADER'),
						]
						: null
				,
				'FIELDS' => [
					'SECTION_META_TITLE' => [
						'ID' => 'SECTION_META_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_META_TITLE'),
					],
					'SECTION_META_KEYWORDS' => [
						'ID' => 'SECTION_META_KEYWORDS',
						'TITLE' => Loc::getMessage('CSD_SEO_META_KEYWORDS'),
					],
					'SECTION_META_DESCRIPTION' => [
						'ID' => 'SECTION_META_DESCRIPTION',
						'TITLE' => Loc::getMessage('CSD_SEO_META_DESCRIPTION'),
					],
					'SECTION_PAGE_TITLE' => [
						'ID' => 'SECTION_PAGE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_SECTION_PAGE_TITLE'),
					],
				],
			],
			'SEO_FOR_ELEMENTS_PAGE_TITLE' => [
				'ID' => 'SEO_FOR_ELEMENTS_PAGE_TITLE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_ELEMENTS'),
				'TYPE' => self::TYPE_ELEMENT,
				'MESSAGE' =>
					$this->getMode() === self::MODE_ELEMENT
						? [
							'DESCRIPTION' => Loc::getMessage('CSD_SEO_FOR_ELEMENTS_INFO_DESCRIPTION_MODE_ELEMENT'),
							'HEADER' => Loc::getMessage('CSD_SEO_FOR_SECTIONS_INFO_HEADER'),
						]
						: null
				,
				'FIELDS' => [
					'ELEMENT_META_TITLE' => [
						'ID' => 'ELEMENT_META_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_META_TITLE'),
					],
					'ELEMENT_META_KEYWORDS' => [
						'ID' => 'ELEMENT_META_KEYWORDS',
						'TITLE' => Loc::getMessage('CSD_SEO_META_KEYWORDS'),
					],
					'ELEMENT_META_DESCRIPTION' => [
						'ID' => 'ELEMENT_META_DESCRIPTION',
						'TITLE' => Loc::getMessage('CSD_SEO_META_DESCRIPTION'),
					],
					'ELEMENT_PAGE_TITLE' => [
						'ID' => 'ELEMENT_PAGE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_ELEMENT_PAGE_TITLE'),
					],
				],
			],
			'SEO_FOR_SECTIONS_PICTURE' => [
				'ID' => 'SEO_FOR_SECTIONS_PICTURE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_SECTIONS_PICTURE'),
				'TYPE' => self::TYPE_SECTION,
				'MESSAGE' => null,
				'FIELDS' => [
					'SECTION_PICTURE_FILE_ALT' => [
						'ID' => 'SECTION_PICTURE_FILE_ALT',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_ALT'),
					],
					'SECTION_PICTURE_FILE_TITLE' => [
						'ID' => 'SECTION_PICTURE_FILE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_TITLE'),
					],
					'SECTION_PICTURE_FILE_NAME' => [
						'ID' => 'SECTION_PICTURE_FILE_NAME',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_NAME'),
					],
				],
			],
			'SEO_FOR_SECTIONS_DETAIL_PICTURE' => [
				'ID' => 'SEO_FOR_SECTIONS_DETAIL_PICTURE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_SECTIONS_DETAIL_PICTURE'),
				'TYPE' => self::TYPE_SECTION,
				'MESSAGE' => null,
				'FIELDS' => [
					'SECTION_DETAIL_PICTURE_FILE_ALT' => [
						'ID' => 'SECTION_DETAIL_PICTURE_FILE_ALT',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_ALT'),
					],
					'SECTION_DETAIL_PICTURE_FILE_TITLE' => [
						'ID' => 'SECTION_DETAIL_PICTURE_FILE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_TITLE'),
					],
					'SECTION_DETAIL_PICTURE_FILE_NAME' => [
						'ID' => 'SECTION_DETAIL_PICTURE_FILE_NAME',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_NAME'),
					],
				],
			],
			'SEO_FOR_ELEMENTS_PREVIEW_PICTURE' => [
				'ID' => 'SEO_FOR_ELEMENTS_PREVIEW_PICTURE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_ELEMENTS_PREVIEW_PICTURE'),
				'TYPE' => self::TYPE_ELEMENT,
				'MESSAGE' => null,
				'FIELDS' => [
					'ELEMENT_PREVIEW_PICTURE_FILE_ALT' => [
						'ID' => 'ELEMENT_PREVIEW_PICTURE_FILE_ALT',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_ALT'),
					],
					'ELEMENT_PREVIEW_PICTURE_FILE_TITLE' => [
						'ID' => 'ELEMENT_PREVIEW_PICTURE_FILE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_TITLE'),
					],
					'ELEMENT_PREVIEW_PICTURE_FILE_NAME' => [
						'ID' => 'ELEMENT_PREVIEW_PICTURE_FILE_NAME',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_NAME'),
					],
				],
			],
			'SEO_FOR_ELEMENTS_DETAIL_PICTURE' => [
				'ID' => 'SEO_FOR_ELEMENTS_DETAIL_PICTURE',
				'TITLE' => Loc::getMessage('CSD_SEO_FOR_ELEMENTS_DETAIL_PICTURE'),
				'TYPE' => self::TYPE_ELEMENT,
				'MESSAGE' => null,
				'FIELDS' => [
					'ELEMENT_DETAIL_PICTURE_FILE_ALT' => [
						'ID' => 'ELEMENT_DETAIL_PICTURE_FILE_ALT',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_ALT'),
					],
					'ELEMENT_DETAIL_PICTURE_FILE_TITLE' => [
						'ID' => 'ELEMENT_DETAIL_PICTURE_FILE_TITLE',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_TITLE'),
					],
					'ELEMENT_DETAIL_PICTURE_FILE_NAME' => [
						'ID' => 'ELEMENT_DETAIL_PICTURE_FILE_NAME',
						'TITLE' => Loc::getMessage('CSD_SEO_FILE_NAME'),
					],
				],
			],
			'SEO_MANAGEMENT' => [
				'ID' => 'SEO_MANAGEMENT',
				'TITLE' => Loc::getMessage('CSD_SEO_MANAGEMENT'),
				'TYPE' => self::TYPE_MANAGEMENT,
				'MESSAGE' => null,
				'FIELDS' => [
					'SEO_CLEAR_VALUES' => [
						'ID' => 'SEO_CLEAR_VALUES',
						'TITLE' => Loc::getMessage('CSD_SEO_CLEAR_VALUES'),
					],
				],
			],
		];

		if ($this->getMode() === self::MODE_ELEMENT)
		{
			$map = array_filter(
				$map,
				static function ($item){
					return $item['TYPE'] === self::TYPE_ELEMENT;
				})
			;
		}

		$hiddenInfoBlocks = $this->getHiddenInfoBlockIds();
		foreach ($map as &$item)
		{
			if (isset($item['MESSAGE']))
			{
				$infoMessageId = $this->getInfoMessageId($item['ID']);
				$item['MESSAGE']['ID'] = $infoMessageId;
				$item['MESSAGE']['HIDDEN'] = in_array($infoMessageId, $hiddenInfoBlocks, true) ? 'Y' : 'N';
			}
		}

		if (Loader::includeModule('bitrix24'))
		{
			unset(
				$map['SEO_FOR_ELEMENTS_DETAIL_PICTURE'],
				$map['SEO_FOR_ELEMENTS_PREVIEW_PICTURE']
			);
		}

		return $map;
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	protected function isFileNameField(string $code): bool
	{
		$fileNameFields = [
			'ELEMENT_DETAIL_PICTURE_FILE_NAME',
			'ELEMENT_PREVIEW_PICTURE_FILE_NAME',
			'SECTION_DETAIL_PICTURE_FILE_NAME',
			'SECTION_PICTURE_FILE_NAME',
		];

		return in_array($code, $fileNameFields, true);
	}

	/**
	 * @param string $messageCode
	 * @return string
	 */
	protected function getInfoMessageId(string $messageCode): string
	{
		return mb_strtolower("{$messageCode}_{$this->getMode()}");
	}

	/**
	 * @return array
	 */
	protected function getMenuItemMap(): array
	{
		return [
			self::TYPE_ELEMENT => $this->prepareAdminMenuToJs(
				CIBlockParameters::GetInheritedPropertyTemplateElementMenuItems(
					$this->getIblockId(),
					self::JS_SELECT_ACTION,
					$this->getMenuId(),
				)
			),
			self::TYPE_SECTION => $this->prepareAdminMenuToJs(
				CIBlockParameters::GetInheritedPropertyTemplateSectionMenuItems(
					$this->getIblockId(),
					self::JS_SELECT_ACTION,
					$this->getMenuId(),
				)
			),
		];
	}

	/**
	 * @param $items
	 * @return array
	 */
	protected function prepareAdminMenuToJs($items): array
	{
		$result = [];
		foreach ($items as $item)
		{
			$formattedItem = ['text' => $item['TEXT']];
			if (isset($item['MENU']))
			{
				$formattedItem['items'] = $this->prepareAdminMenuToJs($item['MENU']);
			}
			else
			{
				$formattedItem['onclick'] = $item['ONCLICK'];
			}

			$result[] = $formattedItem;
		}

		return $result;
	}

	protected function getHiddenInfoBlockIds(): array
	{
		$hiddenInfoBlockJson = \CUserOptions::GetOption('catalog.seo.detail', 'hiddenInfoBlocks');
		if ($hiddenInfoBlockJson)
		{
			$hiddenInfoBlockJson = Json::decode($hiddenInfoBlockJson);
		}

		return is_array($hiddenInfoBlockJson) ? $hiddenInfoBlockJson : [];
	}

	/**
	 * @return int
	 */
	protected function getEntityId(): int
	{
		return $this->entityId;
	}

	/**
	 * @return int
	 */
	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	/**
	 * @return string
	 */
	protected function getMode(): string
	{
		return $this->mode;
	}
}