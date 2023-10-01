<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

class ConfigurationOption
{
	public const TYPE_DATE_TIME = 'datetime';
	public const TYPE_TIME = 'time';
	public const TYPE_NUMBER = 'number';
	public const TYPE_PRESET_STRING = 'preset-string';
	public const TYPE_CUSTOM = 'custom';
	public const TYPE_STRING = 'string';
	public const TYPE_CHECKBOX = 'checkbox';
	public const TYPE_CONSENT = 'user-consent';
	public const TYPE_CONSENT_CONTENT = 'user-consent-content';
	public const TYPE_EMAIL = 'email';
	public const TYPE_LIST = 'list';
	public const TYPE_HTML = 'html';
	public const TYPE_TEXT = 'text';
	public const TYPE_FILE = 'file';
	public const TYPE_TITLE = 'title';
	public const TYPE_TEMPLATE_TYPE = 'template-type';
	public const TYPE_TEMPLATE_ID = 'template-id';
	public const TYPE_MAIL_EDITOR = 'mail-editor';
	public const TYPE_AUDIO = 'audio';
	public const TYPE_SMS_EDITOR = 'sms-editor';
	public const TYPE_USER_LIST = 'user-list';
	public const GROUP_DEFAULT = 0;
	public const GROUP_ADDITIONAL = 1;

	/** @var string $type Type. */
	protected $type;

	/** @var string $code Code. */
	protected $code;

	/** @var string $view View. */
	protected $view;

	/** @var string $name Name. */
	protected $name;

	/** @var string|array $value Value. */
	protected $value;

	/** @var array $items Items. */
	protected $items = [];

	/** @var integer $group Group. */
	protected $group = self::GROUP_DEFAULT;

	/** @var null|string|array $hint Hint. */
	protected $hint;

	protected string|array|null $placeholder = null;

	/** @var boolean $required Required. */
	protected $required = false;

	/** @var boolean $templated Templated. */
	protected $templated = false;

	/** @var callable|null $readonlyView Render readonly value. */
	protected $readonlyView;

	/** @var boolean $showInList Show option value in items list. */
	protected $showInList = false;

	/** @var boolean $showInFilter Show option value in filter. */
	protected $showInFilter = false;

	/** @var boolean $showPreview Show preview button in consent. */
	protected $showPreview = false;

	/** @var boolean $showHelper Show helper block in consent. */
	protected $showHelper = false;

	/** @var int $maxLength max length of string field */
	protected $maxLength;

	/** @var int $maxValue max value of the field */
	protected $maxValue;

	/** @var int $minValue min value of te string field */
	protected $minValue;

	/**
	 * Configuration constructor.
	 * @param array $data Data.
	 */
	public function __construct(array $data = [])
	{
		if (isset($data['type']))
		{
			$this->setType($data['type']);
		}
		if (isset($data['code']))
		{
			$this->setCode($data['code']);
		}
		if (isset($data['name']))
		{
			$this->setName($data['name']);
		}
		if (isset($data['view']))
		{
			$this->setView($data['view']);
		}
		if (isset($data['value']))
		{
			$this->setValue($data['value']);
		}
		if (isset($data['group']))
		{
			$this->setGroup($data['group']);
		}
		if (isset($data['items']))
		{
			$this->setItems($data['items']);
		}
		if (isset($data['required']))
		{
			$this->setRequired($data['required']);
		}
		if (isset($data['templated']))
		{
			$this->setTemplated($data['templated']);
		}
		if (isset($data['hint']))
		{
			$this->setHint($data['hint']);
		}
		if (isset($data['placeholder']))
		{
			$this->setPlaceholder($data['placeholder']);
		}
		if (isset($data['readonly_view']))
		{
			$this->setReadonlyView($data['readonly_view']);
		}
		if (isset($data['show_in_list']))
		{
			$this->setShowInList($data['show_in_list']);
		}
		if (isset($data['show_in_filter']))
		{
			$this->setShowInFilter($data['show_in_filter']);
		}
		if (isset($data['show_preview']))
		{
			$this->setShowPreview($data['show_preview']);
		}
		if (isset($data['show_helper']))
		{
			$this->setShowHelper($data['show_helper']);
		}
		if (isset($data['max_length']))
		{
			$this->setMaxLength($data['max_length']);
		}
		if (isset($data['max_value']))
		{
			$this->setMaxValue($data['max_value']);
		}
		if (isset($data['min_value']))
		{
			$this->setMinValue($data['min_value']);
		}
	}

