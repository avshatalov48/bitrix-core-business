<?php
namespace Bitrix\Report\VisualConstructor\Fields;

/**
 * Div Field for wrappers of type, provide methods start end end which generate DivPartHtml fields for save div context
 */
class Div extends Base
{
	private $content;


	/**
	 * Build div star string
	 * @return string
	 */
	public function buildDivStart()
	{
		$str = '<div';

		$str .= $this->getRenderedIdAttribute();
		$str .= $this->getRenderedClassAttributes();
		$str .= $this->getRenderedDataAttributes();
		$str .= $this->getRenderedInlineStyle();

		$str .= '>';
		return $str;
	}

	/**
	 * @return Html
	 */
	public function start()
	{
		$html = new DivPartHtml($this->buildDivStart());
		$html->setDiv($this);
		$html->setPrefix($this->getPrefix());
		$html->addAssets($this->getAssets());
		if ($this->getKey())
		{
			$html->setKey($this->getKey() . '_start');
		}

		return $html;
	}

	/**
	 * @return Html
	 */
	public function end()
	{
		$html = new DivPartHtml('</div>');
		$html->setDiv($this);
		$html->setPostfix($this->getPostfix());
		if ($this->getKey())
		{
			$html->setKey($this->getKey() . '_end');
		}
		return $html;
	}


	/**
	 * @return void
	 */
	public function printContent()
	{
		echo $this->getContent();
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param mixed $content Value to set in div as content.
	 * @return void
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
}