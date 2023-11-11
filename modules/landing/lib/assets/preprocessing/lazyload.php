<?php

namespace Bitrix\Landing\Assets\PreProcessing;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Node;
use \Bitrix\Landing\File;
use \Bitrix\Main\Web\DOM;

class Lazyload
{
	protected const MODULE_ID = 'landing';
	protected const IMG_PLACEHOLDER_SIZE_DEFAULT = 333;
	protected const BG_PLACEHOLDER_SIZE_DEFAULT = 10;

	protected $block;
	protected $content;
	protected $manifest;
	protected $dom;
	protected $skipDynamic = true;

	protected function __construct(Block $block)
	{
		$this->block = $block;
		$this->manifest = $block->getManifest();
		$this->content = $block->getContent();
	}

	/**
	 * @param bool $flag
	 */
	public function setSkipDynamic(bool $flag = true): void
	{
		$this->skipDynamic = $flag;
	}

	protected function parse(): void
	{
		if (!$this->content || !$this->manifest['nodes'])
		{
			return;
		}
		// tmp skip dynamic
		if ($this->skipDynamic && !empty($this->block->getDynamicParams()))
		{
			return;
		}

		$changed = false;
		foreach ($this->manifest['nodes'] as $selector => $node)
		{
			if ($node['type'] === 'img')
			{
				$node = Node\Img::changeNodeType($node, $this->block);
			}

			if ($node['type'] === 'img' || $node['type'] === 'styleimg')
			{
				$domElements = Node\Style::getNodesBySelector($this->block, $selector);

				if ($node['type'] === 'img')
				{
					foreach ($domElements as $domElement)
					{
						if ($domElement->getTagName() === 'IMG')
						{
							$this->parseImgTag($domElement, $selector);
						}
						else
						{
							$this->parseBg($domElement, $selector);
						}
						$changed = true;
					}
				}
				elseif ($node['type'] === 'styleimg')
				{
					foreach ($domElements as $domElement)
					{
						if ($domElement->getTagName() !== 'IMG')
						{
							$this->parseStyleImg($domElement, $selector);
							$changed = true;
						}
					}
				}
			}
		}

		if ($changed)
		{
			$this->block->saveContent($this->block->getDom()->saveHTML());
			$this->block->save();
		}
	}

	protected function parseImgTag(DOM\Element $node, string $selector): void
	{
		$origSrc = $node->getAttribute('src');
		if (!$origSrc)
		{
			return;
		}

		// get sizes for placeholder
		if (
			($fileId = $node->getAttribute('data-fileid'))
			&& $fileId > 0
			&& ($fileArray = File::getFileArray($fileId))
		)
		{
			$width = $fileArray['WIDTH'];
			$height = $fileArray['HEIGHT'];
		}
		else if ($manifestSize = $this->getPlaceholderSizeFromManifest($selector))
		{
			[$width, $height] = $manifestSize;
		}
		else
		{
			$width = $height = self::IMG_PLACEHOLDER_SIZE_DEFAULT;
		}

		$lazySrc = $this->createPlaceholderImage($width, $height);

		$node->setAttribute('data-lazy-img', 'Y');
		$node->setAttribute('data-src', $origSrc);
		$node->setAttribute('src', $lazySrc);
		$node->setAttribute('loading', 'lazy');    //for native
		if ($srcset = $node->getAttribute('srcset'))
		{
			$node->removeAttribute('srcset');
			$node->setAttribute('data-srcset', $srcset);
		}
	}

	protected function parseBg(DOM\Element $node, string $selector): void
	{
		$styles = DOM\StyleInliner::getStyle($node, false);
		if (!$styles['background-image'])
		{
			return;
		}
		$origBg = implode('|', $styles['background-image']);

		// get sizes for placeholder
		if (
			($fileId = $node->getAttribute('data-fileid'))
			&& $fileId > 0
			&& ($fileArray = File::getFileArray($fileId))
		)
		{
			$width = $fileArray['WIDTH'];
			$height = $fileArray['HEIGHT'];
		}
		else if ($manifestSize = $this->getPlaceholderSizeFromManifest($selector))
		{
			[$width, $height] = $manifestSize;
		}
		else
		{
			$width = $height = self::BG_PLACEHOLDER_SIZE_DEFAULT;
		}

		$node->setAttribute('data-lazy-bg', 'Y');
		$node->setAttribute('data-bg', $origBg);

		$lazySrc = $this->createPlaceholderImage($width, $height);
		DOM\StyleInliner::setStyle($node, array_merge($styles, ['background-image' => ["url({$lazySrc})"]]));
		if ($origSrc = self::getSrcByBgStyle($origBg))
		{
			if (isset($origSrc['src']))
			{
				$node->setAttribute('data-src', $origSrc['src']);
			}
			if (isset($origSrc['src2x']))
			{
				$node->setAttribute('data-src2x', $origSrc['src2x']);
			}
		}
	}

	protected function parseStyleImg(DOM\Element $node, string $selector): void
	{
		if (
			($fileId = $node->getAttribute('data-fileid'))
			&& $fileId > 0
			&& ($fileArray = File::getFileArray($fileId))
		)
		{
			$width = $fileArray['WIDTH'];
			$height = $fileArray['HEIGHT'];

			$node->setAttribute('data-lazy-styleimg', 'Y');
			$node->setAttribute('data-style', $node->getAttribute('style'));

			$lazySrc = $this->createPlaceholderImage($width, $height);
			$node->setAttribute('style', "background-image:url({$lazySrc});");

			// todo: after add src in saveNode - get it too
		}
	}

	protected function getPlaceholderSizeFromManifest(string $selector)
	{
		if (!empty($dimensions = $this->manifest['nodes'][$selector]['dimensions']))
		{
			foreach ($dimensions as $key => $value)
			{
				if (mb_stripos($key, 'width') !== false)
				{
					$width = $value;
				}
				if (mb_stripos($key, 'height') !== false)
				{
					$height = $value;
				}
			}

			if (isset($width, $height))
			{
				return [$width, $height];
			}

			if (isset($width) || isset($height))
			{
				$size = $width ?? $height;

				return [$size, $size];
			}
		}

		return false;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return string path of placeholder
	 */
	protected function createPlaceholderImage(int $width, int $height): string
	{
		return "data:image/svg+xml;base64,".base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'"><rect id="backgroundrect" width="100%" height="100%" x="0" y="0" fill="#ddd" fill-opacity=".7" stroke="none"/></svg>');
		// return "https://cdn.bitrix24.site/placeholder/{$width}x{$height}.png";
	}

	/**
	 * Parse style string and find image urls
	 * @param $style
	 * @return array|bool - false if find nothing
	 */
	protected static function getSrcByBgStyle($style)
	{
		if (preg_match_all(
			'/url\(\'*([^\']+)\'*\)\s*([\d]*x*)/is',
			$style,
			$matches
		))
		{
			$result = [];
			for ($i = 0, $c = count($matches[1]); $i < $c; $i++)
			{
				if ($matches[2][$i] == '2x')
				{
					$result['src2x'] = $matches[1][$i];
				}
				else
				{
					$result['src'] = $matches[1][$i];
				}
			}

			if(!empty($result['src']))
			{
				return $result;
			}
		}

		return false;
	}

	/**
	 * Processing icons in the block content.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
		$lazyload = new self($block);
		$lazyload->parse();
	}

	public static function processingDynamic(Block $block): void
	{
		$lazyload = new self($block);
		$lazyload->setSkipDynamic(false);
		$lazyload->parse();
	}
}