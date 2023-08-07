<?php

namespace Bitrix\Main\Grid\Row\Action;

/**
 * Single item for actions of row.
 *
 * @see \Bitrix\Main\Grid\Row\Action\MenuAction for menu control.
 */
abstract class BaseAction implements Action
{
	/**
	 * If is `true`, action execute when row is double-clicked.
	 *
	 * @var bool|null
	 */
	protected ?bool $default;
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
	 * URL to action.
	 *
	 * For JS code see `onclick` property.
	 *
	 * @var string|null
	 */
	protected ?string $href;
	/**
	 * JS code for onclick event.
	 *
	 * For link see `href` property.
	 *
	 * @var string|null
	 */
	protected ?string $onclick;

	/**
	 * Control text.
	 *
	 * @return string
	 */
	abstract protected function getText(): string;

	/**
	 * @inheritDoc
	 */
	public function getControl(array $rawFields): ?array
	{
		$result = [
			'TEXT' => $this->getText(),
		];

		$propertyMap = [
			'default' => 'DEFAULT',
			'className' => 'ICONCLASS',
			'title' => 'TITLE',
			'href' => 'HREF',
			'onclick' => 'ONCLICK',
		];
		foreach ($propertyMap as $propertyName => $actionName)
		{
			$propertyValue = $this->{$propertyName} ?? null;
			if (isset($propertyValue))
			{
				$result[$actionName] = $propertyValue;
			}
		}

		return $result;
	}
}
