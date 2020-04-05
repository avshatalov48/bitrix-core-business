<?php
namespace Bitrix\Report\VisualConstructor\Fields;

use Bitrix\Main\Page\Asset;
use Bitrix\Report\VisualConstructor\Form;

/**
 * Class Base
 * @package Bitrix\Report\VisualConstructor\Fields
 */
abstract class Base
{
	const FIELDS_COMPONENT_NAME = 'bitrix:report.visualconstructor.config.fields';

	private $key;
	private $classList = array();
	private $dataAttributes = array();
	private $id;
	private $label = '';
	private $isDisplayLabel = true;
	private $prefix;
	private $postfix;
	private $weight = 0;
	private $assets = array();
	private $js = array();
	private $css = array();
	private $inline = array();
	private $jsEvents = array();
	private $jsEventListeners = array();
	private $form;
	private $compatibleViewTypes;
	private $inlineStyle;
	private $display = true;

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * @return Base
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Setter for string which render before field content.
	 *
	 * @param string|Base $prefix String for render before content.
	 * @return void
	 */
	public function setPrefix($prefix)
	{
		if (is_string($prefix))
		{
			$prefix = new Html($prefix);
		}
		$this->prefix = $prefix;
	}

	/**
	 * @return Base
	 */
	public function getPostfix()
	{
		return $this->postfix;
	}

	/**
	 * Setter for string which render after field content.
	 *
	 * @param string|Base $postfix String for render after content.
	 * @return void
	 */
	public function setPostfix($postfix)
	{
		if (is_string($postfix))
		{
			$postfix = new Html($postfix);
		}
		$this->postfix = $postfix;
	}

	/**
	 * @return array
	 */
	public function getAssets()
	{
		return $this->assets;
	}

	/**
	 * Attach js, css, or inline assets to field.
	 *
	 * @param array $assets Array of assets which attach to field.
	 * @return void
	 */
	public function addAssets($assets)
	{
		foreach ($assets as $key => $assetList)
		{
			switch ($key)
			{
				case 'js':
					$this->js = array_merge($this->js, $assetList);
					break;
				case 'css':
					$this->css = array_merge($this->css, $assetList);
					break;
				case 'inline':
					$this->inline = array_merge($this->inline, $assetList);
			}
		}
		$this->assets = $assets;
	}

	/**
	 * @return Form
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Setter for form context.
	 *
	 * @param Form $form Form where render field.
	 * @return void
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}

	/**
	 * Check is displayable this field, if yes collect all assets,
	 * And print content.
	 *
	 * @return void
	 */
	public function render()
	{
		if (!$this->isDisplay())
		{
			return;
		}

		foreach ($this->js as $jsPath)
		{
			Asset::getInstance()->addJs($jsPath);
		}

		foreach ($this->css as $cssPath)
		{
			Asset::getInstance()->addCss($cssPath);
		}

		foreach ($this->inline as $inline)
		{
			//TODO
			Asset::getInstance()->addString($inline);
		}

		if ($this->getPrefix())
		{
			$this->getPrefix()->render();
		}

		$this->printContent();

		if ($this->getPostfix())
		{
			$this->getPostfix()->render();
		}

	}

	/**
	 * Print field html/string ore somthing else printable.
	 *
	 * @return void
	 */
	abstract public function printContent();

	/**
	 * @return array
	 */
	public function getJsEventListeners()
	{
		return $this->jsEventListeners;
	}

	/**
	 * @return array
	 */
	public function getJsEvents()
	{
		return $this->jsEvents;
	}

	/**
	 * Add js event handler to field on some event which fire on $field in first parameter of this function.
	 *
	 * @param self|null $field Field which fire event.
	 * @param string $eventKey Event key which listen this field.
	 * @param array $jsParams JS parameters which pass to handler.
	 * @return $this
	 */
	public function addJsEventListener(Base $field = null, $eventKey, $jsParams)
	{
		$field->jsEvents[$eventKey][] = array(
			'behaviourOwner' => $this,
			'handlerParams' => $jsParams
		);

		$this->jsEventListeners[$eventKey][] = array(
			'eventOwner' => $field,
			'handlerParams' => $jsParams,
		);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Setter for field label.
	 *
	 * @param string $label String to set as label for field.
	 * @return void
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}


	/**
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		return $this->compatibleViewTypes;
	}

	/**
	 * Setter of compatible widget view types.
	 *
	 * @param array $compatibleViewTypes Compatible widget view type list.
	 * @return void
	 */
	public function setCompatibleViewTypes($compatibleViewTypes)
	{
		$this->compatibleViewTypes = $compatibleViewTypes;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Setter for id.
	 *
	 * @param string $id Unique id.
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Setter for Weight.
	 *
	 * @param int $weight Integer of weightof Field.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}


