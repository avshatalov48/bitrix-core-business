<?php
namespace Bitrix\UI\Buttons;

/**
 * Locked button.
 *
 * May be used for locked or access denied functional.
 * Automatically registers `ui.hint` extension and append init js code.
 */
class LockedButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'tag' => Tag::BUTTON,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function init(array $params = [])
	{
		parent::init($params);

		$this->setDisabled();

		$this->getAttributeCollection()->addClass('ui-btn-icon-lock');

		$hint = $params['hint'] ?? null;
		if ($hint)
		{
			$this->addDataAttribute('hint', $hint);
			$this->addDataAttribute('hint-no-icon', true);
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function listExtensions()
	{
		return [
			'ui.hint',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function render($jsInit = true)
	{
		$ouput = parent::render($jsInit);

		// execute in any case despite $jsInit
		$ouput .= "<script>BX.UI.Hint.init();</script>";

		return $ouput;
	}
}
