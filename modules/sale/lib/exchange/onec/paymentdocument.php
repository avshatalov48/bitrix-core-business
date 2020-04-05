<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

class PaymentDocument extends DocumentImport
{
    private static $FIELD_INFOS = null;

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
				'CASH_BOX_CHECKS' => array(
					'ID' => array(
						'TYPE' => 'string'
					),
					'TYPE' => 'array',
					'PROPERTIES' => array(
						'CASHBOX_URL' => array(
							'TYPE' => 'string'
						),
						'CASHBOX_FISCAL_SIGN' => array(
							'TYPE' => 'int'
						),
						'CASHBOX_REG_NUMBER_KKT' => array(
							'TYPE' => 'int'
						),
						'CASHBOX_PRINT_CHECK' => array(
							'TYPE' => 'bool'
						),
					)
				),
                'REK_VALUES' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
                        '1C_PAYED_DATE' => array(
                            'TYPE' => 'datetime'
                        ),
                        '1C_PAYED_NUM' => array(
                            'TYPE' => 'string'
                        ),
                        'CANCEL' => array(
                            'TYPE' => 'bool'
                        ),
                        '1C_RETURN' => array(
                            'TYPE' => 'bool'
                        ),
                        '1C_RETURN_REASON' => array(
                            'TYPE' => 'string'
                        ),
                        '1C_PAYED' => array(
                            'TYPE' => 'bool'
                        ),
						'PAY_SYSTEM_ID' => array(
							'TYPE' => 'int'
						),
                    )
                )
            );
        }
        return self::$FIELD_INFOS;
    }
}

class PaymentCashDocument extends PaymentDocument
{
    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::PAYMENT_CASH;
    }
}

class PaymentCashLessDocument extends PaymentDocument
{
    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::PAYMENT_CASH_LESS;
    }
}

class PaymentCardDocument extends PaymentDocument
{

    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::PAYMENT_CARD_TRANSACTION;
    }

}