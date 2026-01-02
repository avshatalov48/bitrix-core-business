<?php
declare(strict_types=1);

namespace Bitrix\UI\Typography;

use InvalidArgumentException;

class Headline
{
	public static function render(
		string $size = 'md',
		bool $accent = false,
		string $tag = 'div',
		?string $align = null,
		?string $transform = null,
		?string $wrap = null,
		null|string|array $className = null,
		string $content = ''
	): string
	{
		self::validateSize($size);
		self::validateAlign($align);
		self::validateTransform($transform);
		self::validateWrap($wrap);

		$classes = self::buildClasses(
			'ui-headline',
			$size,
			$accent,
			$align,
			$transform,
			$wrap,
			$className
		);

		return self::buildTag($tag, $classes, $content);
	}

	protected static function validateSize(string $size): void
	{
		if (!in_array($size, ['xl', 'lg', 'md', 'sm', 'xs']))
		{
			throw new InvalidArgumentException("Invalid size: {$size}");
		}
	}

	protected static function validateAlign(?string $align): void
	{
		if ($align !== null && !in_array($align, ['left', 'center', 'right', 'justify']))
		{
			throw new InvalidArgumentException("Invalid align: {$align}");
		}
	}

	protected static function validateTransform(?string $transform): void
	{
		if ($transform !== null && !in_array($transform, ['uppercase', 'lowercase', 'capitalize']))
		{
			throw new InvalidArgumentException("Invalid transform: {$transform}");
		}
	}

	protected static function validateWrap(?string $wrap): void
	{
		if ($wrap !== null && !in_array($wrap, ['truncate', 'break-words', 'break-all']))
		{
			throw new InvalidArgumentException("Invalid wrap: {$wrap}");
		}
	}

	protected static function buildClasses(
		string $baseClass,
		string $size,
		bool $accent,
		?string $align,
		?string $transform,
		?string $wrap,
		null|string|array $className
	): string
	{
		$classes = [
			htmlspecialcharsbx($baseClass),
			'--' . htmlspecialcharsbx($size)
		];

		if ($accent)
		{
			$classes[] = '--accent';
		}

		if ($align !== null)
		{
			$classes[] = '--align-' . htmlspecialcharsbx($align);
		}

		if ($transform !== null)
		{
			$classes[] = '--' . htmlspecialcharsbx($transform);
		}

		if ($wrap !== null)
		{
			$classes[] = '--' . htmlspecialcharsbx($wrap);
		}

		if ($className !== null)
		{
			if (is_string($className))
			{
				$classes[] = htmlspecialcharsbx($className);
			}
			elseif (is_array($className))
			{
				foreach ($className as $class)
				{
					$classes[] = htmlspecialcharsbx((string)$class);
				}
			}
		}

		return implode(' ', $classes);
	}

	protected static function buildTag(string $tag, string $classes, string $content): string
	{
		return sprintf(
			'<%s class="%s">%s</%s>',
			htmlspecialcharsbx($tag),
			$classes,
			htmlspecialcharsbx($content),
			htmlspecialcharsbx($tag)
		);
	}
}
