<?php
namespace Bitrix\Report\VisualConstructor\Fields;

/**
 * Class DivPartHtml, this class create for save html part context, after add element (start end of div) we can modify them
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class DivPartHtml extends Html
{
	private $div;

	/**
	 * @param string $class String of class to div.
	 * @return void
	 */
	public function addClass($class)
	{
		$this->getDiv()->addClass($class);
		$this->setContent($this->getDiv()->buildDivStart());
	}

	/**
	 * @param string $key Key of add data attribute ('role').
	 * @param string $value Value of data attribute ('widget').
	 * @return void
	 */
	public function addInlineStyle($key, $value)
	{
		$this->getDiv()->addInlineStyle($key, $value);
		$this->setContent($this->getDiv()->buildDivStart());
	}

	/**
	 * @return Div
	 */
	public function getDiv()
	{
		return $this->div;
	}

	/**
	 * @param Div $div Div in which context this element.
	 * @return void
	 */
	public function setDiv(Div $div)
	{
		$this->div = $div;
	}

}