	/**
	 * Add to class list class string.
	 *
	 * @param string $class Class string.
	 * @return void
	 */
	public function addClass($class)
	{
		$this->classList[] = $class;
	}

	/**
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classList;
	}

	/**
	 * Get data attribute by key.
	 *
	 * @param string $key Key for data attribute which will return.
	 * @return mixed|null
	 */
	public function getDataAttribute($key)
	{
		return !empty($this->dataAttributes[$key]) ? $this->dataAttributes[$key] : null;
	}

	/**
	 * @return array
	 */
	public function getDataAttributes()
	{
		return $this->dataAttributes;
	}

	/**
	 * Set Data attributes by array of pair key => values.
	 *
	 * @param array $dataAttributes Array of pair key=>value.
	 * @return void
	 */
	public function setDataAttributes($dataAttributes)
	{
		$this->dataAttributes = $dataAttributes;
	}

	/**
	 * @param string $key Key for data attribute. ('role').
	 * @param string $value Value for data attribute. ('widget').
	 * @return void
	 */
	public function addDataAttribute($key, $value)
	{
		$this->dataAttributes[$key] = $value;
	}

	/**
	 * Conver id property to string for render as html in element.
	 *
	 * @return string
	 */
	public function getRenderedIdAttribute()
	{
		$result = '';
		if ($this->getId() !== null)
		{
			$result = ' id="' . $this->getId() . '"';
		}
		return $result;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Seter for key.
	 *
	 * @param string $key Unique key for field.
	 * @return void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Convert class list property collection to string for render as html in field element.
	 *
	 * @return string
	 */
	public function getRenderedClassAttributes()
	{
		$classes =  $this->getClasses();
		$classes = array_filter($classes);
		$result = '';
		if (!empty($classes))
		{
			$result = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
		}
		return $result;
	}

	/**
	 * Conver data atttribute property collection to string for render as html in field element.
	 *
	 * @return string
	 */
	public function getRenderedDataAttributes()
	{
		$dataAttributes = $this->getDataAttributes();
		$result = '';
		foreach ($dataAttributes as $key => $value)
		{
			$result .= ' data-' . $key . '="' . $value .  '"';
		}
		return $result;
	}

	/**
	 * Convert inline style propery collection to string for render as html.
	 *
	 * @return string
	 */
	public function getRenderedInlineStyle()
	{
		$inlineStyles = $this->getInlineStyle();
		$result = '';
		if ($inlineStyles)
		{
			$result = 'style="';
			foreach ($inlineStyles as $key => $value)
			{
				$result .= ' ' . $key . ': ' . $value .  ';';
			}
			$result .= '"';
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function isDisplayLabel()
	{
		return $this->isDisplayLabel;
	}

	/**
	 * Setter for display mode of label of field.
	 *
	 * @param bool $isDisplayLabel True if label must render else false.
	 * @return void
	 */
	public function setIsDisplayLabel($isDisplayLabel)
	{
		$this->isDisplayLabel = $isDisplayLabel;
	}

	/**
	 * Add inline style by key and value.
	 *
	 * @param string $key Key of inline style. ('background-color').
	 * @param string $value Value Of inline style. ('red').
	 * @return void
	 */
	public function addInlineStyle($key, $value)
	{
		$this->inlineStyle[$key] = $value;
	}
	/**
	 * @return array
	 */
	public function getInlineStyle()
	{
		return $this->inlineStyle;
	}

	/**
	 * Setter dor inline style.
	 *
	 * @param array $inlineStyle Inline style string.
	 * @return void
	 */
	public function setInlineStyle($inlineStyle)
	{
		$this->inlineStyle = $inlineStyle;
	}

	/**
	 * @return bool
	 */
	public function isDisplay()
	{
		return $this->display;
	}

	/**
	 * Setter for display mode.
	 *
	 * @param bool $display Render or not this field marker.
	 * @return void
	 */
	public function setDisplay($display)
	{
		$this->display = $display;
	}

	/**
	 * Include component for field.
	 *
	 * @param string $templateName Template name string.
	 * @param array $params Parameters pass to component.
	 * @return void
	 */
	protected function includeFieldComponent($templateName, $params = array())
	{
		global $APPLICATION;
		$defaultParams = array(
			'CONFIGURATION_FIELD' => $this,
		);
		$params = array_merge($defaultParams, $params);
		$APPLICATION->IncludeComponent(self::FIELDS_COMPONENT_NAME, $templateName, $params);
	}
}