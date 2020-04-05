<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

class OrderDocument extends DocumentBase
{
	protected static $FIELD_INFOS = null;

    /**
     * @return int
     */
    public function getOwnerEntityTypeId()
    {
        return Exchange\EntityType::ORDER;
    }

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
					'TYPE' => 'int'
				),
				'NUMBER_BASE' => array(
					'TYPE' => 'int'
				),
				'NUMBER' => array(
					'TYPE' => 'string'
				),
				'DISCOUNTS' => array(
					'TYPE' => 'array',
					'FIELDS' => array(
						'NAME' => array(
							'TYPE' => 'string'
						),
						'IN_PRICE' => array(
							'TYPE' => 'bool'
						),
						'AMOUNT' => array(
							'TYPE' => 'string'
						)
					)
				),
				//endregion
				'ID' => array(
					'TYPE' => 'string'
				),
				'OPERATION' => array(
					'TYPE' => 'string'
				),
				'VERSION' => array(
					'TYPE' => 'int'
				),
                'AMOUNT' => array(
                    'TYPE' => 'float'
                ),
                'COMMENT' => array(
                    'TYPE' => 'text'
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
                        '1C_STATUS_ID' => array(
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
                        '1C_DELIVERY_DATE' => array(
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
						'DELIVERY_SYSTEM_ID' => array(
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
						'1C_TRACKING_NUMBER' => array(
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
						//region export fields requsite
						'DATE_PAID' => array(
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
						'PAY_NUMBER' => array(
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
						'DATE_ALLOW_DELIVERY_LAST' => array(
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
						'DELIVERY_SERVICE' => array(
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
						'DELIVERY_ID' => array(
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
						'ORDER_PAID' => array(
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
						'ALLOW_DELIVERY' => array(
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
						'CANCELED' => array(
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
						'FINAL_STATUS' => array(
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
						'ORDER_STATUS' => array(
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
						'ORDER_STATUS_ID' => array(
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
						'DATE_CANCEL' => array(
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
						'CANCEL_REASON' => array(
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
						'DATE_STATUS' => array(
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
						'USER_DESCRIPTION' => array(
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
						'DELIVERY_ADDRESS' => array(
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
                    ),

                ),
                'ITEMS' => array(
                    'TYPE' => 'array',//BASE_UNIT
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
                        'REK_VALUES' => array(
                            'TYPE' => 'array',
                            'FIELDS' => array(
								'PROPERTY_VALUE_BASKET' => array( // import
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
								'TYPE_OF_NOMENKLATURA' => array( // import
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
								'TYPE_NOMENKLATURA' => array(
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
								'BASKET_NUMBER' => array(
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
                                ),
								'IN_PRICE' => array(
									'TYPE' => 'bool'
								)
                            )
                        ),
						'DISCOUNTS' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'SUMM' => array(
									'TYPE' => 'string'
								),
								//region export fields discount
								'NAME' => array(
									'TYPE' => 'string'
								),
								'IN_PRICE' => array(
									'TYPE' => 'bool'
								),
								//endregion
							)
						),
						//region export fields items
						'PRICE_PER_ITEM' => array(
							'TYPE' => 'float'
						),
						'CATALOG_ID' => array(
							'TYPE' => 'string'
						),
						'TAX_RATES' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'RATE' => array(
									'TYPE' => 'float'
								)
							)
						),
						//endregion
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
                        ),
						//region export fields taxes
						'NAME' => array(
							'TYPE' => 'string'
						)
						//endregion
                    )
                ),
                '1C_DATE' => array(
                    'TYPE' => 'datetime'
                ),
                '1C_TIME' => array(
                    'TYPE' => 'datetime'
                ),
				//region export fields stories
                'STORIES' => array(
                	'TYPE' => 'array',
					'FIELDS' => array(
						'ID'=> array(
							'TYPE' => 'string'
						),
						'NAME'=> array(
							'TYPE' => 'string'
						),
						'ADDRESS' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'PRESENTATION' => array(
									'TYPE' => 'string'
								),
								'ADDRESS_FIELD' => array(
									'TYPE' => 'array',
									'FIELDS' => array(
										'STREET' => array(
											'TYPE' => 'array',
											'FIELDS' => array(
												'TYPE' => array(
													'TYPE' => 'string'
												),
												'VALUE' => array(
													'TYPE' => 'string'
												)
											)
										)
									)
								)
							)
						),
						'CONTACTS' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'CONTACT' => array(
									'TYPE' => 'array',
									'FIELDS' => array(
										'WORK_PHONE_NEW' => array(
											'TYPE' => 'array',
											'FIELDS' => array(
												'TYPE' => array(
													'TYPE' => 'string'
												),
												'VALUE' => array(
													'TYPE' => 'string'
												)
											)
										)
									)
								)
							)
						)
					)
				), // schemes element
                //endregion
				'AGENT'=>array()
            );

			static::unitFieldsInfo(self::$FIELD_INFOS);
			static::koefFieldsInfo(self::$FIELD_INFOS);
        }
        return self::$FIELD_INFOS;
    }

	static protected function unitFieldsInfo(&$info)
	{
		$info['ITEMS']['FIELDS']['ITEM_UNIT'] = array(
				'TYPE' => 'array',
				'FIELDS' => array(
					'ITEM_UNIT_CODE' => array(
						'TYPE' => 'int'
					),
					'ITEM_UNIT_NAME' => array(
						'TYPE' => 'string'
					)
				)
			);
	}

	static protected function koefFieldsInfo(&$info)
	{//export
		$info['ITEMS']['FIELDS']['KOEF'] = array(
			'TYPE' => 'string'
		);
	}
}