<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

class PaymentDocument extends DocumentBase
{
	protected static $FIELD_INFOS = null;

    /**
     * @return array
     */
	static public function getFieldsInfo()
    {
        if(!self::$FIELD_INFOS)
        {
            self::$FIELD_INFOS = array(
				//region export fields
				/*'XML_1C_DOCUMENT_ID' => array(
					'TYPE' => 'string'
				),*/
				'DATE' => array(
					'TYPE' => 'date'
				),
				'TIME' => array(
					'TYPE' => 'time'
				),
				'ROLE' => array(
					'TYPE' => 'string'
				),
				'CURRENCY' => array(
				    'TYPE' => 'string'
				),
				'CURRENCY_RATE' => array(
				    'TYPE' => 'string'
				),
				'VERSION' => array(
					'TYPE' => 'string'
				),
				'NUMBER_BASE' => array(
					'TYPE' => 'string'
				),
				'NUMBER' => array(
					'TYPE' => 'int'
				),
				//endregion
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

					'PROPERTIES' => array(
						'TYPE' => 'array',
						'FIELDS' => array(
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
							)
						)
					),
					//region export checks.fields
					'PROP_VALUES' => array(
						'TYPE'=>'array',
						'FIELDS'=>array(
							'ID'=> array(
								'TYPE'=>'string'
							),
							'VALUE'=> array(
								'TYPE'=>'bool'
							)
						)
					)
					//endregion
				),
                'REK_VALUES' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
						//region export fields
                    	'PAYED_DATE' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'datetime'
								)
							)
						),
						'PAY_SYSTEM' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
						),
						'PAY_PAID' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'bool'
								)
							)
						),
						'PAY_RETURN' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'bool'
								)
							)
						),
						'PAY_RETURN_REASON' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
						),
						'SITE_NAME' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
						),
						'REKV' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
						),
						//endregion
                    	'1C_PAYED_DATE' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'datetime'
								)
							)
                        ),
                        '1C_PAYED_NUM' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
                        ),
                        'CANCEL' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'bool'
								)
							)
                        ),
                        '1C_RETURN' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'bool'
								)
							)
                        ),
                        '1C_RETURN_REASON' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'string'
								)
							)
                        ),
                        '1C_PAYED' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'bool'
								)
							)
                        ),
						'PAY_SYSTEM_ID' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'int'
								)
							)
						),
                    )
                )
            );
        }
        return self::$FIELD_INFOS;
    }

	/**
	 * @param int $level
	 * @return string
	 */
	public function output($level=0)
	{
		$fields = $this->getFieldValues();
		$xml = parent::outputXml($fields, $level);

		foreach ($fields as $name=>$value)
		{
			if(is_array($value))
			{
				switch ($name)
				{
					case 'CASH_BOX_CHECKS':
						$xml .= $this->outputXmlCashBoxChecks($level+0, $name, array($value));
						break;
				}
			}
		}

		return $xml;
	}

	protected function outputXmlCashBoxChecks($level, $name, $checks)
	{
		$result ='';
		$result .= $this->openNodeDirectory($level+0, 'CASHBOX_CHECKS');

		foreach ($checks as $check)
		{
			$result .= $this->openNodeDirectory($level+1, 'CASHBOX_CHECK');
			foreach ($check as $code=>$value)
			{
				if(is_array($value))
				{
					switch ($code)
					{
						case 'PROP_VALUES':
							$result .= $this->openNodeDirectory($level+2, $code);
							$result .= $this->openNodeDirectory($level+3, 'PROP_VALUE');
							foreach ($value as $k=>$v)
								$result .= $this->formatXMLNode($level+4, $k, $v);
							$result .= $this->closeNodeDirectory($level+3, 'PROP_VALUE');
							$result .= $this->closeNodeDirectory($level+2, $code);
							break;
					}
				}
				else
					$result .= $this->formatXMLNode($level+2, $code, $value);
			}
			$result .= $this->closeNodeDirectory($level+1, 'CASHBOX_CHECK');
		}
		$result .= $this->closeNodeDirectory($level+0, 'CASHBOX_CHECKS');
		return $result;
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