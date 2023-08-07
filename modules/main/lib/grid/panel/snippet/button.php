<?

namespace Bitrix\Main\Grid\Panel\Snippet;


use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Types;


/**
 * Button for group actions panel
 * @package Bitrix\Main\Grid\Panel\Snippet
 */
class Button
{
	protected $id = "";
	protected $name = "";
	protected $type = "";
	protected $class = "";
	protected $text = "";
	/**
	 * @var Onchange
	 */
	protected $onchange;
	protected $title = "";

	public function __construct()
	{
		$this->type = Types::BUTTON;
		$this->id = 'panel_control_'.uniqid();
	}


	/**
	 * Sets button title
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title = "")
	{
		$this->title = $title;
		return $this;
	}


	/**
	 * Sets button id
	 * @param string $id
	 * @return $this
	 */
	public function setId($id = "")
	{
		$this->id = $id;
		return $this;
	}


	/**
	 * Sets button name attribute value
	 * @param string $name
	 * @return $this
	 */
	public function setName($name = "")
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * Sets button class name
	 * @param string $class
	 * @return $this
	 */
	public function setClass($class = "")
	{
		$this->class = $class;
		return $this;
	}


	/**
	 * Sets button text
	 * @param string $text
	 * @return $this
	 */
	public function setText($text = "")
	{
		$this->text = $text;
		return $this;
	}


	/**
	 * Sets actions on button click
	 * @param Onchange $onchange
	 * @return $this
	 */
	public function setOnchange(Snippet\Onchange $onchange)
	{
		$this->onchange = $onchange;
		return $this;
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$result = array(
			"TYPE" => $this->type,
			"ID" => $this->id,
			"NAME" => $this->name,
			"CLASS" => $this->class,
			"TEXT" => $this->text,
			"TITLE" => $this->title,
			"ONCHANGE" => $this->onchange->toArray()
		);

		return $result;
	}
}
