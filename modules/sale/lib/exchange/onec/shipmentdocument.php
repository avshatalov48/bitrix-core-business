<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

class ShipmentDocument extends DocumentImport
{
    private static $FIELD_INFOS = null;

    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::SHIPMENT;
    }

    /**
     * @return array
     */
    public static function getFieldsInfo()
    {
        if(!self::$FIELD_INFOS)
        {
            self::$FIELD_INFOS = array(
                'ID' => array(
                    'TYPE' => 'string'
                ),
                //'XML_1C_DOCUMENT_ID' => array(
                //    'TYPE' => 'string'
                //),
                'OPERATION' => array(
                    'TYPE' => 'string'
                ),
                'ORDER_ID' => array(
                    'TYPE' => 'string'
                ),
                'AMOUNT' => array(
                    'TYPE' => 'float'
                ),
                'COMMENT' => array(
                    'TYPE' => 'string'
                ),
                'CANCELED' => array(
                    'TYPE' => 'bool'
                ),
                'VERSION_1C' => array(
                    'TYPE' => 'string'
                ),
                'ID_1C' => array(
                    'TYPE' => 'string'
                ),
                'REK_VALUES' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
                        '1C_DELIVERY_NUM' => array(
                            'TYPE' => 'string'
                        ),
                        '1C_DELIVERY_DATE' => array(
                            'TYPE' => 'datetime'
                        ),
                        'CANCEL' => array(
                            'TYPE' => 'bool'
                        ),
                        'DEDUCTED' => array(
                            'TYPE' => 'bool'
                        ),
                        '1C_TRACKING_NUMBER' => array(
                            'TYPE' => 'string'
                        ),
                        'DELIVERY_SYSTEM_ID' => array(
                            'TYPE' => 'int'
                        ),
                    )
                ),
                'ITEMS' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
                        'ID' => array(
                            'TYPE' => 'string'
                        ),
                        'NAME' => array(
                            'TYPE' => 'string'
                        ),
                        'QUANTITY' => array(
                            'TYPE' => 'float'
                        ),
                        'SUMM' => array(
                            'TYPE' => 'float'
                        ),
                        'PRICE_PER_UNIT' => array(
                            'TYPE' => 'float'
                        ),
                        'PRICE_ONE' => array(
                            'TYPE' => 'float'
                        ),
                        'ITEM_UNIT' => array(
                            'TYPE' => 'array',
                            'FIELDS' => array(
                                'ITEM_UNIT_CODE' => array(
                                    'TYPE' => 'string'
                                ),
                                'ITEM_UNIT_NAME' => array(
                                    'TYPE' => 'string'
                                )
                            )
                        ),
                        'REK_VALUES' => array(
                            'TYPE' => 'array',
                            'FIELDS' => array(
                                'ITEM_TYPE' => array(
                                    'TYPE' => 'string'
                                ),
                                'PROP_BASKET' => array(
                                    'TYPE' => 'string'
                                ),
                            )
                        ),
                        'TAXES' => array(
                            'TYPE' => 'array',
                            'FIELDS' => array(
                                'NAME' => array(
                                    'TYPE' => 'string'
                                ),
                                'TAX_VALUE' => array(
                                    'TYPE' => 'string'
                                )
                            )
                        )
                    )
                ),
                'TAXES' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
                        'SUMM' => array(
                            'TYPE' => 'float'
                        ),
                        'IN_PRICE' => array(
                            'TYPE' => 'bool'
                        )
                    )
                ),
                '1C_DATE' => array(
                    'TYPE' => 'datetime'
                ),
                '1C_TIME' => array(
                    'TYPE' => 'datetime'
                ),
                'AGENT' => array(),
            );
        }
        return self::$FIELD_INFOS;
    }
}