	/**
	 * Get as array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return [
			'type' => $this->getType(),
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'view' => $this->getView(),
			'value' => $this->getValue(),
			'group' => $this->getGroup(),
			'items' => $this->getItems(),
			'required' => $this->isRequired(),
			'templated' => $this->isTemplated(),
			'hint' => $this->getHint(),
			'placeholder' => $this->getPlaceholder(),
			'max_length' => $this->getMaxLength(),
			'min_value' => $this->getMinValue(),
			'max_value' => $this->getMaxValue(),
			'show_in_list' => $this->getShowInList(),
			'show_preview' => $this->getShowPreview(),
			'show_helper' => $this->getShowHelper(),
		];
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set type.
	 *
	 * @param string $type Type.
	 * @return void
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set code.
	 *
	 * @param string $code Code.
	 * @return void
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * Get view.
	 *
	 * @return string|callable
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Set view.
	 *
	 * @param string|callable $view View.
	 * @return void
	 */
	public function setView($view)
	{
		$this->view = $view;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string $name Name.
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get value.
	 *
	 * @return string|array
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Has value.
	 *
	 * @return bool
	 */
	public function hasValue()
	{
		return is_array($this->value) ? count($this->value) > 0 : (bool)$this->value;
	}

	/**
	 * Set value.
	 *
	 * @param string|array $value Value.
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Get group.
	 *
	 * @return integer
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Set value.
	 *
	 * @param integer $group Group.
	 * @return void
	 */
	public function setGroup($group)
	{
		$this->group = $group;
	}

	/**
	 * Get items.
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Set items.
	 *
	 * @param array $items Items.
	 * @return void
	 */
	public function setItems(array $items)
	{
		if (!is_numeric(implode('', array_keys($items))))
		{
			$this->items = [];
			foreach ($items as $code => $value)
			{
				$this->items[] = ['code' => $code, 'value' => $value];
			}
		}
		else
		{
			$this->items = $items;
		}
	}

	/**
	 * Is required.
	 *
	 * @return boolean
	 */
	public function isRequired()
	{
		return $this->required;
	}

	/**
	 * Set required.
	 *
	 * @param boolean $required Required.
	 * @return void
	 */
	public function setRequired($required)
	{
		$this->required = (bool)$required;
	}

	/**
	 * Is templated.
	 *
	 * @return boolean
	 */
	public function isTemplated()
	{
		return $this->templated;
	}

	/**
	 * Set required.
	 *
	 * @param boolean $templated Templated.
	 * @return void
	 */
	public function setTemplated($templated)
	{
		$this->templated = (bool)$templated;
	}

	/**
	 * Get hint.
	 *
	 * @return null|string|array
	 */
	public function getHint()
	{
		return $this->hint;
	}

	/**
	 * Set required.
	 *
	 * @param null|string|array $hint Hint.
	 * @return void
	 */
	public function setHint($hint)
	{
		$this->hint = $hint;
	}


	/**
	 * Get readonly view.
	 *
	 * @param mixed $value Option value
	 * @return mixed
	 */
	public function getReadonlyView($value)
	{
		if (is_callable($this->readonlyView))
		{
			$callback = $this->readonlyView;
			$value = $callback($value);
		}
		return $value;
	}

	/**
	 * @return int
	 */
	public function getMaxLength()
	{
		return $this->maxLength;
	}


	/**
	 * Get show in list or not.
	 *
	 * @return bool
	 */
	public function getShowInList()
	{
		return $this->showInList;
	}

	/**
	 * Get show in filter or not.
	 *
	 * @return bool
	 */
	public function getShowInFilter()
	{
		return $this->showInFilter;
	}

	/**
	 * Set readonly view callback.
	 *
	 * @param callable|null $readonlyView Readonly view callback.
	 * @return void
	 */
	public function setReadonlyView($readonlyView)
	{
		$this->readonlyView = $readonlyView;
	}

	/**
	 * Set show in list or not.
	 *
	 * @param boolean $showInList Show in items list.
	 * @return void
	 */
	public function setShowInList($showInList)
	{
		$this->showInList = $showInList;
	}

	/**
	 * Set show in list or not.
	 *
	 * @param boolean $showInFilter Show in filter.
	 * @return void
	 */
	public function setShowInFilter($showInFilter)
	{
		$this->showInFilter = $showInFilter;
	}

	/**
	 * @param int $maxLength
	 */
	public function setMaxLength(int $maxLength)
	{
		$this->maxLength = $maxLength;
	}

	/**
	 * @return int
	 */
	public function getMaxValue()
	{
		return $this->maxValue;
	}

	/**
	 * @param int $maxValue
	 *
	 * @return ConfigurationOption
	 */
	public function setMaxValue(int $maxValue)
	{
		$this->maxValue = $maxValue;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMinValue()
	{
		return $this->minValue;
	}

	/**
	 * @param int $minValue
	 *
	 * @return ConfigurationOption
	 */
	public function setMinValue(int $minValue)
	{
		$this->minValue = $minValue;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getShowPreview(): bool
	{
		return $this->showPreview;
	}

	/**
	 * @param bool $showPreview
	 * @return ConfigurationOption
	 */
	public function setShowPreview(bool $showPreview): ConfigurationOption
	{
		$this->showPreview = $showPreview;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getShowHelper(): bool
	{
		return $this->showHelper;
	}

	/**
	 * @param bool $showHelper
	 * @return ConfigurationOption
	 */
	public function setShowHelper(bool $showHelper): ConfigurationOption
	{
		$this->showHelper = $showHelper;
		return $this;
	}

	private function getPlaceholder(): array|string|null
	{
		return $this->placeholder;
	}

	private function setPlaceholder(mixed $placeholder): void
	{
		$this->placeholder = $placeholder;
	}

}
