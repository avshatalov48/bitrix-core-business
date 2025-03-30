<?php

namespace Bitrix\UI\Buttons;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Contract;

//We know about lazy load. So, the code loads common messages for default buttons,
//which implemented by subclasses of BaseButton.
Loc::loadLanguageFile(__FILE__);

class BaseButton implements Contract\Renderable
{
	const UNIQ_ID_DATA_ATTR      = 'btn-uniqid';
	const JSON_OPTIONS_DATA_ATTR = 'json-options';

	/** @var string */
	protected $id;
	/** @var string */
	protected $text;
	/** @var string */
	protected $tag = Tag::BUTTON;
	/** @var string */
	protected $baseClass = "ui-btn";
	/** @var string */
	protected $link;
	/** @var integer|string */
	protected $counter;
	/** @var array */
	protected $events = [];
	/** @var ButtonAttributes */
	private $attributes;

	final public function __construct(array $params = [])
	{
		$this->attributes = new ButtonAttributes();
		$this->attributes->addDataAttribute(self::UNIQ_ID_DATA_ATTR, $this->generateUniqid());
		$this->addClass($this->getBaseClass());

		$this->init($params);
	}

	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [];
	}

	protected function init(array $params = [])
	{
		$this->buildFromArray(array_merge(
			$this->getDefaultParameters(),
			$params
		));
	}

	final public static function create(array $params = [])
	{
		return new static($params);
	}

	protected function buildFromArray($params)
	{
		if (isset($params['text']))
		{
			$this->setText($params['text']);
		}

		if (!empty($params['styles']))
		{
			$this->setStyles($params['styles']);
		}

		if (!empty($params['maxWidth']))
		{
			$this->setMaxWidth($params['maxWidth']);
		}

		if (!empty($params['className']) && is_string($params['className']))
		{
			$params['classList'] = array_filter(explode(' ', $params['className']));
		}

		if (empty($params['classList']))
		{
			$params['classList'] = [];
		}

		$params['classList'] = array_merge(
			[$this->getBaseClass()],
			$params['classList']
		);

		$this->getAttributeCollection()->setClassList($params['classList']);

		if (!empty($params['counter']))
		{
			$this->setCounter($params['counter']);
		}

		if (!empty($params['id']))
		{
			$this->setId($params['id']);
		}

		if (!empty($params['tag']))
		{
			$this->setTag($params['tag']);
		}

		if (!empty($params['link']))
		{
			$this->setLink($params['link']);
		}

		if (!empty($params['click']))
		{
			$this->bindEvent('click', $params['click']);
		}

		if (!empty($params['onclick']))
		{
			$this->bindEvent('click', $params['onclick']);
		}

		if (!empty($params['events']))
		{
			$this->bindEvents($params['events']);
		}

		if (isset($params['dataset']) && is_array($params['dataset']))
		{
			foreach ($params['dataset'] as $name => $value)
			{
				$this->addDataAttribute($name, $value);
			}
		}
	}

	protected function listExtensions()
	{
		return [];
	}

	public static function getJsClass()
	{
		return 'BX.UI.' . (new \ReflectionClass(get_called_class()))->getShortName();
	}

	protected function appendDefaultJsonOption(ButtonAttributes $attributes)
	{
		if (count($this->getEvents()) > 0)
		{
			$attributes->addJsonOption('events', $this->getEvents());
		}

		return $attributes;
	}

	public function render($jsInit = true)
	{
		Extension::load($this->listExtensions());

		$output = '';
		$tagName = $this->getTag();
		$attributes = clone $this->getAttributeCollection();
		$this->appendDefaultJsonOption($attributes);

		switch ($tagName)
		{
			case Tag::LINK:
			case Tag::BUTTON:
				if ($tagName === Tag::LINK && $this->getLink())
				{
					$attributes['href'] = $this->getLink();
				}

				$inner = $this->renderInner();
				$output = "<{$tagName} {$attributes}>{$inner}</{$tagName}>";
				break;
			case Tag::INPUT:
			case Tag::SUBMIT:
				$attributes['value'] = htmlspecialcharsbx($this->getText());
				$attributes['type'] = Tag::BUTTON;

				if ($tagName === Tag::SUBMIT)
				{
					$tagName = Tag::INPUT;
					$attributes['type'] = Tag::SUBMIT;
				}

				$output = "<{$tagName} {$attributes}/>";
				break;
		}

		if ($jsInit)
		{
			$js = $this->renderJavascript();
			if ($js)
			{
				$output .= "<script>BX.ready(function(){ {$js} });</script>";
			}
		}

		return $output;
	}

	protected function generateUniqid()
	{
		return 'uibtn-' . Random::getString(8);
	}

	public function isInputTag()
	{
		return $this->isInputType();
	}

	public function isInputType()
	{
		return in_array($this->tag, [
			Tag::INPUT,
			Tag::SUBMIT,
		], true);
	}

	protected function renderInner()
	{
		$counter = $this->getCounter();
		return (
			(!empty($this->getText()) ? '<span class="ui-btn-text">'.htmlspecialcharsbx($this->getText()).'</span>' : '').
			($counter !== null ? '<span class="ui-btn-counter">'.htmlspecialcharsbx($counter).'</span>' : '' )
		);
	}

	protected function renderJavascript()
	{
		$selector = $this->getQuerySelector();

		return "BX.UI.ButtonManager.createFromNode(document.querySelector('{$selector}'));";
	}

	protected function getQuerySelector()
	{
		$tag = $this->getTag();
		$uniqId = $this->getUniqId();
		$uniqIdName = "data-" . self::UNIQ_ID_DATA_ATTR;

		return "{$tag}[{$uniqIdName}=\"{$uniqId}\"]";
	}

	public function getUniqId()
	{
		return $this->getAttributeCollection()->getDataAttribute(self::UNIQ_ID_DATA_ATTR);
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	public function getMaxWidth()
	{
		return isset($this->getAttributeCollection()['style']['max-width'])?
			$this->getAttributeCollection()['style']['max-width'] : null;
	}

	public function setMaxWidth($width)
	{
		if (!isset($this->getAttributeCollection()['style']))
		{
			$this->getAttributeCollection()['style'] = [];
		}

		$this->getAttributeCollection()['style']['max-width'] = $width;

		return $this;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function setLink($link)
	{
		if (is_string($link) && !empty($link))
		{
			$this->link = $link;
			$this->setTag(Tag::LINK);
		}

		return $this;
	}

	public function getCounter()
	{
		return $this->counter;
	}

	public function setCounter($counter)
	{
		if (in_array($counter, [0, '0', '', null, false], true))
		{
			$this->counter = null;
		}
		else if ((is_int($counter) && $counter > 0) || (is_string($counter) && mb_strlen($counter)))
		{
			$this->counter = $counter;
		}

		return $this;
	}

	public function addClass($className)
	{
		$this->getAttributeCollection()->addClass($className);

		return $this;
	}

	public function unsetClass($className)
	{
		$this->getAttributeCollection()->removeClass($className);

		return $this;
	}

	public function removeClass($className)
	{
		return $this->unsetClass($className);
	}

	public function hasClass($className)
	{
		return $this->getAttributeCollection()->hasClass($className);
	}

	public function getClassList()
	{
		return $this->getAttributeCollection()['class']?: [];
	}

	public function addAttribute($name, $value = null)
	{
		if (mb_strtolower($name) === 'class')
		{
			throw new ArgumentException('Could not add "class" attribute. You should use ::addClass()', 'class');
		}

		$this->getAttributeCollection()[$name] = $value;

		return $this;
	}

	public function unsetAttribute($name)
	{
		unset($this->getAttributeCollection()[$name]);

		return $this;
	}

	public function removeAttribute($name)
	{
		return $this->unsetAttribute($name);
	}

	public function getAttribute($name, $defaultValue = null)
	{
		return $this->getAttributeCollection()->getAttribute($name, $defaultValue);
	}

	public function addDataAttribute($name, $value = null)
	{
		$this->getAttributeCollection()->addDataAttribute($name, $value);

		return $this;
	}

	public function getDataAttribute($name, $defaultValue = null)
	{
		return $this->getAttributeCollection()->getDataAttribute($name, $defaultValue);
	}

	public function setDataRole($dataRole)
	{
		$this->addDataAttribute('role', $dataRole);

		return $this;
	}

	public function getDataRole()
	{
		return $this->getDataAttribute('role');
	}

	public function setStyles(array $styles)
	{
		$this->getAttributeCollection()['style'] = $styles;
	}

	public function getStyles()
	{
		return $this->getAttributeCollection()['style'];
	}

	/**
	 * @return ButtonAttributes
	 */
	public function getAttributeCollection()
	{
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 *
	 * @return static
	 */
	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @param string $tag
	 *
	 * @return static
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBaseClass()
	{
		return $this->baseClass;
	}

	/**
	 * @param bool $flag
	 * @return static
	 */
	public function setDisabled($flag = true)
	{
		if ($flag === false)
		{
			unset($this->getAttributeCollection()['disabled']);
		}
		else
		{
			$this->getAttributeCollection()['disabled'] = true;
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->getAttributeCollection()['disabled'] === true;
	}

	/**
	 * @return array
	 */
	public function getEvents()
	{
		return $this->events;
	}

	/**
	 * @param string $eventName Event identifier.
	 * @param string|JsHandler|JsCode $fn Link to js function which will be invoked.
	 * @see in js BX.UI.BaseButton.handleEvent to know order of arguments in event handler.
	 *
	 * @return $this
	 */
	public function bindEvent($eventName, $fn)
	{
		if (is_string($fn))
		{
			$fn = new JsHandler($fn);
		}

		$this->events[$eventName] = $fn;

		return $this;
	}

	/**
	 * @param array $events
	 *
	 * @return $this
	 */
	public function bindEvents(array $events)
	{
		foreach ($events as $name => $fn)
		{
			$this->bindEvent($name, $fn);
		}

		return $this;
	}

	/**
	 * @param string $eventName
	 *
	 * @return $this
	 */
	public function unbindEvent($eventName)
	{
		unset($this->events[$eventName]);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function unbindEvents()
	{
		unset($this->events);

		return $this;
	}
}