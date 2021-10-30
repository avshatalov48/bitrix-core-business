<?php
namespace Bitrix\Landing\Node;

class Type
{
	const COMPONENT = 'component';
	const MEDIA = 'embed';
	const ICON = 'icon';
	const IMAGE = 'img';
	const LINK = 'link';
	const MAP = 'map';
	const TEXT = 'text';
	const STYLE_IMAGE = 'styleimg';

	protected static $classes = [];

	/**
	 * Gets class handler for type of node.
	 * @param string $type Node type.
	 * @return string
	 */
	public static function getClassName(string $type): string
	{
		$type = mb_strtolower($type);

		if ($type === '' || $type === 'type')
		{
			throw new \Bitrix\Main\ArgumentTypeException(
				'Invalid node type'
			);
		}

		if (isset(self::$classes[$type]))
		{
			return self::$classes[$type];
		}

		$class = __NAMESPACE__ . '\\' . $type;

		// check custom classes
		$event = new \Bitrix\Main\Event(
			'landing',
			'onGetNodeClass',
			[
				'type' => $type
			]
		);
		$event->send();
		/** @var \Bitrix\Main\ORM\EventResult $result */
		foreach ($event->getResults() as $result)
		{
			if ($result->getType() != \Bitrix\Main\EventResult::ERROR)
			{
				if (
					($modified = $result->getModified()) &&
					isset($modified['class']) &&
					is_subclass_of($modified['class'], '\\Bitrix\\Landing\\Node')
				)
				{
					$class = $modified['class'];
				}
			}
		}

		self::$classes[$type] = $class;

		return self::$classes[$type];
	}
}