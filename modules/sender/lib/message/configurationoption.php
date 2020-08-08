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
	const TYPE_DATE_TIME = 'datetime';
	const TYPE_NUMBER = 'number';
	const TYPE_CUSTOM = 'custom';
	const TYPE_PRESET_STRING = 'preset-string';
	const TYPE_STRING = 'string';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_EMAIL = 'email';
	const TYPE_LIST = 'list';
	const TYPE_HTML = 'html';
	const TYPE_TEXT = 'text';
	const TYPE_FILE = 'file';
	const TYPE_TEMPLATE_TYPE = 'template-type';
	const TYPE_TEMPLATE_ID = 'template-id';
	const TYPE_MAIL_EDITOR = 'mail-editor';
	const TYPE_AUDIO = 'audio';
	const TYPE_SMS_EDITOR = 'sms-editor';
	const TYPE_USER_LIST = 'user-list';

	const GROUP_DEFAULT = 0;
	const GROUP_ADDITIONAL = 1;

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
	protected $items = array();

	/** @var integer $group Group. */
	protected $group = self::GROUP_DEFAULT;

	/** @var null|string|array $hint Hint. */
	protected $hint;

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

	/** @var int $maxLength max length of string field */
	protected $maxLength;

	/**
	 * Configuration constructor.
	 * @param array $data Data.
	 */
	public function __construct(array $data = array())
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
		if (isset($data['max_length']))
		{
			$this->setMaxLength($data['max_length']);
		}
	}

	/**
	 * Get as array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		return array(
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
			'max_length' => $this->getMaxLength(),
		);
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
		return is_array($this->value) ? count($this->value) > 0 : !!$this->value;
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
			$this->items = array();
			foreach ($items as $code => $value)
			{
				$this->items[] = array('code' => $code, 'value' => $value);
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
		$this->required = (bool) $required;
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
		$this->templated = (bool) $templated;
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

}