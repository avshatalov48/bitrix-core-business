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
	protected bool $hasHint = false;

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
			$this->hasHint = true;
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
		$output = parent::render($jsInit);
		$uniqId = $this->getUniqId();

		// execute in any case despite $jsInit
		if ($this->hasHint)
		{
			// HACK: hint does not work on locked buttons because mouse(enter/out) events don't work for disabled buttons. So we remove disabled attribute
			$output .=
				"<script>
				setTimeout(() => {
					const lockedButton = BX.UI.ButtonManager.getByUniqid('{$uniqId}');
					if (lockedButton && lockedButton.button)
					{
						lockedButton.button.removeAttribute('disabled');
					}
					
					BX.UI.Hint.init();
				}, 200);
			</script>";
		}


		return $output;
	}
}
