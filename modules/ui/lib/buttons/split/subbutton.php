<?php
namespace Bitrix\UI\Buttons\Split;

use Bitrix\Main\NotSupportedException;
use Bitrix\UI\Buttons;

class SubButton extends Buttons\BaseButton
{
	protected $buttonType = Type::MAIN;
	/** @var Button */
	protected $splitButton;

	protected function init(array $params = [])
	{
		$this->removeClass($this->getBaseClass());

		if (!empty($params['buttonType']))
		{
			$this->buttonType = $params['buttonType'] === Type::MAIN? Type::MAIN : Type::MENU;
		}

		$this->baseClass = $this->buttonType;
		parent::init($params);

		if ($this->isInputType())
		{
			throw new NotSupportedException("Split button cannot be an input tag.");
		}
	}

	public static function getJsClass()
	{
		return 'BX.UI.SplitSubButton';
	}

	protected function renderJavascript()
	{
		return "";
	}

	protected function renderInner()
	{
		return $this->isMenuButton() ? '' : parent::renderInner();
	}

	/**
	 * @return bool
	 */
	public function isMainButton()
	{
		return $this->getButtonType() === Type::MAIN;
	}

	/**
	 * @return bool
	 */
	public function isMenuButton()
	{
		return $this->getButtonType() === Type::MENU;
	}

	/**
	 * @return string
	 */
	public function getButtonType()
	{
		return $this->buttonType;
	}

	/**
	 * @return Button
	 */
	public function getSplitButton()
	{
		return $this->splitButton;
	}

	/**
	 * @param Button $splitButton
	 *
	 * @return SubButton
	 */
	public function setSplitButton($splitButton)
	{
		$this->splitButton = $splitButton;

		return $this;
	}

	/**
	 * @param bool $flag
	 *
	 * @return static
	 */
	public function setActive($flag = true)
	{
		return $this->toggleState($flag, State::ACTIVE, State::MAIN_ACTIVE, State::MENU_ACTIVE);
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		$state = $this->getSplitButton()->getState();
		if ($state === State::ACTIVE)
		{
			return true;
		}

		if ($this->isMainButton())
		{
			return $state === State::MAIN_ACTIVE;
		}
		else
		{
			return $state === State::MENU_ACTIVE;
		}
	}

	/**
	 * @param bool $flag
	 *
	 * @return static
	 */
	public function setDisabled($flag = true)
	{
		$this->toggleState($flag, State::DISABLED, State::MAIN_DISABLED, State::MENU_DISABLED);

		return parent::setDisabled($flag);
	}

	/**
	 * @param bool $flag
	 *
	 * @return static
	 */
	public function setHovered($flag = true)
	{
		return $this->toggleState($flag, State::HOVER, State::MAIN_HOVER, State::MENU_HOVER);;
	}

	/**
	 * @return bool
	 */
	public function isHovered()
	{
		$state = $this->getSplitButton()->getState();
		if ($state === State::HOVER)
		{
			return true;
		}

		if ($this->isMainButton())
		{
			return $state === State::MAIN_HOVER;
		}
		else
		{
			return $state === State::MENU_HOVER;
		}
	}

	/**
	 * @param $flag
	 * @param $globalState
	 * @param $mainState
	 * @param $menuState
	 *
	 * @return static
	 */
	public function toggleState($flag, $globalState, $mainState, $menuState)
	{
		$state = $this->getSplitButton()->getState();
		if ($flag === false)
		{
			if ($state === $globalState)
			{
				$this->getSplitButton()->setState($this->isMainButton()? $menuState : $mainState);
			}
			else
			{
				$this->getSplitButton()->setState(null);
			}
		}
		else
		{
			if ($state === $mainState && $this->isMenuButton())
			{
				$this->getSplitButton()->setState($globalState);
			}
			else if ($state === $menuState && $this->isMainButton())
			{
				$this->getSplitButton()->setState($globalState);
			}
			else if ($state !== $globalState)
			{
				$this->getSplitButton()->setState($this->isMainButton()? $mainState : $menuState);
			}
		}

		return $this;
	}
}