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
	const TYPE_SMS_EDITOR = 'sms-editor';

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
	 */
	public function setHint($hint)
	{
		$this->hint = $hint;
	}
}