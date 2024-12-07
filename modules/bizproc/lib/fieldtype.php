<?php
namespace Bitrix\Bizproc;

use Bitrix\Bizproc\BaseType\Base;
use Bitrix\Main;

/**
 * Class FieldType
 * @package Bitrix\Bizproc
 */
class FieldType
{

	/**
	 * Base type BOOL
	 */
	public const BOOL = 'bool';

	/**
	 * Base type DATE
	 */
	public const DATE = 'date';

	/**
	 * Base type DATETIME
	 */
	public const DATETIME = 'datetime';

	/**
	 * Base type DOUBLE
	 */
	public const DOUBLE = 'double';

	/**
	 * Base type FILE
	 */
	public const FILE = 'file';

	/**
	 * Base type INT
	 */
	public const INT = 'int';

	/**
	 * Base type SELECT
	 */
	public const SELECT = 'select';

	/**
	 * Base type INTERNALSELECT
	 */
	public const INTERNALSELECT = 'internalselect';

	/**
	 * Base type STRING
	 */
	public const STRING = 'string';

	/**
	 * Base type TEXT
	 */
	public const TEXT = 'text';

	/**
	 * Base type USER
	 */
	public const USER = 'user';

	/**
	 * Base type TIME
	 */
	public const TIME = 'time';

	/**
	 * Control render mode - Bizproc Designer
	 */
	public const RENDER_MODE_DESIGNER = 1;

	/**
	 * Control render mode - Admin panel
	 */
	public const RENDER_MODE_ADMIN = 2;

	/**
	 * Control render mode - Mobile application
	 */
	public const RENDER_MODE_MOBILE = 4;

	/**
	 * Control render mode - Automation designer
	 */
	public const RENDER_MODE_PUBLIC = 8;

	/**
	 * Control render mode - Native mobile controls
	 */
	public const RENDER_MODE_JN_MOBILE = 16;

	public const VALUE_CONTEXT_DOCUMENT = 'Document';
	public const VALUE_CONTEXT_REST = 'rest';
	public const VALUE_CONTEXT_JN_MOBILE = 'jn_mobile';

	/** @var \Bitrix\Bizproc\BaseType\Base $typeClass */
	protected $typeClass;

	/** @var array $property */
	protected $property;

	/** @var array $documentType */
	protected $documentType;

	/** @var array $documentId */
	protected $documentId;

	/**
	 * @param array $property Document property.
	 * @param array $documentType Document type.
	 * @param string $typeClass Type class manager.
	 * @param null|array $documentId
	 */
	public function __construct(array $property, array $documentType, $typeClass, array $documentId = null)
	{
		$this->property = static::normalizeProperty($property);
		$this->documentType = $documentType;

		$this->typeClass = $typeClass;
		$this->documentId = $documentId;
	}

	/**
	 * @return array
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * @return null|string
	 */
	public function getType()
	{
		return isset($this->property['Type']) ? $this->property['Type'] : null;
	}

	/**
	 * @return string
	 */
	public function getBaseType()
	{
		$class = $this->typeClass;
		return $class::getType();
	}

	/**
	 * @return string
	 */
	public function getTypeClass()
	{
		return $this->typeClass;
	}

	/**
	 * Set type class manager.
	 * @param string $typeClass Type class name.
	 * @return $this
	 * @throws Main\ArgumentException
	 */
	public function setTypeClass($typeClass)
	{
		if (is_subclass_of($typeClass, '\Bitrix\Bizproc\BaseType\Base'))
		{
			$this->typeClass = $typeClass;
		}
		else
			throw new Main\ArgumentException('Incorrect type class.');

		return $this;
	}

	/**
	 * @return array
	 */
	public function getDocumentType()
	{
		return $this->documentType;
	}

	/**
	 * @param array $documentType Document type.
	 * @return $this
	 */
	public function setDocumentType(array $documentType)
	{
		$this->documentType = $documentType;
		return $this;
	}

	/**
	 * @return array|null
	 */
	public function getDocumentId()
	{
		return $this->documentId;
	}

