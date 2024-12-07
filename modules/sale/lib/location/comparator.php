<?
namespace Bitrix\Sale\Location;

use Bitrix\Main\Loader;
use Bitrix\Sale\Location\Comparator\Replacement;
use Bitrix\Main\ArgumentOutOfRangeException;

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Bitrix\Sale\Location\Comparator\Replacement' => 'lib/location/comparator/ru/replacement.php',
	)
);

class Comparator
{
	const LOCALITY = 0;
	const DISTRICT = 1;
	const REGION = 2;
	const COUNTRY = 3;

	public static $variants = null;
	public static $replacement = null;

	public static function isLocationsEqual($location1, $location2)
	{
		foreach($location1 as $type => $name)
		{
			if(empty($location2[$type]))
				continue;

			if($location1[$type] <> '' && $location2[$type] <> '')
			{
				/** @var Comparator  $comparator */
				$comparator = self::getConcreteComparatorClassaName($type);

				if(!$comparator::isEntityEqual($location1[$type], $location2[$type]))
					return false;
			}
		}

		return true;
	}

	public static function getReplacement()
	{
		if(self::$replacement === null)
			self::setReplacement();

		return self::$replacement;
	}

	public static function setReplacement(Replacement $replacement = null)
	{
		if($replacement === null)
			self::$replacement = new Replacement;
		else
			self::$replacement = $replacement;
	}

	public static function isCountryRussia($countryName)
	{
		return self::getReplacement()->isCountryRussia($countryName);
	}

	/**
	 * @param int|string $type.
	 * @return string Comparator class name.
	 * @throws ArgumentOutOfRangeException
	 */
	private static function getConcreteComparatorClassaName($type)
	{
		if($type === self::LOCALITY || $type === 'LOCALITY' || $type == 'CITY')
			$result = 'ComparatorLocality';
		elseif($type === self::DISTRICT || $type === 'SUBREGION')
			$result = 'ComparatorDistrict';
		elseif($type === self::REGION || $type === 'REGION')
			$result = 'ComparatorRegion';
		elseif($type === self::COUNTRY || $type === 'COUNTRY')
			$result = 'ComparatorCountry';
		else
			throw new ArgumentOutOfRangeException('type');

		return '\Bitrix\Sale\Location\\'.$result;
	}

	public static function isEntityEqual($entity1, $entity2, $type = '')
	{
		if($type <> '')
		{
			/** @var Comparator  $comparator */
			$comparator = self::getConcreteComparatorClassaName($type);
			return 	$comparator::isEntityEqual($entity1, $entity2);
		}

		if(is_array($entity1) && !empty($entity1['NAME']))
		{
			$entity1N = array('NAME' => $entity1['NAME']);
			$entity1N['TYPE'] = !empty($entity1['TYPE']) ? $entity1['TYPE'] : '';
		}
		else
		{
			$entity1N = static::normalize($entity1);
		}

		if(is_array($entity2) && !empty($entity2['NAME']))
		{
			$entity2N = array('NAME' => $entity2['NAME']);
			$entity2N['TYPE'] = !empty($entity2['TYPE']) ? $entity2['TYPE'] : '';
		}
		else
		{
			$entity2N = static::normalize($entity2);
		}

		if($entity1N['NAME'] <> '' && $entity2N['NAME'] <> '')
			if($entity1N['NAME'] != $entity2N['NAME'])
				return false;

		if($entity1N['TYPE'] <> '' && $entity2N['TYPE'] <> '')
			if($entity1N['TYPE'] != $entity2N['TYPE'])
				return false;

		return true;
	}

	protected static function getTypes()
	{
		return array();
	}

	protected static function getVariantsValues()
	{
		if(static::$variants === null)
		{
			static::setVariantsValues(array());
		}

		return static::$variants;
	}

	public static function setVariantsValues(array $variants = array())
	{
		static::$variants = $variants;
	}

	public static function setVariants(array $variants = array())
	{
		foreach($variants as $type => $v)
		{
			/** @var Comparator  $comparator */
			$comparator = self::getConcreteComparatorClassaName($type);
			$comparator::setVariantsValues(
				self::normalizeVariants($v)
			);
		}
	}

