<?php

namespace Bitrix\Main\Engine;

final class FallbackAction extends Action
{
	public const ACTION_NAME   = 'fallback';
	public const ACTION_METHOD = 'fallbackAction';

	/** @var string */
	private $originalActionName;

	public function __construct($name, Controller $controller, $config = [])
	{
		$this->originalActionName = $name;
		parent::__construct(self::ACTION_NAME, $controller, $config);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			$controller = $this->getController();
			$this->binder = AutoWire\Binder::buildForMethod($controller, self::ACTION_METHOD)
				->setSourcesParametersToMap([
					['actionName' => $this->originalActionName]
				])
			;
		}

		return $this;
	}
}