<?php

namespace Bitrix\Main\Web\DOM;

class DisplayProperties
{
	const DISPLAY = 'display';
	const FONT = 'font';

	const DISPLAY_HIDDEN = 'hidden';
	const DISPLAY_BLOCK = 'block';
	const DISPLAY_INLINE = 'inline';

	const FONT_NORMAL = 'normal';
	const FONT_BOLD = 'bold';
	const FONT_ITALIC = 'italic';
	const FONT_UNDERLINED = 'underlined';
	const FONT_DELETED = 'deleted';

	protected $properties = [];

	public function __construct(Node $node, array $properties = [], array $defaultProperties = [])
	{
		$this->properties = array_merge(
			$this->getDefaultProperties(),
			$defaultProperties,
			$this->getNodeProperties($node),
			$properties
		);
	}

	/**
	 * @return bool
	 */
	public function isHidden()
	{
		return isset($this->properties[static::DISPLAY]) && $this->properties[static::DISPLAY] === static::DISPLAY_HIDDEN;
	}

	/**
	 * @return bool
	 */
	public function isDisplayBlock()
	{
		return isset($this->properties[static::DISPLAY]) && $this->properties[static::DISPLAY] === static::DISPLAY_BLOCK;
	}

	/**
	 * @return bool
	 */
	public function isDisplayInline()
	{
		return isset($this->properties[static::DISPLAY]) && $this->properties[static::DISPLAY] === static::DISPLAY_INLINE;
	}

	/**
	 * @return bool
	 */
	public function isFontBold()
	{
		return (
			isset($this->properties[static::FONT][static::FONT_BOLD])
			&& $this->properties[static::FONT][static::FONT_BOLD] === true
		);
	}

	/**
	 * @return bool
	 */
	public function isFontItalic()
	{
		return (
			isset($this->properties[static::FONT][static::FONT_ITALIC])
			&& $this->properties[static::FONT][static::FONT_ITALIC] === true
		);
	}

	/**
	 * @return bool
	 */
	public function isFontUnderlined()
	{
		return (
			isset($this->properties[static::FONT][static::FONT_UNDERLINED])
			&& $this->properties[static::FONT][static::FONT_UNDERLINED] === true
		);
	}

