<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
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
    /** @var ISettings */
    protected $settings = null;

    /** @var Converter[]|null  */
    private static $instances = null;

    /**
     * @param int $typeId Type ID.
     * @return IConverter
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
                if($typeId === EntityType::ORDER)
                {
                    self::$instances[$typeId] = new ConverterDocumentOrder();
                }
                elseif($typeId === EntityType::SHIPMENT)
                {
                    self::$instances[$typeId] = new ConverterDocumentShipment();
                }
                elseif($typeId === EntityType::PAYMENT_CASH ||
                    $typeId === EntityType::PAYMENT_CASH_LESS ||
                    $typeId === EntityType::PAYMENT_CARD_TRANSACTION)
                {
                    self::$instances[$typeId] = new ConverterDocumentPayment();
                }
                elseif($typeId == EntityType::PROFILE ||
					$typeId == EntityType::USER_PROFILE)
                {
                    self::$instances[$typeId] = new ConverterDocumentProfile();
                }
                else
                {
                    throw new NotSupportedException("Entity type: '".EntityType::ResolveName($typeId)."' is not supported in current context");
                }
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
     * @return ISettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}