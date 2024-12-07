<?

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Class Type. Available field types
 * @package Bitrix\Main\UI\Filter
 */
class Type
{
	const STRING = "STRING";
	const TEXTAREA = "TEXTAREA";
	const NUMBER = "NUMBER";
	const DATE = "DATE";
	const SELECT = "SELECT";
	const MULTI_SELECT = "MULTI_SELECT";
	const DEST_SELECTOR = "DEST_SELECTOR";
	const ENTITY_SELECTOR = "ENTITY_SELECTOR";
	const ENTITY = "ENTITY";
	const CUSTOM = "CUSTOM";
	const CUSTOM_ENTITY = "CUSTOM_ENTITY";
	const CUSTOM_DATE = "CUSTOM_DATE";

	protected $list = [];
	protected static $instance;

	public function __construct()
	{
		$constants = (new \ReflectionClass(__CLASS__))->getConstants();
		foreach ($constants as $key)
		{
			$list[$key] = null;
			$filename = str_replace("_", "", mb_strtolower($key))."type.php";
			if (file_exists(__DIR__."/".$filename))
			{
				$className = str_replace('_', ' ', mb_strtolower($key));
				$className = str_replace(' ', '', ucwords($className));
				$list[$key] = __NAMESPACE__."\\".$className."Type";
			}
		}
		$event = new Event(self::class, 'onGetList', $constants);
		$event->send();
		if($event->getResults())
		{
			foreach($event->getResults() as $evenResult)
			{
				if($evenResult->getType() == EventResult::SUCCESS)
				{
					$result = $evenResult->getParameters();
					if (is_array($result) &&
						array_key_exists("CODE_NAME", $result) && !empty($result["CODE_NAME"]) &&
						array_key_exists("CLASS", $result) && !empty($result["CLASS"])
					)
					{
						$list[$result["CODE_NAME"]] = $result["CLASS"];
					}
				}
			}
		}
		$this->list = $list;
	}

	public function getTypesList()
	{
		return $this->list;
	}

	/**
	 * @return Type
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * Gets field types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}

	/**
	 * Picks up from request filter data and converts it to ORM filter.
	 * @param array $data
	 * @param array $sourceFields
	 * @return array
	 */
	public static function getLogicFilter($data, array $sourceFields)
	{
		$types = self::getInstance()->getTypesList();
		$result = [];

		foreach ($sourceFields as $sourceFieldKey => $sourceField)
		{
			$filter = array_merge(
				FieldAdapter::adapt($sourceField),
				[
					"STRICT" => isset($sourceField["strict"]) && $sourceField["strict"] === true
				]
			);
			/*
			 * @todo Make a default type and use it. Not this condition.
			 */
			if (array_key_exists($filter["TYPE"], $types) &&
				class_exists($types[$filter["TYPE"]]) &&
				is_callable(array($types[$filter["TYPE"]], "getLogicFilter")))
			{
				$res = call_user_func_array(array($types[$filter["TYPE"]], "getLogicFilter"), array($data, $filter));
				if (!empty($res))
					$result += $res ;
			}
			else if (array_key_exists($filter["NAME"], $data) && $data[$filter["NAME"]] <> '')
			{
				$result[$filter["NAME"]] = $data[$filter["NAME"]];
			}
		}
		return $result;
	}
}