	/**
	 * @return bool
	 */
	public function isFontDeleted()
	{
		return (
			isset($this->properties[static::FONT][static::FONT_DELETED])
			&& $this->properties[static::FONT][static::FONT_DELETED] === true
		);
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @return array
	 */
	protected function getDefaultProperties()
	{
		return [
			static::DISPLAY => static::DISPLAY_INLINE,
			'font' => [],
		];
	}

	/**
	 * Returns true if this node should be rendered.
	 *
	 * @param \Bitrix\Main\Web\DOM\Node $node
	 * @return bool
	 */
	protected function isHiddenNode(Node $node)
	{
		static $hiddenNodeNames = [
			'#comment' => true, 'STYLE' => true, 'SCRIPT' => true,
		];

		return isset($hiddenNodeNames[$node->getNodeName()]);
	}

	/**
	 * Returns true if html-tag with this $tagName displays as block by default.
	 *
	 * @param string $tagName
	 * @return bool
	 */
	protected function isBlockTag($tagName)
	{
		$blockTagNames = [
			'address' => true, 'article' => true, 'aside' => true, 'blockquote' => true, 'details' => true,
			'dialog' => true, 'dd' => true, 'div' => true, 'dl' => true, 'dt' => true, 'fieldset' => true,
			'figcaption' => true, 'figure' => true, 'footer' => true, 'form' => true, 'h1' => true, 'h2' => true,
			'h3' => true, 'h4' => true, 'h5' => true, 'h6' => true, 'header' => true, 'hgroup' => true, 'hr' => true,
			'li' => true, 'main' => true, 'nav' => true, 'ol' => true, 'p' => true, 'pre' => true, 'section' => true, 'table' => true,
			'ul' => true,
		];

		return isset($blockTagNames[mb_strtolower($tagName)]);
	}

	/**
	 * Returns true if html-tag with this $tagName has bold font-weight by default.
	 *
	 * @param string $tagName
	 * @return bool
	 */
	protected function isBoldTag($tagName)
	{
		$boldTagNames = [
			'b' => true, 'mark' => true, 'em' => true, 'strong' => true, 'h1' => true, 'h2' => true, 'h3' => true,
			'h4' => true, 'h5' => true, 'h6' => true,
		];

		return isset($boldTagNames[mb_strtolower($tagName)]);
	}

	/**
	 * Returns true if html-tag with this $tagName has italic font-style by default.
	 *
	 * @param string $tagName
	 * @return bool
	 */
	protected function isItalicTag($tagName)
	{
		$italicTagNames = [
			'i' => true, 'cite' => true, 'dfn' => true,
		];

		return isset($italicTagNames[mb_strtolower($tagName)]);
	}

	/**
	 * Returns true if html-tag with this $tagName has underlined font-decoration by default.
	 *
	 * @param string $tagName
	 * @return bool
	 */
	protected function isUnderlinedTag($tagName)
	{
		return mb_strtolower($tagName) == 'u';
	}

	/**
	 * Returns true if html-tag with this $tagName renders as 'deleted' by default.
	 *
	 * @param string $tagName
	 * @return bool
	 */
	protected function isDeletedTag($tagName)
	{
		return mb_strtolower($tagName) == 'del';
	}

	/**
	 * @param Node $node
	 * @return array
	 */
	protected function getNodeProperties(Node $node)
	{
		$result = [];

		if($this->isHiddenNode($node))
		{
			$result[static::DISPLAY] = static::DISPLAY_HIDDEN;
			return $result;
		}

		if($node instanceof Element)
		{
			$styles = $node->getStyle();
			$display = false;
			$font = [];
			if($styles)
			{
				$stylePairs = explode(';', $styles);
				foreach($stylePairs as $pair)
				{
					list($name, $value) = explode(':', $pair);
					if($name && $value)
					{
						$name = trim($name);
						$value = trim($value);
						if($name == static::DISPLAY)
						{
							if($value == 'none')
							{
								$display = static::DISPLAY_HIDDEN;
							}
							elseif($value == 'block')
							{
								$display = static::DISPLAY_BLOCK;
							}
							elseif($value == 'inline')
							{
								$display = static::DISPLAY_INLINE;
							}
						}
						elseif($name == 'font-weight')
						{
							if(intval($value) > 500 || $value == 'bold')
							{
								$font[static::FONT_BOLD] = true;
							}
							elseif(intval($value) < 500 || $value == 'normal')
							{
								$font[static::FONT_BOLD] = false;
							}
						}
						elseif($name == 'font-style')
						{
							if($value == 'italic' || mb_strpos($value, 'oblique') === 0)
							{
								$font[static::FONT_ITALIC] = true;
							}
							elseif($value == 'normal')
							{
								$font[static::FONT_ITALIC] = false;
							}
						}
						elseif($name == 'text-decoration')
						{
							if(strpos($value, 'underline') !== false)
							{
								$font[static::FONT_UNDERLINED] = true;
							}
							if(strpos($value, 'line-through') !== false)
							{
								$font[static::FONT_DELETED] = true;
							}
							if($value == 'none')
							{
								$font[static::FONT_UNDERLINED] = false;
								$font[static::FONT_DELETED] = false;
							}
						}
					}
				}
			}
			if($display == static::DISPLAY_HIDDEN)
			{
				$result[static::DISPLAY] = $display;
				return $result;
			}
			if(!$display && $this->isBlockTag($node->getTagName()))
			{
				$display = static::DISPLAY_BLOCK;
			}

			if(!isset($font[static::FONT_BOLD]) && $this->isBoldTag($node->getTagName()))
			{
				$font[static::FONT_BOLD] = true;
			}
			if(!isset($font[static::FONT_ITALIC]) && $this->isItalicTag($node->getTagName()))
			{
				$font[static::FONT_ITALIC] = true;
			}
			if(!isset($font[static::FONT_UNDERLINED]) && $this->isUnderlinedTag($node->getTagName()))
			{
				$font[static::FONT_UNDERLINED] = true;
			}
			if($this->isDeletedTag($node->getTagName()))
			{
				$font[static::FONT_DELETED] = true;
			}

			if($display)
			{
				$result[static::DISPLAY] = $display;
			}
			$result['font'] = $font;
		}

		return $result;
	}
}