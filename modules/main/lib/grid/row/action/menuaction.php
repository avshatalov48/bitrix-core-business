<?php

namespace Bitrix\Main\Grid\Row\Action;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

/**
 * Menu item for actions of row.
 *
 * @see \Bitrix\Main\Grid\Row\Action\BaseItem for single control.
 */
abstract class MenuAction implements Action
{
	/**
	 * CSS class name.
	 *
	 * @var string|null
	 */
	protected ?string $className;
	/**
	 * Tooltip title.
	 *
	 * @var string|null
	 */
	protected ?string $title;

	/**
	 * @inheritDoc
	 */
	final public static function getId(): ?string
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	final public function processRequest(HttpRequest $request): ?Result
	{
		return null;
	}

	/**
	 * Control text.
	 *
	 * @return string
	 */
	abstract protected function getText(): string;

	/**
	 * @return Action[]
	 */
	abstract protected function getMenu(): array;

	/**
	 * @inheritDoc
	 */
	public function getControl(array $rawFields): array
	{
		$result = [
			'TEXT' => $this->getText(),
			'MENU' => [],
		];

		if (isset($this->className))
		{
			$result['ICONCLASS'] = $this->className;
		}

		if (isset($this->title))
		{
			$result['TITLE'] = $this->title;
		}

		foreach ($this->getMenu() as $subAction)
		{
			if ($subAction instanceof Action)
			{
				$control = $subAction->getControl($rawFields);
				if (isset($control))
				{
					$result['MENU'][] = $control;
				}
			}
		}

		return $result;
	}
}
