<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block\Content;

use Bitrix\Main\SystemException;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;

Loc::loadMessages(__FILE__);

/**
 * Class Engine
 * @package Bitrix\Fileman\Block
 */
class Engine
{
	const BLOCK_PLACE_ATTR = 'data-bx-block-editor-place';
	const STYLIST_TAG_ATTR = 'data-bx-stylist-container';
	const BLOCK_PLACE_ATTR_DEF_VALUE = 'body';

	const CONTENT_SLICE = 0;
	const CONTENT_JSON = 1;

	/** @var  BlockContent $content Block content. */
	protected $content;

	/** @var  Document $document Document. */
	protected $document;

	/** @var string|null $encoding Encoding.  */
	protected $encoding = null;

	/**
	 * Check string for the presence of slices.
	 *
	 * @param string $string String.
	 * @return bool
	 */
	public static function isSupported($string)
	{
		foreach (self::getConverters() as $converter)
		{
			if ($converter::isValid($string))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Create instance.
	 * @param Document|null $document Template document.
	 * @return static
	 */
	public static function create(Document $document = null)
	{
		return new static($document);
	}

	/**
	 * Engine constructor.
	 * @param Document|null $document Template document.
	 */
	public function __construct(Document $document = null)
	{
		$this->setDocument($document ?: new Document);
	}

	/**
	 * Set html.
	 *
	 * @param string $html Html.
	 * @return $this
	 */
	public function setHtml($html)
	{
		$this->document->loadHTML($html);
		return $this;
	}

	/**
	 * Set document.
	 *
	 * @param Document $document Template document.
	 * @return $this
	 */
	public function setDocument(Document $document)
	{
		$this->document = $document;
		return $this;
	}

	/**
	 * Set content string.
	 *
	 * @param string $string Content string.
	 * @return Engine
	 */
	public function setContent($string)
	{
		return $this->setBlockContent(static::createBlockContent($string));
	}

	/**
	 * Set block content.
	 *
	 * @param BlockContent $blockContent Block content.
	 * @return $this
	 */
	public function setBlockContent(BlockContent $blockContent)
	{
		$this->content = $blockContent;
		$this->fill();
		return $this;
	}

	/**
	 * Set encoding.
	 *
	 * @param string|null $encoding Encoding.
	 * @return $this
	 */
	public function setEncoding($encoding = null)
	{
		$this->encoding = $encoding;
		return $this;
	}

	public function getDocument()
	{
		return $this->document;
	}

	public function getHtml()
	{
		$html = $this->document->saveHTML();
		return $html ? $html : '';
	}

	/**
	 * Fill HTML template by content.
	 *
	 * @param string $htmlTemplate Html template.
	 * @param string $content Content.
	 * @param string $encoding Encoding.
	 * @return string
	 */
	public static function fillHtmlTemplate($htmlTemplate, $content, $encoding = null)
	{
		$instance = static::create()->setEncoding($encoding)->setHtml($htmlTemplate)->setContent($content);

		if($instance->fill())
		{
			return $instance->getHtml() ?: $htmlTemplate;
		}
		else
		{
			return $htmlTemplate;
		}
	}

	/**
	 * Fill document by content.
	 *
	 * @return bool
	 */
	public function fill()
	{
		// prepare blocks
		$blocks = array();
		$grouped = array();
		foreach($this->content->getBlocks() as $item)
		{
			$grouped[$item['place']][] = $item['value'];
		}

		foreach($grouped as $place => $values)
		{
			$blocks[$place] = "\n" . implode("\n", $values) . "\n";
		}
		unset($grouped);


		// unite styles to one string
		$styles = '';
		foreach($this->content->getStyles() as $item)
		{
			$styles .= "\n" . $item['value'];
		}

		if($styles && preg_match_all("#<style[\\s\\S]*?>([\\s\\S]*?)</style>#i", $styles, $matchesStyles))
		{
			$styles = '';
			$matchesStylesCount = count($matchesStyles);
			for($i = 0; $i < $matchesStylesCount; $i++)
			{
				$styles .= "\n" . $matchesStyles[1][$i];
			}
		}

		// if nothing to replace, return content
		if(!$styles && count($blocks) ===  0)
		{
			return false;
		}

		// add styles block to head of document
		if($styles)
		{
			$this->addStylesToDocumentHead($styles);
		}

		// fill places by blocks
		if($blocks)
		{
			$this->addBlocksToDocument($blocks);
		}

		return true;
	}

	protected function addBlocksToDocument($blocks)
	{
		$placeList = $this->document->querySelectorAll('[' . static::BLOCK_PLACE_ATTR . ']');
		if(empty($placeList))
		{
			return;
		}

		// find available places
		$firstPlaceCode = null;
		$bodyPlaceCode = null;
		$placeListByCode = array();
		foreach($placeList as $place)
		{
			/* @var $place \Bitrix\Main\Web\DOM\Element */
			if (!$place || !$place->getAttributeNode(static::BLOCK_PLACE_ATTR))
			{
				continue;
			}

			/*
			// remove child nodes
			foreach($place->getChildNodesArray() as $child)
			{
				$place->removeChild($child);
			}
			*/

			$placeCode = $place->getAttribute(static::BLOCK_PLACE_ATTR);
			$placeListByCode[$placeCode] = $place;
			if(!$firstPlaceCode)
			{
				$firstPlaceCode = $placeCode;
			}

			if(!$bodyPlaceCode && $placeCode == static::BLOCK_PLACE_ATTR_DEF_VALUE)
			{
				$bodyPlaceCode = $placeCode;
			}
		}

		// group block list by existed places
		$blocksByExistType = array();
		foreach($blocks as $placeCode => $blockHtml)
		{
			// if there is no place, find body-place or first place or skip filling place
			if(!array_key_exists($placeCode, $placeListByCode))
			{
				if($bodyPlaceCode)
				{
					$placeCode = $bodyPlaceCode;
				}
				elseif($firstPlaceCode)
				{
					$placeCode = $firstPlaceCode;
				}
				else
				{
					continue;
				}
			}

			$blocksByExistType[$placeCode][] = $blockHtml;
		}

		//fill existed places by blocks
		foreach($blocksByExistType as $placeCode => $blockHtmlList)
		{
			if(!array_key_exists($placeCode, $placeListByCode))
			{
				continue;
			}

			$place = $placeListByCode[$placeCode];
			$place->setInnerHTML(implode("\n", $blockHtmlList));
		}
	}

	protected function addStylesToDocumentHead($styleString)
	{
		$headDomElement = $this->document->getHead();
		if(!$headDomElement)
		{
			return;
		}

		$styleNode = end($headDomElement->querySelectorAll('style[' . self::STYLIST_TAG_ATTR . ']'));
		if(!$styleNode)
		{
			$styleNode = $this->document->createElement('style');
			$styleNode->setAttribute('type', 'text/css');
			$styleNode->setAttribute(self::STYLIST_TAG_ATTR, 'item');
			$headDomElement->appendChild($styleNode);
			$styleNode->appendChild($this->document->createTextNode($styleString));
		}
		else
		{
			$styleList1 = CssParser::parseCss($styleNode->getTextContent());
			$styleList2 = CssParser::parseCss($styleString);
			$styleList = array_merge($styleList1, $styleList2);

			$styleListByKey = array();
			foreach($styleList as $styleItem)
			{
				if(!is_array($styleListByKey[$styleItem['SELECTOR']]))
				{
					$styleListByKey[$styleItem['SELECTOR']] = array();
				}

				$styleListByKey[$styleItem['SELECTOR']] = array_merge(
					$styleListByKey[$styleItem['SELECTOR']],
					$styleItem['STYLE']
				);
			}

			$stylesString = '';
			foreach($styleListByKey as $selector => $declarationList)
			{
				$stylesString .= $selector . '{' . CssParser::getDeclarationString($declarationList) . "}\n";
			}

			if($stylesString)
			{
				$styleNode->setInnerHTML('');
				$styleNode->appendChild($this->document->createTextNode($stylesString));
			}
		}
	}

	/**
	 * Parse string to array of block content.
	 *
	 * @param string $string String.
	 * @return BlockContent
	 * @throws SystemException
	 */
	protected static function createBlockContent($string)
	{
		foreach (self::getConverters() as $converter)
		{
			if ($converter::isValid($string))
			{
				return $converter::toArray($string);
			}
		}

		throw new SystemException('Wrong content type.');
	}

	protected static function getConverters()
	{
		return array(
			__NAMESPACE__ . '\JsonConverter',
			__NAMESPACE__ . '\SliceConverter',
		);
	}
}