	public static function flatten($value)
	{
		$result = preg_replace('/\s*(\(.*\))/iu', ' ', $value);
		$result = preg_replace('/[~\'\"\`\!\@\#\$\%\^\&\*\+\=\\\.\,\?\:\;\{\}\[\]\-]/iu', ' ', $result);
		$result = preg_replace('/\s{2,}/iu', ' ', $result);
		$result = mb_strtoupper($result);
		$result = self::getReplacement()->changeYoE($result);
		$result = trim($result);

		return $result;
	}

	protected static function normalizeVariants(array $variants)
	{
		$result = array();

		foreach($variants as $k => $v)
			$result[self::flatten($k)] = self::flatten($v);

		return $result;
	}

	public static function normalizeEntity($name, $type)
	{
		/** @var Comparator $comparator */
		$comparator = self::getConcreteComparatorClassaName($type);
		return $comparator::normalize($name);
	}

	// Gadyukino d. | Derevnya Gadyukino  => array( 'NAME' => 'Gadykino', 'TYPE' => 'DEREVNYA'
	protected static function normalize($name)
	{
		$name = self::flatten($name);

		if($name == '')
			return array('NAME' => '', 'TYPE' => '');

		$matches = array();
		$types = static::getTypes();
		$resultType = '';
		$variants = static::getVariantsValues();

		foreach($variants as $wrong => $correct)
		{
			if($name == self::flatten($wrong))
			{
				$name = $correct;
				break;
			}
		}

		foreach($types as $type => $search)
		{
			if(!is_array($search))
				continue;

			$search[] = $type;

			foreach($search as $s)
			{
				$regexp = '';
				$s = self::flatten($s);

				if(mb_strpos($name, $s.' ') !== false)
					$regexp = '/^'.$s.'\s+(.*)$/iu';
				elseif(mb_strpos($name, ' '.$s) !== false)
					$regexp = '/^(.*)\s+'.$s.'$/iu';

				if($regexp <> '' && preg_match($regexp, $name, $matches))
				{
					$name = $matches[1];
					$resultType = $type;
					break 2;
				}
			}
		}

		return array(
			'NAME' => $name,
			'TYPE' => $resultType
		);
	}

	public static function getLocalityNamesArray($name, $type)
	{
		if($name == '')
			return array();

		$result = array();
		$types = self::getReplacement()->getLocalityTypes();

		if($type <> '')
		{
			$result[] = mb_strtoupper($type.' '.$name);
			$result[] = mb_strtoupper($name.' '.$type);

			if(is_array($types[$type]) && !empty($types[$type]))
			{
				foreach($types[$type] as $t)
				{
					$result[] = mb_strtoupper($t.' '.$name);
					$result[] = mb_strtoupper($name.' '.$t);
				}
			}
		}
		else
		{
			foreach($types as $k => $v)
			{
				$result[] = mb_strtoupper($k.' '.$name);
				$result[] = mb_strtoupper($name.' '.$k);

				if(is_array($v) && !empty($v))
				{
					foreach($v as $vv)
					{
						$result[] = mb_strtoupper($vv.' '.$name);
						$result[] = mb_strtoupper($name.' '.$vv);
					}
				}
			}
		}

		return $result;
	}
}

class ComparatorLocality extends Comparator
{
	public static $variants = null;

	protected static function getTypes()
	{
		return self::getReplacement()->getLocalityTypes();
	}
}

class ComparatorDistrict extends Comparator
{
	public static $variants = null;

	protected static function getTypes()
	{
		return self::getReplacement()->getDistrictTypes();
	}
}

class ComparatorRegion extends Comparator
{
	public static $variants = null;

	protected static function getTypes()
	{
		return self::getReplacement()->getRegionTypes();
	}

	public static function setVariantsValues(array $variants = array())
	{
		static::$variants = static::normalizeVariants(
			array_merge(
				self::getReplacement()->getRegionVariants(),
				$variants
			)
		);
	}
}

class ComparatorCountry extends Comparator
{
	public static $variants = null;

	public static function setVariantsValues(array $variants = array())
	{
		static::$variants = static::normalizeVariants(
			array_merge(
				self::getReplacement()->getCountryVariants(),
				$variants
			)
		);
	}
}