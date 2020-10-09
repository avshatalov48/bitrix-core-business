<?

namespace Bitrix\Main\UI\Filter;


/**
 * Class NumberType. Subtypes of number field
 * @package Bitrix\Main\UI\Filter
 */
class NumberType
{
	const SINGLE = "exact"; // =
	const RANGE = "range"; // <= =>
	const MORE = "more"; // >
	const LESS = "less"; // <


	/**
	 * Gets number field types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}

	/**
	 * Returns postfix for request.
	 * @return string
	 */
	public static function getPostfix()
	{
		return "_numsel";
	}

	/**
	 * Search in plain array data that can belongs to this type.
	 * @param array $data
	 * @param array $filterFields
	 * @return array
	 */
	public static function getLogicFilter(array $data, array $filterFields)
	{
		$filter = [];
		$keys = array_filter($data, function($key) { return (mb_substr($key, 0 - mb_strlen(self::getPostfix())) == self::getPostfix()); }, ARRAY_FILTER_USE_KEY);
		foreach ($keys as $key => $val)
		{
			$id = mb_substr($key, 0, 0 - mb_strlen(self::getPostfix()));
			switch($val)
			{
				case self::SINGLE:
					if (array_key_exists($id."_from", $data))
						$filter["=".$id] = $data[$id."_from"];
					else if (array_key_exists($id."_to", $data))
						$filter["=".$id] = $data[$id."_to"];
					break;
				case self::RANGE:
					if (array_key_exists($id."_from", $data))
						$filter[">=".$id] = $data[$id."_from"];
					if (array_key_exists($id."_to", $data))
						$filter["<=".$id] = $data[$id."_to"];
					break;
				case self::MORE:
					if (array_key_exists($id."_from", $data))
						$filter[">".$id] = $data[$id."_from"];
					break;
				case self::LESS:
					if (array_key_exists($id."_to", $data))
						$filter["<".$id] = $data[$id."_to"];
					break;
			}
		}
		return $filter;
	}
}