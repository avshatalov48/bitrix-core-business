<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\IConverter;
use Bitrix\Sale\Exchange\ISettings;

/**
 * Class Converter
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
abstract class Converter implements IConverter
{
	const MEASURE_CODE_DEFAULT = 796;
	const KOEF_DEFAULT = 1;
	const CURRENCY_RATE_DEFAULT = 1;

    /** @var ISettings */
    protected $settings = null;

    /** @var Converter[]|null  */
    private static $instances = null;

	/**
     * @param int $typeId Type ID.
     * @return IConverter
	 * @deprecated
     */
    public static function getInstance($typeId)
    {
        if(!is_int($typeId))
        {
            $typeId = (int)$typeId;
        }

        if(!EntityType::IsDefined($typeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
        }

        if(self::$instances === null || !isset(self::$instances[$typeId]))
        {
            if(self::$instances === null)
            {
                self::$instances = array();
            }

            if(!isset(self::$instances[$typeId]))
            {
				self::$instances[$typeId] = ConverterFactory::create($typeId);
            }
        }
        return self::$instances[$typeId];
    }

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings)
    {
        $this->settings = $settings;
    }

	/**
	 * @return array
	 */
	abstract protected function getFieldsInfo();

    /**
     * @return ISettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

	/**
	 * @return int
	 */
	public function getOwnerEntityTypeId()
	{
		return DocumentType::UNDEFINED;
	}

	/**
	 * @param $value
	 * @param mixed
	 */
	protected function externalizeField(&$value, $fieldInfo=null)
	{
		if($value<>'')
		{
			switch($fieldInfo['TYPE'])
			{
				case 'text':
					$value = self::toText($value);
					break;
				case 'time':
					if($value instanceof DateTime)
						$value = \CAllDatabase::FormatDate($value->toString(), \CAllSite::GetDateFormat("FULL", LANG), "HH:MI:SS");
					break;
				case 'date':
					if($value instanceof DateTime)
						$value = \CAllDatabase::FormatDate($value->toString(), \CAllSite::GetDateFormat("FULL", LANG), "YYYY-MM-DD");
					break;
				case 'datetime':
					if($value instanceof DateTime)
						$value = \CAllDatabase::FormatDate($value->toString(), \CAllSite::GetDateFormat("FULL", LANG), "YYYY-MM-DD HH:MI:SS");
					break;
				case 'bool':
					$value = $value == 'Y'? 'true':'false';
					break;
				case 'int':
					$value = intval($value);
					break;
			}
		}
	}

	/**
	 * @param $value
	 * @return string
	 */
	protected static function toText($value)
	{
		$value = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $value);
		$value = preg_replace('/<blockquote[^>]*>.*?<\/blockquote>/is', '', $value);
		$value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);

		return html_entity_decode(
			strip_tags(
				preg_replace('/(<br[^>]*>)+/is'.BX_UTF_PCRE_MODIFIER, "\n", $value)
			)
		);
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public static function normalizeExternalCode($code)
	{
		$xml_id = $code;
		list($productXmlId, $offerXmlId) = explode("#", $xml_id, 2);
		if ($productXmlId === $offerXmlId)
			$xml_id = $productXmlId;

		return $xml_id;
	}

	/**
	 * @param $rekv
	 * @param array $info
	 * @return array
	 */
	protected function externalizeRekv(array $rekv, array $info)
	{
		$result = array();
		$rowId=0;
		foreach($rekv as $kRekv=>$vRekv)
		{
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch($name)
				{
					case 'NAME':
						$value = $kRekv;
						break;
					case 'VALUE':
						$value = $vRekv;
						if($value instanceof DateTime)
							$fieldInfo['TYPE'] = 'datetime';
						break;
				}
				$this->externalizeField($value, $fieldInfo);
				$result[$rowId][$name] = $value;
			}
			$rowId++;
		}
		return $result;
	}

	/**
	 * @param $rekv
	 * @param array $info
	 * @return array
	 */
	protected function externalizeRekvValue($kRekv, $vRekv, array $info)
	{
		$result = array();
		foreach($info['FIELDS'] as $name=>$fieldInfo)
		{
			$value='';
			switch($name)
			{
				case 'NAME':
					$value = DocumentBase::getLangByCodeField($kRekv);
					break;
				case 'VALUE':
					$value = $vRekv;
					break;
			}
			$this->externalizeField($value, $fieldInfo);
			$result[$name] = $value;
		}
		return $result;
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	protected function modifyTrim(array $fields)
	{

		$result = array();
		foreach ($fields as $key=>$value)
		{
			if(is_array($value))
			{
				if(count($value)>0)
				{
					if($key === 'REK_VALUES' || $key === 'ADDRESS_FIELD' || $key === 'CONTACT' || $key === 'REPRESENTATIVE')
					{
						$groupFieldValues = array();
						foreach ($value as $k=>$v)
						{
							if($v['VALUE']=='')
							{
								unset($fields[$key][$k]);
							}
							$groupFieldValues = $fields[$key];
						}
						if(count($groupFieldValues)>0)
							$result[$key] = $groupFieldValues;
						else
							unset($fields[$key]);
					}
					else
					{
						$value = $this->modifyTrim($value);
						if(count($value)>0)
							$result[$key]=$value;
					}
				}
			}
			else
			{
				if($value<>'')
					$result[$key]=$value;
			}
		}

		return $result;
	}

	/**
	 * @param $lid
	 * @return string
	 */
	static protected function getSiteNameByLid($lid)
	{
		static $sites;

		if($sites === null)
		{
			$res = \CSite::getList($by="sort", $order="desc");
			while ($site = $res->fetch())
			{
				$sites[$site['LID']]=$site['NAME'];
			}

			if(!is_array($sites))
			{
				$sites = array();
			}
		}
		return isset($sites[$lid]) ? $sites[$lid]:'';
	}
}