	/**
	 * @param array $documentId Document id.
	 * @return $this
	 */
	public function setDocumentId(array $documentId)
	{
		$this->documentId = $documentId;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isMultiple()
	{
		return !empty($this->property['Multiple']);
	}

	/**
	 * @param bool $value Multiple flag.
	 * @return $this
	 */
	public function setMultiple($value)
	{
		$this->property['Multiple'] = (bool)$value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRequired()
	{
		return !empty($this->property['Required']);
	}

	/**
	 * @return null|mixed
	 */
	public function getOptions()
	{
		return isset($this->property['Options']) ? $this->property['Options'] : null;
	}

	/**
	 * @param mixed $options Options data.
	 * @return $this
	 */
	public function setOptions($options)
	{
		$this->property['Options'] = $options;
		return $this;
	}

	/**
	 * Get field settings.
	 * Settings contain additional information that may be required for rendering of field.
	 * @return array
	 */
	public function getSettings()
	{
		return isset($this->property['Settings']) && is_array($this->property['Settings'])
			? $this->property['Settings'] : array();
	}

	/**
	 * set field settings.
	 * Settings contain additional information that may be required for rendering of field.
	 * @param array $settings Settings array.
	 * @return $this
	 */
	public function setSettings(array $settings)
	{
		$this->property['Settings'] = $settings;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getName()
	{
		return $this->property['Name'];
	}

	/**
	 * @return null|string
	 */
	public function getDescription()
	{
		return $this->property['Description'];
	}

	/**
	 * @param mixed $value Field value.
	 * @param string $format Format name.
	 * @return mixed|null|string
	 */
	public function formatValue($value, $format = 'printable')
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			return $typeClass::formatValueMultiple($this, $value, $format);
		}
		else
		{
			return $typeClass::formatValueSingle($this, $value, $format);
		}
	}

	/**
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return array|bool|float|int|string
	 */
	public function convertValue($value, $toTypeClass)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			return $typeClass::convertValueMultiple($this, $value, $toTypeClass);
		}
		else
		{
			return $typeClass::convertValueSingle($this, $value, $toTypeClass);
		}
	}

	/**
	 * @param array $baseValue Base value.
	 * @param mixed $appendValue Value to append.
	 * @return mixed Merge result.
	 */
	public function mergeValue($baseValue, $appendValue): array
	{
		$typeClass = $this->typeClass;
		$baseValue = (array) $baseValue;

		if ($this->isMultiple() && !\CBPHelper::isEmptyValue($appendValue))
		{
			return $typeClass::mergeValue($this, $baseValue, $appendValue);
		}

		return $baseValue;
	}

	/**
	 * @param int $renderMode Control render mode.
	 * @return bool
	 */
	public function canRenderControl($renderMode)
	{
		$typeClass = $this->typeClass;
		return $typeClass::canRenderControl($renderMode);
	}

	/**
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public function renderControl(array $field, $value, $allowSelection, $renderMode)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			return $typeClass::renderControlMultiple($this, $field, $value, $allowSelection, $renderMode);
		}
		else
		{
			return $typeClass::renderControlSingle($this, $field, $value, $allowSelection, $renderMode);
		}
	}

	/**
	 * @param string $callbackFunctionName Client callback function name.
	 * @param mixed $value Field value.
	 * @return string
	 */
	public function renderControlOptions($callbackFunctionName, $value)
	{
		$typeClass = $this->typeClass;
		return $typeClass::renderControlOptions($this, $callbackFunctionName, $value);
	}

	/**
	 * @param array $field Form field.
	 * @param array $request Request data.
	 * @param array &$errors Errors collection.
	 * @return array|null
	 */
	public function extractValue(array $field, array $request, array &$errors = null)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			$result = $typeClass::extractValueMultiple($this, $field, $request);
		}
		else
		{
			$result = $typeClass::extractValueSingle($this, $field, $request);
		}
		$errors = $typeClass::getErrors();

		return $result;
	}

	/**
	 * @param mixed $value Field value.
	 * @return void
	 */
	public function clearValue($value)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			$typeClass::clearValueMultiple($this, $value);
		}
		else
		{
			$typeClass::clearValueSingle($this, $value);
		}
	}

	/**
	 * @param string $context Context identification (Document, Variable etc.)
	 * @param mixed $value Field value.
	 * @return mixed
	 */
	public function internalizeValue($context, $value)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			return $typeClass::internalizeValueMultiple($this, $context, $value);
		}
		else
		{
			return $typeClass::internalizeValueSingle($this, $context, $value);
		}
	}

	/**
	 * @param string $context Context identification (Document, Variable etc.)
	 * @param mixed $value Field value.
	 * @return mixed
	 */
	public function externalizeValue($context, $value)
	{
		$typeClass = $this->typeClass;

		if ($this->isMultiple())
		{
			return $typeClass::externalizeValueMultiple($this, $context, $value);
		}
		else
		{
			return $typeClass::externalizeValueSingle($this, $context, $value);
		}
	}

	/**
	 * Get list of supported base types.
	 * @return array
	 */
	public static function getBaseTypesMap()
	{
		return array(
			static::BOOL => BaseType\BoolType::class,
			static::DATE => BaseType\Date::class,
			static::DATETIME => BaseType\Datetime::class,
			static::DOUBLE => BaseType\Double::class,
			static::FILE => BaseType\File::class,
			static::INT => BaseType\IntType::class,
			static::SELECT => BaseType\Select::class,
			static::STRING => BaseType\StringType::class,
			static::TEXT => BaseType\Text::class,
			static::USER => BaseType\User::class,
			static::INTERNALSELECT => BaseType\InternalSelect::class,
			static::TIME => BaseType\Time::class,
		);
	}

	public static function isBaseType(string $type): bool
	{
		return array_key_exists($type, static::getBaseTypesMap());
	}

	public static function convertUfType(string $type): ?string
	{
		$bpType = null;
		switch ($type)
		{
			case 'string':
			case 'datetime':
			case 'date':
			case 'double':
			case 'file':
				$bpType = $type;
				break;
			case 'integer':
				$bpType = 'int';
				break;
			case 'boolean':
				$bpType = 'bool';
				break;
			case 'employee':
				$bpType = 'user';
				break;
			case 'enumeration':
				$bpType = 'select';
				break;
			case 'money':
			case 'url':
			case 'address':
			case 'resourcebooking':
			case 'crm_status':
			case 'iblock_section':
			case 'iblock_element':
			case 'crm':
				$bpType = "UF:{$type}";
				break;
		}

		return $bpType;
	}

	/**
	 * Normalize property structure.
	 * @param string|array $property Document property.
	 * @return array
	 */
	public static function normalizeProperty($property)
	{
		$normalized = [
			'Id' => null,
			'Type' => null,

			'Name' => null,
			'Description' => null,

			'Multiple' => false,
			'Required' => false,

			'Options' => null,
			'Settings' => null,
			'Default' => null,
		];

		if (!is_array($property))
		{
			$normalized['Type'] = (string) $property;

			return $normalized;
		}

		foreach ($property as $key => $val)
		{
			switch(mb_strtoupper($key))
			{
				case 'TYPE':
				case '0':
					$normalized['Type'] = (string)$val;
					break;
				case 'MULTIPLE':
				case '1':
					$normalized['Multiple'] = \CBPHelper::getBool($val);
					break;
				case 'REQUIRED':
				case '2':
					$normalized['Required'] = \CBPHelper::getBool($val);
					break;
				case 'OPTIONS':
				case '3':
					$normalized['Options'] = is_array($val)? $val : (string)$val;
					break;
				case 'SETTINGS':
					$normalized['Settings'] = is_array($val) ? $val : null;
					break;
				case 'ID':
					$normalized['Id'] = (string)$val;
					break;
				case 'NAME':
				case 'TITLE':
					$normalized['Name'] = (string)$val;
					break;
				case 'DESCRIPTION':
					$normalized['Description'] = (string)$val;
					break;
				case 'DEFAULT':
					$normalized['Default'] = $val;
					break;
			}
		}

		return $normalized;
	}

	public static function normalizePropertyList(array $props): array
	{
		$normalized = [];
		foreach ($props as $id => $item)
		{
			$item['Id'] ??= $id;
			$normalized[] = static::normalizeProperty($item);
		}

		return $normalized;
	}

	/**
	 * @param $value
	 * @return void
	 */
	public function setValue($value)
	{
		$typeClass = $this->typeClass;

		$this->property['Default'] =
			($this->isMultiple())
				? $typeClass::validateValueMultiple($value, $this)
				: $typeClass::validateValueSingle($value, $this)
		;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->property['Default'];
	}

	public function convertPropertyToView(int $viewMode): array
	{
		$typeClass = $this->typeClass;

		return $typeClass::convertPropertyToView($this, $viewMode, $this->getProperty());
	}
}