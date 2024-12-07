<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\Template\Entity;

class ElementSkuPrice extends Base
{
	protected $ids = null;

	/**
	 * @param array $id Entity identifier.
	 */
	protected static function getInstance($id)
	{
		$key = implode(',', $id);
		$class = get_called_class();
		if (!isset(static::$instance[$class]))
		{
			static::$instance[$class] = [];
		}
		if (!isset(static::$instance[$class][$key]))
		{
			static::$instance[$class][$key] = new static($id);
		}
		return static::$instance[$class][$key];
	}

	/**
	 * @param array|mixed $ids Array of iblock element identifiers.
	 */
	public function __construct($ids)
	{
		parent::__construct(0);
		$this->ids = $ids;
	}

	/**
	 * Used to initialize entity fields from some external source.
	 *
	 * @param array $fields Entity fields.
	 *
	 * @return void
	 */
	public function setFields(array $fields)
	{
		parent::setFields($fields);
		if (
			is_array($this->fields)
			//&& $this->fields["MEASURE"] > 0
		)
		{
			//$this->fields["MEASURE"] = new ElementCatalogMeasure($this->fields["MEASURE"]);
			//TODO
		}
	}

	/**
	 * Loads values from database.
	 * Returns true on success.
	 *
	 * @return boolean
	 */
	protected function loadFromDatabase()
	{
		if (!isset($this->fields) && is_array($this->ids))
		{
			$this->fields = array();
			$pricesList =\CPrice::getListEx(array(), array(
				"=PRODUCT_ID" => $this->ids,
				"+<=QUANTITY_FROM" => 1,
				"+>=QUANTITY_TO" => 1,
			), false, false, array("PRICE", "CURRENCY", "CATALOG_GROUP_ID", "CATALOG_GROUP_CODE"));
			$this->fields = array();
			while ($priceInfo = $pricesList->fetch())
			{
				$priceId = $priceInfo["CATALOG_GROUP_ID"];
				$price = \CCurrencyLang::currencyFormat($priceInfo["PRICE"], $priceInfo["CURRENCY"], true);
				$this->fields[$priceId][] = $price;
				$this->addField($priceId, $priceId, $price);
				$this->addField($priceInfo["CATALOG_GROUP_CODE"], $priceId, $price);
			}
		}
		return is_array($this->fields);
	}
}
