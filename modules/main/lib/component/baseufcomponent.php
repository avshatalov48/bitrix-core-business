<?php

namespace Bitrix\Main\Component;

use Bitrix\Main\Context;
use Bitrix\Main\UserField\HtmlBuilder;
use CBitrixComponent;
use CBitrixComponentTemplate;
use ReflectionClass;

/**
 * Class BaseUfComponent
 * @package Bitrix\Main\Component
 */
abstract class BaseUfComponent extends CBitrixComponent
{
	public const
		MODE_DEFAULT = '.default',
		TEMPLATE_PAGE_DEFAULT = '.default',
		TEMPLATE_NAME_DEFAULT = '.default';

	/*
	 * List of available media types
	 * MediaType === Template folder by default, may be overriding in child class
	 */
	public const
		MEDIA_TYPE_DEFAULT = '.default',
		MEDIA_TYPE_MOBILE = 'mobile';

	/**
	 * @var array $htmlBuilder
	 */
	protected static
		$htmlBuilder = [];

	/**
	 * @var CBitrixComponentTemplate $componentTemplate
	 * @var array $userField
	 * @var array $additionalParameters
	 */
	protected
		$componentTemplate,
		$userField = [],
		$additionalParameters = [];


	/**
	 * @var string $mediaType
	 * @var string $mode
	 */
	private
		$mediaType = '',
		$mode = '',
		$availableModes = [];

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->componentTemplate = new CBitrixComponentTemplate();
	}

	final public function executeComponent()
	{
		$this->initResult();
		$this->prepareResult();

		$this->initAvailableModes();
		$this->initMode();
		$this->initMediaType();

		$templateName = $this->resolveTemplateName();
		$templatePage = $this->resolveTemplatePage();

		$this->setTemplateName($templateName);

		if($templatePage && !$this->isExistTemplatePage($templatePage))
		{
			// changing  to default templatePage if file with $templatePage name not exist ...
			if($templatePage !== static::TEMPLATE_PAGE_DEFAULT)
			{
				$templatePage = static::TEMPLATE_PAGE_DEFAULT;
			}
			// ... or setting default templateName if $templatePage name
			// is equal to TEMPLATE_PAGE_DEFAULT and not exist in current templateName folder
			else
			{
				$this->setTemplateName(static::TEMPLATE_NAME_DEFAULT);
			}
		}

		if($templatePage)
		{
			$this->includeComponentTemplate($templatePage);
		}
		else
		{
			$this->__showError(str_replace(
				['#NAME#', '#PAGE#'],
				[$this->getMode(), $this->getMediaType()],
				"Cannot find '#NAME#' template with page '#PAGE#'"
			));
		}
	}

	final protected function initResult(): void
	{
		$this->setUserField($this->arParams['~userField'] ?? []);
		// TODO: $this->arParams['additionalParameters'] contains already html-encoded values (mostly by chance). This is deeply incorrect.
		$this->setAdditionalParameters($this->arParams['additionalParameters'] ?? []);
		$this->setParentComponent($this->getAdditionalParameter('parentComponent'));

		$this->arResult['additionalParameters'] = $this->getAdditionalParameters();
		$this->arResult['userField'] = $this->getUserField();
		$this->arResult['fieldName'] = $this->getFieldName();
		$this->arResult['value'] = $this->getFieldValue();
	}

	/**
	 * @return array
	 */
	public function getUserField()
	{
		return $this->userField;
	}

	/**
	 * @param array|bool $userField
	 */
	public function setUserField($userField): void
	{
		if (!is_array($userField))
		{
			$userField = [];
		}

		$this->userField = $userField;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getAdditionalParameter(string $key)
	{
		return ($this->additionalParameters[$key] ?? null);
	}

	/**
	 * @return array
	 */
	public function getAdditionalParameters(): array
	{
		return $this->additionalParameters;
	}

	/**
	 * @param array|null $additionalParameters
	 */
	public function setAdditionalParameters(?array $additionalParameters): void
	{
		$this->additionalParameters = $additionalParameters;
	}

	/**
	 * @return CBitrixComponent|null
	 */
	public function getParentComponent(): ?CBitrixComponent
	{
		return $this->__parent;
	}

	/**
	 * @param CBitrixComponent|null $_parent
	 */
	public function setParentComponent(?CBitrixComponent $_parent): void
	{
		$this->__parent = $_parent;
	}

	/**
	 * You can override this method in a child class
	 * to add the personal custom functionality of the child class
	 */
	protected function prepareResult(): void
	{

	}

	protected function initAvailableModes(): void
	{
		if(!empty($this->additionalParameters['mode']))
		{
			$modes = (is_array($this->additionalParameters['mode']) ?
				$this->additionalParameters['mode'] : [$this->additionalParameters['mode']]
			);
		}
		else
		{
			$modes = [static::MODE_DEFAULT];
		}

		$this->setAvailableModes($modes);
	}

	/**
	 * @return array
	 */
	public function getAvailableModes(): array
	{
		return $this->availableModes;
	}

	/**
	 * @param array $availableModes
	 */
	public function setAvailableModes(array $availableModes): void
	{
		$this->availableModes = $availableModes;
	}

	protected function initMode(): void
	{
		$availableModes = $this->getAvailableModes();
		$mode = array_shift($availableModes);
		$this->setMode($mode);
	}

	/**
	 * @param string $mode
	 */
	protected function setMode(string $mode): void
	{
		$this->mode = $mode;
	}

	protected function initMediaType(): void
	{
		$mediaType = static::MEDIA_TYPE_DEFAULT;
		if (isset($this->additionalParameters['mediaType']) && $this->additionalParameters['mediaType'])
		{
			$mediaType = $this->additionalParameters['mediaType'];
		}

		$this->setMediaType($mediaType);
	}

	/**
	 * @param string $mediaType
	 */
	protected function setMediaType(string $mediaType): void
	{
		$this->mediaType = $mediaType;
	}

	/**
	 * Resolving a mode name to template name.
	 * By default, mode === templateFolderName, can be otherwise in child class
	 * @return string
	 */
	protected function resolveTemplateName(): string
	{
		return ($this->getAvailableTemplateFolder() ?? static::TEMPLATE_NAME_DEFAULT);
	}

	/**
	 * @return string
	 */
	final public function getMode(): string
	{
		return $this->mode;
	}

	/**
	 * @return string
	 */
	protected function getTemplateNameFromMode(): string
	{
		return ($this->getMode() ?: static::TEMPLATE_NAME_DEFAULT);
	}

	/**
	 * Return templatePage name from mediaType
	 * or null if mediaType incorrect
	 * @return null|string
	 */
	final protected function resolveTemplatePage(): ?string
	{
		return ($this->isPossibleMediaType() ?
			$this->getTemplatePageFromMediaType() : null
		);
	}

	/**
	 * @param string|null $templatePage
	 * @return bool
	 */
	final protected function isExistTemplatePage(?string $templatePage = ''): bool
	{
		if(empty($templatePage))
		{
			$templatePage = $this->getTemplatePage();
		}
		return $this->hasTemplatePage($templatePage);
	}

	/**
	 * Checking if mediaType specified when calling the component is valid
	 * @return bool
	 */
	final protected function isPossibleMediaType(): bool
	{
		static $mediaTypes = null;
		if($mediaTypes === null)
		{
			$mediaTypes = $this->getMediaTypes();
		}
		return in_array($this->getMediaType(), $mediaTypes, true);
	}

	/**
	 * @return string
	 */
	protected function getTemplatePageFromMediaType(): string
	{
		return $this->getMediaType() ?: static::MEDIA_TYPE_DEFAULT;
	}

	/**
	 * Return all mediaTypes
	 * @return array
	 */
	final protected function getMediaTypes(): array
	{
		$reflection = new ReflectionClass(__CLASS__);
		$constants = $reflection->getConstants();
		$result = [];
		foreach($constants as $name => $value)
		{
			if(str_starts_with($name, 'MEDIA_TYPE_'))
			{
				$result[$name] = $value;
			}
		}
		return $result;
	}

	/**
	 * @return string
	 */
	protected function getMediaType(): string
	{
		return $this->mediaType;
	}

	/**
	 * @return bool
	 */
	final protected function hasTemplateFolder(): bool
	{
		static $checkedTemplateFolders = [];

		$name = $this->getName();
		$mode = $this->getMode();

		if (!isset($checkedTemplateFolders[$name]))
		{
			$checkedTemplateFolders[$name] = [];
		}

		if (
			!array_key_exists($mode, $checkedTemplateFolders)
			|| $checkedTemplateFolders[$name][$mode] === null
		)
		{
			$this->setTemplateName($this->getTemplateNameFromMode());
			$this->componentTemplate->Init($this);
			$checkedTemplateFolders[$name][$mode] = $this->componentTemplate->hasTemplate();
		}

		return $checkedTemplateFolders[$name][$mode];
	}

	/**
	 * Returning first correct template folder from array of availables modes
	 * @return null|string
	 */
	final protected function getAvailableTemplateFolder(): ?string
	{
		$availableMethodsKey = $this->generateAvailableModesHash();
		static $availableMode = [];

		if(
			!array_key_exists($availableMethodsKey, $availableMode)
			||
			$availableMode[$availableMethodsKey] === null
		)
		{
			foreach($this->getAvailableModes() as $mode)
			{
				$this->setMode($mode);
				if($this->hasTemplateFolder())
				{
					$availableMode[$availableMethodsKey] = $this->getMode();
					break;
				}
			}
		}
		return $availableMode[$availableMethodsKey];
	}

	/**
	 * @return string
	 */
	final protected function generateAvailableModesHash(): string
	{
		return md5(static::getUserTypeId() . json_encode($this->getAvailableModes()));
	}

	/**
	 * @param string $templatePage
	 * @return bool
	 */
	final protected function hasTemplatePage(string $templatePage): bool
	{
		static $isCheckedTemplatePage = null;

		if($isCheckedTemplatePage === null)
		{
			$this->componentTemplate->Init($this, $this->getTemplateNameFromMode());
			$isCheckedTemplatePage = $this->componentTemplate->hasTemplatePage($templatePage);
		}

		return $isCheckedTemplatePage;
	}

	/**
	 * @return string
	 */
	protected function getFieldName(): string
	{
		$nameFromAdditionalParameters = ($this->additionalParameters['NAME'] ?? null);
		$nameFromUserField = ($this->userField['FIELD_NAME'] ?? null);

		$fieldName = $nameFromAdditionalParameters ?? $nameFromUserField;
		if (!$fieldName)
		{
			return '';
		}

		if($this->isMultiple() && !mb_substr_count($fieldName, '[]'))
		{
			$fieldName .= '[]';
		}

		return $fieldName;
	}

	/**
	 * @return bool
	 */
	public function isMultiple(): bool
	{
		return (isset($this->userField['MULTIPLE']) && $this->userField['MULTIPLE'] === 'Y');
	}

	/**
	 * @return array
	 */
	protected function getFieldValue(): array
	{
		$value = [];

		if(empty($this->additionalParameters['bVarsFromForm']) && !isset($this->additionalParameters['VALUE']))
		{
			$value = (
				isset($this->userField['ENTITY_VALUE_ID']) && $this->userField['ENTITY_VALUE_ID'] <= 0
					? ($this->userField['SETTINGS']['DEFAULT_VALUE'] ?? [])
					: ($this->userField['VALUE'] ?? [])
			);
		}
		elseif(isset($this->additionalParameters['VALUE']))
		{
			$value = $this->additionalParameters['VALUE'];
		}
		elseif(isset($this->userField['FIELD_NAME']))
		{
			$value = Context::getCurrent()->getRequest()->get($this->userField['FIELD_NAME']);
		}

		return self::normalizeFieldValue($value);
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	final protected static function normalizeFieldValue($value): array
	{
		if(!is_array($value))
		{
			$value = array($value);
		}
		if(empty($value))
		{
			$value = array(null);
		}

		return $value;
	}

	/**
	 * @return HtmlBuilder
	 */
	final public function getHtmlBuilder()
	{
		if(!array_key_exists(static::getUserTypeId(), self::$htmlBuilder))
		{
			$this->setHtmlBuilder(new HtmlBuilder(static::getUserTypeId()));
		}

		return self::$htmlBuilder[static::getUserTypeId()];
	}

	/**
	 * @param HtmlBuilder $htmlBuilder
	 */
	final public function setHtmlBuilder(HtmlBuilder $htmlBuilder): void
	{
		self::$htmlBuilder[static::getUserTypeId()] = $htmlBuilder;
	}

	/**
	 * @return bool
	 */
	final public function isDefaultMode(): bool
	{
		return ($this->getMediaType() === static::MEDIA_TYPE_DEFAULT);
	}

	/**
	 * @return bool
	 */
	final public function isMobileMode(): bool
	{
		return ($this->getMediaType() === static::MEDIA_TYPE_MOBILE);
	}

	/**
	 * @return bool
	 */
	final public function isAjaxRequest(): bool
	{
		return Context::getCurrent()->getRequest()->isAjaxRequest();
	}

	/**
	 * @return string
	 */
	abstract protected static function getUserTypeId(): string;
}
