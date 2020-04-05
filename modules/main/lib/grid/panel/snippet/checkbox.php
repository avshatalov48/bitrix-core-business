<?

namespace Bitrix\Main\Grid\Panel\Snippet;


use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Types;


/**
 * Group action panel checkbox
 * @package Bitrix\Main\Grid\Panel\Snippet
 */
class Checkbox
{
	protected $id = "";
	protected $name = "";
	protected $class = "";
	protected $text = "";
	/**
	 * @var Onchange
	 */
	protected $onchange;
	protected $value = "";

	public function __construct()
	{
		$this->type = Types::CHECKBOX;
		$this->id = 'panel_control_'.uniqid();
	}


	/**
	 * Sets checkbox value
	 * @param string $value
	 * @return $this
	 */
	public function setValue($value = "")
	{
		$this->value = $value;
		return $this;
	}


	/**
	 * Sets checkbox id
	 * @param string $id
	 * @return $this
	 */
	public function setId($id = "")
	{
		$this->id = $id;
		return $this;
	}


	/**
	 * Sets checkbox name attribute value
	 * @param string $name
	 * @return $this
	 */
	public function setName($name = "")
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * Sets checkbox class name
	 * @param string $class
	 * @return $this
	 */
	public function setClass($class = "")
	{
		$this->class = $class;
		return $this;
	}


	/**
	 * Sets checkbox label text
	 * @param string $text
	 * @return $this
	 */
	public function setText($text = "")
	{
		$this->text = $text;
		return $this;
	}


	/**
	 * Sets actions on checkbox change
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
			"LABEL" => $this->text,
			"VALUE" => $this->value,
			"ONCHANGE" => $this->onchange->toArray()
		);

		return $result;
	}
}