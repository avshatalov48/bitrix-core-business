<?php

namespace Bitrix\Sale\Controller;

/*
 * Error code notation x(category1) xxx(category2) xxx(code category) xxxxx(code) - 2 000 403 00010
 * # category1 (x):
 * Check arguments in BaseAction - 1
 * Action - 2
 *
 * # category2 (xxx):
 * BaseAction - 018
 * AddBasketItemAction - 019
 * DeleteBasketItemAction - 020
 * UpdateBasketItemAction - 021
 * SaveOrderAction - 022
 * GetBasketAction - 023
 * UserConsentRequestAction - 024
 * ChangeBasketItemAction - 025
 * InitiatePayAction - 026
 *
 * # code category (xxx) - http status
 *
 * # code (xxxxx) - any value
 * SaveOrderAction - check fields - 00000 - 09999
 * SaveOrderAction - person type - 01000 - 01999
 * SaveOrderAction - basket - 02000 - 02999
 * SaveOrderAction - properties - 03000 - 03999
 * SaveOrderAction - trading platform - 04000 - 04999
 * SaveOrderAction - user - 05000 - 05999
 * SaveOrderAction - user profile - 06000 - 06999
 * SaveOrderAction - final - 07000 - 07999
 * SaveOrderAction - save - 08000 - 08999
 */

/**
 * Class ErrorEnumeration
 * @package Bitrix\Sale\Controller
 * @internal
 */
class ErrorEnumeration
{
	// \Bitrix\Sale\Controller\Action\Entity\BaseAction
	public const BASE_ACTION_UPPERCASE_KEY = 101840000001;
	public const BASE_ACTION_ACCESS_DENIED = 201840300001;

	// \Bitrix\Sale\Controller\Action\Entity\AddBasketItemAction
	public const ADD_BASKET_ITEM_ACTION_SITE_ID_NOT_FOUND = 201940400001;
	public const ADD_BASKET_ITEM_ACTION_FUSER_ID_NOT_FOUND = 201940400002;
	public const ADD_BASKET_ITEM_ACTION_PRODUCT_NOT_FOUND = 201940400003;
	public const ADD_BASKET_ITEM_ADD_PRODUCT_TO_BASKET = 201950010000;
	public const ADD_BASKET_ITEM_SAVE_BASKET = 201950020000;

	// \Bitrix\Sale\Controller\Action\Entity\DeleteBasketItemAction
	public const DELETE_BASKET_ITEM_ACTION_BASKET_ITEM_NOT_EXIST = 202040400001;
	public const DELETE_BASKET_ITEM_ACTION_ORDER_EXIST = 202050000001;
	public const DELETE_BASKET_ITEM_ACTION_BASKET_LOAD = 202050000002;
	public const DELETE_BASKET_ITEM_ACTION_SAVE_BASKET = 202050010000;

	// \Bitrix\Sale\Controller\Action\Entity\UpdateBasketItemAction
	public const UPDATE_BASKET_ITEM_ACTION_BASKET_ITEM_NOT_EXIST = 202140400001;
	public const UPDATE_BASKET_ITEM_ACTION_ORDER_EXIST = 202150000001;
	public const UPDATE_BASKET_ITEM_ACTION_BASKET_LOAD = 202150000002;
	public const UPDATE_BASKET_ITEM_ACTION_SET_FIELD = 202150010000;
	public const UPDATE_BASKET_ITEM_ACTION_SAVE_BASKET = 202150020000;
	public const UPDATE_BASKET_ITEM_ACTION_CHECK_QUANTITY = 202150030000;

	// \Bitrix\Sale\Controller\Action\Entity\SaveOrderAction
	public const SAVE_ORDER_ACTION_SITE_ID_NOT_FOUND = 202240400001;
	public const SAVE_ORDER_ACTION_FUSER_ID_NOT_FOUND = 202240400002;
	public const SAVE_ORDER_ACTION_PERSON_TYPE_ID_NOT_FOUND = 202240400003;
	public const SAVE_ORDER_ACTION_SET_PERSON_TYPE_ID = 202250001000;
	public const SAVE_ORDER_ACTION_SET_BASKET = 202250002000;
	public const SAVE_ORDER_ACTION_SET_PROPERTIES = 202250003000;
	public const SAVE_ORDER_ACTION_SET_TRADE_BINDINGS = 202250004000;
	public const SAVE_ORDER_ACTION_SET_USER = 202250005000;
	public const SAVE_ORDER_ACTION_SET_PROFILE = 202250006000;
	public const SAVE_ORDER_ACTION_FINAL_ACTIONS = 202250007000;
	public const SAVE_ORDER_ACTION_SAVE = 202250008000;

	// \Bitrix\Sale\Controller\Action\Entity\GetBasketAction
	public const GET_BASKET_ACTION_SITE_ID_NOT_FOUND = 202340400001;
	public const GET_BASKET_ACTION_FUSER_ID_NOT_FOUND = 202340400002;

	// \Bitrix\Sale\Controller\Action\Entity\UserConsentRequestAction
	public const USER_CONSENT_REQUEST_ACTION_ID_NOT_FOUND = 202440400001;

	// \Bitrix\Sale\Controller\Action\Entity\ChangeBasketItemAction
	public const CHANGE_BASKET_ITEM_ACTION_SITE_ID_NOT_FOUND = 202540400001;
	public const CHANGE_BASKET_ITEM_ACTION_FUSER_ID_NOT_FOUND = 202540400002;
	public const CHANGE_BASKET_ITEM_ACTION_BASKET_ID_NOT_FOUND = 202540400003;
	public const CHANGE_BASKET_ITEM_ACTION_PRODUCT_ID_NOT_FOUND = 202540400004;
	public const CHANGE_BASKET_ITEM_ACTION_BASKET_ITEM_LOAD = 202550000001;
	public const CHANGE_BASKET_ITEM_ACTION_PARENT_PRODUCT_LOAD = 202550000002;
	public const CHANGE_BASKET_ITEM_ACTION_PRODUCT_LOAD = 202550000003;
	public const CHANGE_BASKET_ITEM_ACTION_SET_FIELD = 202550000004;
	public const CHANGE_BASKET_ITEM_ACTION_REFRESH_BASKET = 202550000005;
	public const CHANGE_BASKET_ITEM_ACTION_SAVE_BASKET = 202550000006;

	// \Bitrix\Sale\Controller\Action\Entity\InitiatePayAction
	public const INITIATE_PAY_ACTION_PAYMENT_ID_NOT_FOUND = 202640400001;
	public const INITIATE_PAY_ACTION_PAY_SYSTEM_ID_NOT_FOUND = 202640400002;
	public const INITIATE_PAY_ACTION_ACCESS_CODE_NOT_FOUND = 202640400003;
	public const INITIATE_PAY_ACTION_PAYMENT_NOT_FOUND = 202640400004;
	public const INITIATE_PAY_ACTION_ORDER_NOT_FOUND = 202640400005;
	public const INITIATE_PAY_ACTION_PAYMENT_SERVICE_NOT_FOUND = 202640400006;
	public const INITIATE_PAY_ACTION_COMMON_ERROR = 202650000007;
	public const INITIATE_PAY_ACTION_ORDER_STATUS_ERROR = 202650000009;
	public const INITIATE_PAY_ACTION_ORDER_ACCESS_ERROR = 202650000010;
	public const INITIATE_PAY_ACTION_UNABLE_TO_UPDATE_PAYMENT = 202650000011;
	public const INITIATE_PAY_ACTION_PAYMENT_SERVICE_INTERNAL_ERROR = 202650000012;
}
