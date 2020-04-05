<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

class ShipmentDocument extends DocumentBase
{
	protected static $FIELD_INFOS = null;

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
	static public function getFieldsInfo()
    {
        if(!self::$FIELD_INFOS)
        {
            self::$FIELD_INFOS = array(
				//region export fields
            	/*'XML_1C_DOCUMENT_ID' => array(
				    'TYPE' => 'string'
				),*/
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
				    'TYPE' => 'int'
				),
				'NUMBER' => array(
				    'TYPE' => 'int'
				),
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
				),
				'DATE' => array(
					'TYPE' => 'date'
				),
				'TIME' => array(
					'TYPE' => 'time'
				),
				//endregion
            	'ID' => array(
                    'TYPE' => 'string'
                ),
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
                        'DEDUCTED' => array(
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
                        'DELIVERY_SYSTEM_ID' => array(
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
						//region export requsite.fields
						'PRICE_DELIVERY' => array(
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
						'DATE_ALLOW_DELIVERY' => array(
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
						'DELIVERY_LOCATION' => array(
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
						'DELIVERY_STATUS' => array(
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
						'DELIVERY_DEDUCTED' => array(
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
						'DATE_DEDUCTED' => array(
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
						'REASON_UNDO_DEDUCTED' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'NAME' => array(
									'TYPE' => 'string'
								),
								'VALUE' => array(
									'TYPE' => 'text'
								)
							)
						),
						'RESERVED' => array(
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
						'DELIVERY' => array(
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
						'DELIVERY_DATE_CANCEL' => array(
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
									'TYPE' => 'text'
								)
							)
						),
						'REASON_MARKED' => array(
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
						//'DELIVERY_DEDUCTED' => array('TYPE' => 'bool'),
						//'CANCELED' => array('TYPE' => 'bool'),
						//'DELIVERY_ID' => array('TYPE' => 'int'),
						//'TRACKING_NUMBER' => array('TYPE' => 'string'),
						//endregion
                    )
                ),
                'ITEMS' => array(
                    'TYPE' => 'array',
                    'FIELDS' => array(
						//region export fields items
						'CATALOG_ID' => array(
							'TYPE' => 'string'
						),
						'DISCOUNTS' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'SUMM' => array(
									'TYPE' => 'string'
								),
								'NAME' => array(
									'TYPE' => 'string'
								),
								'IN_PRICE' => array(
									'TYPE' => 'bool'
								),
							)
						),
						'TAX_RATES' => array(
							'TYPE' => 'array',
							'FIELDS' => array(
								'VAT' => array(
									'TYPE' => 'string'
								),
								'RATE' => array(
									'TYPE' => 'float'
								)
							)
						),
						'PRICE_PER_ITEM' => array(
							'TYPE' => 'float'
						),
						//endregion
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
								//region export fields items.requsite
								'PROPERTY_VALUE_BASKET' => array(
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
								'TYPE_OF_NOMENKLATURA' => array(
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
								//endregion
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