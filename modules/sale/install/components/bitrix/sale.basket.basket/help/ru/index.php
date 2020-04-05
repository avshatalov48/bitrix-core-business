<pre>
Array
(
    [INFO] => Array // массив с дополнительной справочной информацией (список статусов, платежных систем, служб доставок.)
        (
            [STATUS] => Array // массив со всеми статусами
                (
                    [N] => Array // ключ массива - идентификатор статуса
                        (
                            [ID] => N 			// идентификатор статуса
                            [SORT] => 100		// индекс сортировки
                            [GROUP_ID] => 30		// группа пользователей, имеющая права на работу с этим статусом
                            [PERM_VIEW] => Y		// права на просмотр заказов в данном статусе
                            [PERM_CANCEL] => N		// права на отмену заказов в данном статусе
                            [PERM_DELIVERY] => N	// права на разрешение доставки заказов в данном статусе
                            [PERM_PAYMENT] => N		// права на оплату заказов в данном статусе
                            [PERM_STATUS] => N		// права на перевод в этот статусе заказов
                            [PERM_UPDATE] => N		// права на изменение заказов в данном статусе
                            [PERM_DELETE] => N		// права на удаление заказов в данном статусе
                            [LID] => ru			// язык статуса
                            [NAME] => Принят		// название статуса
                            [DESCRIPTION] => 		// описание статуса
                        )
                )

            [PAY_SYSTEM] => Array // массив со всеми платежными системами текущего сайта
                (
                    [1] => Array
                        (
                            [ID] => 1
                            [LID] => ru
                            [CURRENCY] => RUR
                            [NAME] => Наличные
                            [ACTIVE] => Y
                            [SORT] => 100
                            [DESCRIPTION] => 
                        )
                )

            [DELIVERY] => Array // массив со всеми службами доставки текущего сайта
                (
                    [1] => Array
                        (
                            [ID] => 1
                            [NAME] => Курьерская
                            [LID] => ru
                            [PERIOD_FROM] => 0
                            [PERIOD_TO] => 3
                            [PERIOD_TYPE] => D
                            [WEIGHT_FROM] => 0
                            [WEIGHT_TO] => 5000
                            [ORDER_PRICE_FROM] => 0.00
                            [ORDER_PRICE_TO] => 1000.00
                            [ORDER_CURRENCY] => USD
                            [ACTIVE] => Y
                            [PRICE] => 60.00
                            [CURRENCY] => EUR
                            [SORT] => 100
                            [DESCRIPTION] => 
                        )
                )
        )

    [ORDERS] => Array
        (
            [0] => Array
                (
                    [ORDER] => Array
                        (
                            [ID] => 33
                            [LID] => ru
                            [PERSON_TYPE_ID] => 1
                            [PAYED] => N
                            [DATE_PAYED] => 
                            [EMP_PAYED_ID] => 
                            [CANCELED] => N
                            [DATE_CANCELED] => 
                            [EMP_CANCELED_ID] => 
                            [REASON_CANCELED] => 
                            [STATUS_ID] => F
                            [DATE_STATUS] => 11.01.2007 16:44:57
                            [PAY_VOUCHER_NUM] => 
                            [PAY_VOUCHER_DATE] => 
                            [EMP_STATUS_ID] => 10
                            [PRICE_DELIVERY] => 0.00
                            [ALLOW_DELIVERY] => Y
                            [DATE_ALLOW_DELIVERY] => 11.01.2007 16:44:57
                            [EMP_ALLOW_DELIVERY_ID] => 10
                            [PRICE] => 12450.00
                            [CURRENCY] => RUR
                            [DISCOUNT_VALUE] => 0.00
                            [SUM_PAID] => 0.00
                            [USER_ID] => 10
                            [PAY_SYSTEM_ID] => 1
                            [DELIVERY_ID] => 
                            [DATE_INSERT] => 11.01.2007 16:06:08
                            [DATE_INSERT_FORMAT] => 11.01.2007 16:06:08
                            [DATE_UPDATE] => 12.01.2007 13:01:20
                            [USER_DESCRIPTION] => 
                            [ADDITIONAL_INFO] => 
                            [PS_STATUS] => 
                            [PS_STATUS_CODE] => 
                            [PS_STATUS_DESCRIPTION] => 
                            [PS_STATUS_MESSAGE] => 
                            [PS_SUM] => 
                            [PS_CURRENCY] => 
                            [PS_RESPONSE_DATE] => 
                            [COMMENTS] => 
                            [TAX_VALUE] => 0.00
                            [STAT_GID] => 
                            [RECURRING_ID] => 
                            [RECOUNT_FLAG] => Y
                            [USER_LOGIN] => anton
                            [USER_NAME] => Anton
                            [USER_LAST_NAME] => Ezhkov
                            [USER_EMAIL] => anton@bitrixsoft.ru
                            [~ID] => 33
                            [~LID] => ru
                            [~PERSON_TYPE_ID] => 1
                            [~PAYED] => N
                            [~DATE_PAYED] => 
                            [~EMP_PAYED_ID] => 
                            [~CANCELED] => N
                            [~DATE_CANCELED] => 
                            [~EMP_CANCELED_ID] => 
                            [~REASON_CANCELED] => 
                            [~STATUS_ID] => F
                            [~DATE_STATUS] => 11.01.2007 16:44:57
                            [~PAY_VOUCHER_NUM] => 
                            [~PAY_VOUCHER_DATE] => 
                            [~EMP_STATUS_ID] => 10
                            [~PRICE_DELIVERY] => 0.00
                            [~ALLOW_DELIVERY] => Y
                            [~DATE_ALLOW_DELIVERY] => 11.01.2007 16:44:57
                            [~EMP_ALLOW_DELIVERY_ID] => 10
                            [~PRICE] => 12450.00
                            [~CURRENCY] => RUR
                            [~DISCOUNT_VALUE] => 0.00
                            [~SUM_PAID] => 0.00
                            [~USER_ID] => 10
                            [~PAY_SYSTEM_ID] => 1
                            [~DELIVERY_ID] => 
                            [~DATE_INSERT] => 11.01.2007 16:06:08
                            [~DATE_INSERT_FORMAT] => 11.01.2007 16:06:08
                            [~DATE_UPDATE] => 12.01.2007 13:01:20
                            [~USER_DESCRIPTION] => 
                            [~ADDITIONAL_INFO] => 
                            [~PS_STATUS] => 
                            [~PS_STATUS_CODE] => 
                            [~PS_STATUS_DESCRIPTION] => 
                            [~PS_STATUS_MESSAGE] => 
                            [~PS_SUM] => 
                            [~PS_CURRENCY] => 
                            [~PS_RESPONSE_DATE] => 
                            [~COMMENTS] => 
                            [~TAX_VALUE] => 0.00
                            [~STAT_GID] => 
                            [~RECURRING_ID] => 
                            [~RECOUNT_FLAG] => Y
                            [~USER_LOGIN] => anton
                            [~USER_NAME] => Anton
                            [~USER_LAST_NAME] => Ezhkov
                            [~USER_EMAIL] => anton@bitrixsoft.ru
                            [FORMATED_PRICE] => 12 450.00 р.
                            [CAN_CANCEL] => N
                        )

                    [BASKET_ITEMS] => Array
                        (
                            [0] => Array
                                (
                                    [ID] => 51
                                    [FUSER_ID] => 20
                                    [ORDER_ID] => 33
                                    [PRODUCT_ID] => 45414
                                    [PRODUCT_PRICE_ID] => 49409
                                    [PRICE] => 12450.00
                                    [CURRENCY] => RUR
                                    [DATE_INSERT] => 11.01.2007 16:05:50
                                    [DATE_UPDATE] => 11.01.2007 16:05:50
                                    [WEIGHT] => 0
                                    [QUANTITY] => 1
                                    [LID] => ru
                                    [DELAY] => N
                                    [NAME] => Битрикс: Управление сайтом - Малый бизнес (MySQL/OracleXE/MS SQL Express)
                                    [CAN_BUY] => Y
                                    [MODULE] => catalog
                                    [CALLBACK_FUNC] => CatalogBasketCallback
                                    [NOTES] => 
                                    [ORDER_CALLBACK_FUNC] => CatalogBasketOrderCallback
                                    [PAY_CALLBACK_FUNC] => CatalogPayOrderCallback
                                    [CANCEL_CALLBACK_FUNC] => CatalogBasketCancelCallback
                                    [DETAIL_PAGE_URL] => 
                                    [DISCOUNT_PRICE] => 0.00
                                    [CATALOG_XML_ID] => 
                                    [PRODUCT_XML_ID] => 23740
                                    [~ID] => 51
                                    [~FUSER_ID] => 20
                                    [~ORDER_ID] => 33
                                    [~PRODUCT_ID] => 45414
                                    [~PRODUCT_PRICE_ID] => 49409
                                    [~PRICE] => 12450.00
                                    [~CURRENCY] => RUR
                                    [~DATE_INSERT] => 11.01.2007 16:05:50
                                    [~DATE_UPDATE] => 11.01.2007 16:05:50
                                    [~WEIGHT] => 0
                                    [~QUANTITY] => 1
                                    [~LID] => ru
                                    [~DELAY] => N
                                    [~NAME] => Битрикс: Управление сайтом - Малый бизнес (MySQL/OracleXE/MS SQL Express)
                                    [~CAN_BUY] => Y
                                    [~MODULE] => catalog
                                    [~CALLBACK_FUNC] => CatalogBasketCallback
                                    [~NOTES] => 
                                    [~ORDER_CALLBACK_FUNC] => CatalogBasketOrderCallback
                                    [~PAY_CALLBACK_FUNC] => CatalogPayOrderCallback
                                    [~CANCEL_CALLBACK_FUNC] => CatalogBasketCancelCallback
                                    [~DETAIL_PAGE_URL] => 
                                    [~DISCOUNT_PRICE] => 0.00
                                    [~CATALOG_XML_ID] => 
                                    [~PRODUCT_XML_ID] => 23740
                                )
                        )
                )
        )
)
</pre>