<?php

namespace Bitrix\UI\Buttons;

class Button extends BaseButton
{
	/** @var array  */
	protected $properties = [];

	protected function init(array $params = [])
	{
		$this->setColor(Color::SUCCESS);

		if (isset($params['baseClassName']))
		{
			$this->baseClass = $params['baseClassName'];
		}

		parent::init($params);
	}

	/**
	 * @param $value
	 * @param $enum
	 *
	 * @return bool
	 */
	protected function isEnumValue($value, $enum)
	{
		try
		{
			return in_array($value, (new \ReflectionClass($enum))->getConstants(), true);
		}
		catch (\ReflectionException $e)
		{}

		return false;
	}

	/**
	 * @param $propertyName
	 * @param $value
	 * @param $enum
	 *
	 * @return $this
	 */
	protected function setProperty($propertyName, $value, $enum)
	{
		if ($this->isEnumValue($value, $enum))
		{
			$this
				->removeClass($this->getProperty($propertyName))
				->addClass($value)
			;
			$this->properties[$propertyName] = $value;
		}
		elseif ($value === null)
		{
			$this->removeClass($this->getProperty($propertyName));
			$this->properties[$propertyName] = $value;
		}

		return $this;
	}

	/**
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return mixed|null
	 */
	protected function getProperty($name, $defaultValue = null)
	{
		if (isset($this->properties[$name]))
		{
			return $this->properties[$name];
		}

		return $defaultValue;
	}

	protected function buildFromArray($params)
	{
		parent::buildFromArray($params);

		if (isset($params['color']))
		{
			$this->setColor($params['color']);
		}

		if (isset($params['icon']))
		{
			$this->setIcon($params['icon']);
		}

		if (isset($params['state']))
		{
			$this->setState($params['state']);
		}

		if (isset($params['size']))
		{
			$this->setSize($params['size']);
		}

		if (isset($params['menu']))
		{
			$this->setMenu($params['menu']);
		}

		if (isset($params['noCaps']))
		{
			$this->setNoCaps($params['noCaps']);
		}

		if (isset($params['round']))
		{
			$this->setRound($params['round']);
		}

		if (!empty($params['dropdown']) || (isset($params['menu']) && $params['dropdown'] !== false))
		{
			$this->setDropdown();
		}
		elseif (array_key_exists('dropdown', $params) && $params['dropdown'] === false)
		{
			$this->getAttributeCollection()->addJsonOption('dropdown', false);
		}
	}

	public function setIcon($icon)
	{
		return $this->setProperty('icon', $icon, Icon::class);
	}

	public function getIcon()
	{
		return $this->getProperty('icon');
	}

	/**
	 * @param string $color
	 * @see Color
	 *
	 * @return Button
	 */
	public function setColor($color)
	{
		return $this->setProperty('color', $color, Color::class);
	}

	/**
	 * @return string|null
	 */
	public function getColor()
	{
		return $this->getProperty('color');
	}

	/**
	 * @param $size
	 *
	 * @return Button
	 * @see Size
	 *
	 */
	public function setSize($size)
	{
		return $this->setProperty('size', $size, Size::class);
	}

	/**
	 * @return string|null
	 */
	public function getSize()
	{
		return $this->getProperty('size');
	}

	/**
	 * @param string $state
	 * @see State
	 *
	 * @return Button
	 */
	public function setState($state)
	{
		return $this->setProperty('state', $state, State::class);
	}

	/**
	 * @return bool
	 */
	public function getState()
	{
		return $this->getProperty('state');
	}

	/**
	 * @param bool $flag
	 *
	 * @return $this
	 */
	public function setActive($flag = true)
	{
		if ($flag)
		{
			$this->setState(State::ACTIVE);
		}
		else
		{
			$this->setState(null);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->getState() === State::ACTIVE;
	}

	/**
	 * @param bool $flag
	 *
	 * @return $this
	 */
	public function setHovered($flag = true)
	{
		if ($flag)
		{
			$this->setState(State::HOVER);
		}
		else
		{
			$this->setState(null);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHover()
	{
		return $this->getState() === State::HOVER;
	}

	/**
	 * @param bool $flag
	 *
	 * @return BaseButton
	 */
	public function setDisabled($flag = true)
	{
		if ($flag)
		{
			$this->setState(State::DISABLED);
		}
		else
		{
			$this->setState(null);
		}

		return parent::setDisabled($flag);
	}

	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->getState() === State::DISABLED;
	}

	/**
	 * @param bool $flag
	 *
	 * @return $this
	 */
	public function setWaiting($flag = true)
	{
		if ($flag)
		{
			$this->setState(State::WAITING);
			parent::setDisabled(true);
		}
		else
		{
			$this->setState(null);
			parent::setDisabled(false);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isWaiting()
	{
		return $this->getState() === State::WAITING;
	}

	/**
	 * @param bool $flag
	 *
	 * @return $this
	 */
	public function setClocking($flag = true)
	{
		if ($flag)
		{
			$this->setState(State::CLOCKING);
			parent::setDisabled(true);
		}
		else
		{
			$this->setState(null);
			parent::setDisabled(false);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isClocking()
	{
		return $this->getState() === State::CLOCKING;
	}

	/**
	 * @param bool $flag
	 * @return static
	 */
	public function setNoCaps($flag = true)
	{
		if ($flag === false)
		{
			$this->removeClass(Style::NO_CAPS);
		}
		else
		{
			$this->addClass(Style::NO_CAPS);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNoCaps()
	{
		return $this->hasClass(Style::NO_CAPS);
	}

	/**
	 * @param bool $flag
	 * @return static
	 */
	public function setRound($flag = true)
	{
		if ($flag === false)
		{
			$this->removeClass(Style::ROUND);
		}
		else
		{
			$this->addClass(Style::ROUND);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRound()
	{
		return $this->hasClass(Style::ROUND);
	}

	/**
	 * @param bool $flag
	 * @return static
	 */
	public function setDropdown($flag = true)
	{
		if ($flag === false)
		{
			$this->removeClass(Style::DROPDOWN);
		}
		else
		{
			$this->addClass(Style::DROPDOWN);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDropdown()
	{
		return $this->hasClass(Style::DROPDOWN);
	}

	/**
	 * @param bool $flag
	 * @return static
	 */
	public function setCollapsed($flag = true)
	{
		if ($flag === false)
		{
			$this->removeClass(Style::COLLAPSED);
		}
		else
		{
			$this->addClass(Style::COLLAPSED);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isCollapsed()
	{
		return $this->hasClass(Style::COLLAPSED);
	}

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setMenu($options)
	{
		$this->getAttributeCollection()->addJsonOption('menu', $options);

		return $this;
	}
}