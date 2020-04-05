<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Reference
{
	/**
	 * @return array
	 */
	public static function getRpoCategoriesMap()
	{
		return array(
			0 => 'ORDINARY',
			1 => 'ORDERED',
			2 => 'WITH_DECLARED_VALUE',
			3 => 'ORDINARY',
			4 => 'WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY'
		);
	}

	/**
	 * @return array
	 */
	public static function getQualityCodesList()
	{
		return array(
			'GOOD' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_G'),
			'ON_DEMAND' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_OD'),
			'POSTAL_BOX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_PB'),
			'UNDEF_01' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U1'),
			'UNDEF_02'=> Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U2'),
			'UNDEF_03' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U3'),
			'UNDEF_04' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U4'),
			'UNDEF_05' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U5'),
			'UNDEF_06' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U6'),
			'UNDEF_07' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_QC_U7'),
		);
	}

	/**
	 * @return array
	 */
	public static function getProfilesCategoriesMap()
	{
		return array(
			'POSTAL_PARCEL' => 4,
			'ONLINE_PARCEL' => 23,
			'ONLINE_COURIER' => 24,
			'EMS' => 7,
			'EMS_OPTIMAL' => 34,
			'LETTER' => 2,
			'BANDEROL' => 3,
			'BUSINESS_COURIER' => 30,
			'BUSINESS_COURIER_ES' => 31,
			'PARCEL_CLASS_1' => 47
		);
	}

	/**
	 * @param string $kind
	 * @return string
	 */
	public static function getRpoKind($kind)
	{
		$kinds = array(
			'POSTAL_PARCEL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_01'),
			'ONLINE_PARCEL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_02'),
			'ONLINE_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_03'),
			'EMS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_04'),
			'EMS_OPTIMAL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_05'),
			'LETTER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_06'),
			'BANDEROL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_07'),
			'BUSINESS_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_08'),
			'BUSINESS_COURIER_ES' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_09'),
			'PARCEL_CLASS_1' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RK_09')
		);

		return isset($kinds[$kind]) ? $kinds[$kind] : $kind;
	}

	/**
	 * @param string $category
	 * @return string
	 */
	public static function getRpoCategory($category)
	{
		$categories = array(
			"SIMPLE" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_01'),
			"ORDINARY" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_02'),
			"ORDERED" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_03'),
			"WITH_DECLARED_VALUE" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_04'),
			"WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_05'),
			"WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_06'),
			"COMBINED" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_RC_07'),
		);

		return isset($categories[$category]) ? $categories[$category] : $category;
	}

	/**
	 * @param string $status
	 * @return string
	 */
	public static function getBatchStatus($status)
	{
		$statuses = array(
			'CREATED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_01'),
			'FINALIZED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_02'),
			'SENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_03'),
			'COMPLETED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_04'),
			'ARCHIVED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_05'),
			'DELETED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_BS_06')
		);

		return isset($statuses[$status]) ? $statuses[$status] : $status;
	}

	/**
	 * @param string $errorCode
	 * @param string $method
	 * @return string
	 */
	public static function getErrorDescription($errorCode, $method)
	{
		$errorCodes = array(
			array(
				'METHODS' => array(
					'ALL'
				),
				'CODES' => array(
					'UNDEFINED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_01'),
					'EMPTY_MAIL_CATEGORY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_02'),
					'ILLEGAL_MAIL_CATEGORY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_03'),
					'RESTRICTED_MAIL_CATEGORY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_04'),
					'EMPTY_MAIL_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_05'),
					'ILLEGAL_MAIL_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_06'),
					'EMPTY_ADDRESS_TYPE_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_07'),
					'ILLEGAL_ADDRESS_TYPE_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_08'),
					'EMPTY_MAIL_DIRECT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_09'),
					'ILLEGAL_MAIL_DIRECT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_10'),
					'ILLEGAL_INDEX_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_11'),
					'EMPTY_INDEX_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_12'),
					'EMPTY_REGION_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_13'),
					'EMPTY_PLACE_TO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_14'),
					'EMPTY_INSR_VALUE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_15'),
					'ILLEGAL_INSR_VALUE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_16'),
					'INSR_VALUE_EXCEEDS_MAX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_17'),
					'EMPTY_PAYMENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_18'),
					'ILLEGAL_PAYMENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_19'),
					'NOT_INSURED_PAYMENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_20'),
					'EMPTY_TRANSPORT_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_21'),
					'ILLEGAL_TRANSPORT_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_22'),
					'EMPTY_MASS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_23'),
					'ILLEGAL_MASS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_24'),
					'ILLEGAL_MASS_EXCESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_25'),
					'BARCODE_ERROR' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_26'),
					'TARIFF_ERROR' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_27'),
					'IMP13N_ERROR' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_28'),
					'ILLEGAL_INITIALS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_29'),
					'EMPTY_NUM_ADDRESS_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_30'),
					'ILLEGAL_POSTCODE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_31'),
					'READONLY_STATE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_32'),
					'DIFFERENT_POSTCODE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_33'),
					'DIFFERENT_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_34'),
					'DIFFERENT_TRANSPORT_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_35'),
					'EMPTY_POSTOFFICE_CODE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_36'),
					'ILLEGAL_POSTOFFICE_CODE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_37'),
					'NO_AVAILABLE_POSTOFFICES' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_38'),
					'ACCESS_VIOLATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_39'),
					'NOT_FOUND' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_40'),
					'ALL_SHIPMENTS_SENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_41'),
					'PAST_DUE_DATE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_42'),
					'SENDING_MAIL_FAILED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_43'),
					'DIFFERENT_MAIL_TYPE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_44'),
					'DIFFERENT_MAIL_CATEGORY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_45'),
					'ABSENT_OVERSIZE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_46'),
					'UNEXPECTED_OVERSIZE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_47'),
					'ABSENT_FRAGILE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_48'),
					'UNEXPECTED_FRAGILE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_49'),
					'ABSENT_COURIER_DELIVERY_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_50'),
					'UNEXPECTED_COURIER_DELIVERY_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_51'),
					'ABSENT_ORDER_OF_NOTICE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_52'),
					'UNEXPECTED_ORDER_OF_NOTICE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_53'),
					'ABSENT_SIMPLE_NOTICE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_54'),
					'UNEXPECTED_SIMPLE_NOTICE_POSTMARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_ED_55'),
				)
			),
		);

		$result = '';

		foreach($errorCodes as $item)
		{
			if(!in_array($method, $item['METHODS']) && !in_array('ALL', $item['METHODS']))
				continue;

			if(isset($item['CODES'][$errorCode]))
			{
				$result = $item['CODES'][$errorCode];
				break;
			}
		}

		if(strlen($result) <= 0)
			$result = $errorCode;

		return $result;
	}

	/**
	 * @param string $method
	 * @return string
	 */
	public static function getPaymentMethod($method)
	{
		$methods = array(
			'CASHLESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PM_01'),
			'STAMP' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PM_02'),
			'FRANKING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PM_03')
		);

		return isset($methods[$method]) ? $methods[$method] : $method;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getShipmentNoticeType($type)
	{
		$types = array(
			'SIMPLE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_SNT_01'),
			'ORDERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_SNT_02'),
		);

		return isset($types[$type]) ? $types[$type] : $type;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getTransportType($type)
	{
		$types = array(
			'SURFACE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TT_01'),
			'AVIA' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TT_02'),
			'COMBINED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TT_03'),
			'EXPRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TT_04'),
		);

		return isset($types[$type]) ? $types[$type] : $type;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getPostmarkType($type)
	{
		$types = array(
			'WITHOUT_MARK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_01'),
			'WITH_SIMPLE_NOTICE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_02'),
			'WITH_ORDER_OF_NOTICE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_03'),
			'WITH_INVENTORY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_04'),
			'CAUTION_FRAGILE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_05'),
			'HEAVY_HANDED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_06'),
			'LARGE_BULKY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_07'),
			'WITH_DELIVERY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_08'),
			'AWARDED_IN_OWN_HANDS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_09'),
			'WITH_DOCUMENTS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_10'),
			'WITH_GOODS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_11'),
			'NO_RETURN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_12'),
			'NONSTANDARD' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_13'),
			'INSURED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_14'),
			'WITH_ELECTRONIC_NOTIFICATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_15'),
			'BUSINESS_COURIER_EXPRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_16'),
			'NONSTANDARD_UPTO_10KG' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_17'),
			'NONSTANDARD_UPTO_20KG' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_18'),
			'WITH_CASH_ON_DELIVERY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_19'),
			'SAFETY_GUARANTEE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_20'),
			'ASSURE_PACKAGE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_21'),
			'COURIER_DELIVERY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_22'),
			'COMPLETENESS_CHECKING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_23'),
			'OVERSIZED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_24'),
			'RUPOST_PACKAGE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_PT_25'),
		);

		return isset($types[$type]) ? $types[$type] : $type;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getAddressType($type)
	{
		$types = array(
			'DEFAULT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_AT_01'),
			'PO_BOX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_AT_02'),
			'DEMAND' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_AT_03'),
		);

		return isset($types[$type]) ? $types[$type] : $type;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getEnvelopeType($type)
	{
		$types = array(
			'C4' => '229'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM').' x 324'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM'),
			'C5' => '162'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM').' x 229'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM'),
			'DL' => '220'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM').' x 110'.Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MM')
		);

		return isset($types[$type]) ? $types[$type] : $type;
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	public static function getTrackingAttr($attr)
	{
		$attrs = array(
			'UNKNOWN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_01'),
			'FOREIGN_ACCEPTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_02'),
			'SINGLE_ACCEPTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_03'),
			'PARTIAL_ACCEPTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_04'),
			'PARTIAL_ACCEPTING_REMOTE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_05'),
			'GIVING_COMMON' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_06'),
			'GIVING_RECIPIENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_07'),
			'GIVING_SENDER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_08'),
			'GIVING_RECIPIENT_IN_PO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_09'),
			'GIVING_SENDER_IN_PO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_10'),
			'GIVING_RECIPIENT_REMOTE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_11'),
			'GIVING_RECIPIENT_POSTMAN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_12'),
			'GIVING_SENDER_POSTMAN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_13'),
			'GIVING_RECIPIENT_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_14'),
			'GIVING_SENDER_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_15'),
			'GIVING_RECIPIENT_CONTROL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_16'),
			'GIVING_RECIPIENT_CONTROL_POSTMAN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_17'),
			'GIVING_RECIPIENT_CONTROL_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_18'),
			'RETURNING_BY_EXPIRED_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_19'),
			'RETURNING_BY_SENDER_REQUEST' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_20'),
			'RETURNING_BY_RECEPIENT_ABSENCE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_21'),
			'RETURNING_BY_RECEPIENT_REJECT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_22'),
			'RETURNING_BY_RECEPIENT_DEATH' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_23'),
			'RETURNING_BY_UNREADABLE_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_24'),
			'RETURNING_BY_CUSTOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_25'),
			'RETURNING_BY_UNKNOWN_RECEPIENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_26'),
			'RETURNING_BY_OTHER_REASONS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_27'),
			'RETURNING_BY_WRONG_ADRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_28'),
			'DELIVERING_BY_CLIENT_REQUEST' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_29'),
			'DELIVERING_TO_NEW_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_30'),
			'DELIVERING_BY_ROUTER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_31'),
			'LOST' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_32'),
			'CONFISCATED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_33'),
			'SKIPPING_BY_ERROR' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_34'),
			'SKIPPING_BY_CUSTOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_35'),
			'UNDELIVERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_36'),
			'POSTE_RESTANTE_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_37'),
			'STORING_IN_BOX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_38'),
			'TEMPORAL_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_39'),
			'ADDITIONAL_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_40'),
			'CUSTOM_HOLDING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_41'),
			'UNASSIGNED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_42'),
			'UNCLAIMED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_43'),
			'PROHIBITED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_44'),
			'SORTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_45'),
			'SENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_46'),
			'ARRIVED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_47'),
			'DELIVERED_TO_SORTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_48'),
			'SORTED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_49'),
			'DELIVERED_TO_EXCHANGE_HUB' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_50'),
			'PROCESSED_BY_EXCHANGE_HUB' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_51'),
			'DELIVERED_TO_HUB' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_52'),
			'LEAVED_HUB' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_53'),
			'DELIVERED_TO_PO' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_54'),
			'EXPIRED_PO_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_55'),
			'FORWARDED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_56'),
			'GET' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_57'),
			'ARRIVED_IN_RUSSIA' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_58'),
			'ARRIVED_IN_PARCELS_CENTER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_59'),
			'GIVEN_TO_COURIER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_60'),
			'GIVEN_FOR_REMOTE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_61'),
			'DELIVERED_HYBRID' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_62'),
			'GIVEN_TO_POSTMAN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_63'),
			'GIVEN_FOR_BOXROOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_64'),
			'LEFT_POSTOFFICE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_65'),
			'SPECIFY_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_66'),
			'EXPECTING_COURIER_DELIVERY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_67'),
			'PROLONG_STORAGE_DATE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_68'),
			'NOTIFICATION_SENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_69'),
			'NOTIFICATION_RECEIVED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_70'),
			'POCHTOMAT_ORDERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_71'),
			'POSTMAN_ORDERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_72'),
			'COURIER_ORDERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_73'),
			'IMPORTED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_74'),
			'EXPORTED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_75'),
			'ACCEPTED_BY_CUSTOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_76'),
			'FAILED_BY_TEMPORAL_ABSENCE_OF_RECEPIENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_77'),
			'FAILED_BY_DELAYING_REQUEST' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_78'),
			'FAILED_BY_UNFILLED_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_79'),
			'FAILED_BY_INVALID_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_80'),
			'FAILED_BY_RECEPIENT_LEAVING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_81'),
			'FAILED_BY_RECEPINT_REJECT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_82'),
			'UNOVERCAMING_FAIL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_83'),
			'FAILED_WITH_ANOTHER_REASON' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_84'),
			'WAITING_RECEPIENT_IN_OFFICE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_85'),
			'RECEPIENT_NOT_FOUND' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_86'),
			'TECHNICALLY_FAILED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_87'),
			'REGISTERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_88'),
			'CUSTOM_LEGALIZED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_89'),
			'LEGALIZED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_90'),
			'CANCELED_LEGLIZATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_91'),
			'PROCESSED_BY_CUSTOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_92'),
			'REJECTED_BY_CUSTOM' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_93'),
			'PASSED_WITH_CUSTOM_NOTIFY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_94'),
			'PASSED_WITH_CUSTOM_TAX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_95'),
			'DELIGATED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_96'),
			'DESTROYED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_97'),
			'ACCOUNTED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_98'),
			'LOSS_REGISTERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_99'),
			'CUSTOM_DUTY_RECEIVED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_100'),
			'DM_REGISTERED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_101'),
			'DM_ABSENCE_POSTBOX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_102'),
			'DM_ABSENCE_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_103'),
			'DM_WRONG_POSTOFFICE_INDEX' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_104'),
			'DM_WRONG_ADDRESS' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_105'),
			'TEMPORARY_STORING_ARRIVED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_106'),
			'CUSTOM_STORING_PROLONGED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_107'),
			'CUSTOM_STORING_PROLONGED_1' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_108'),
			'CUSTOM_STORING_PROLONGED_2' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_109'),
			'CUSTOM_STORING_PROLONGED_3' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_110'),
			'CUSTOM_STORING_PROLONGED_4' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_111'),
			'CUSTOM_STORING_PROLONGED_5' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_112'),
			'CUSTOM_STORING_PROLONGED_6' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_113'),
			'CUSTOM_STORING_PROLONGED_7' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_114'),
			'CUSTOM_STORING_PROLONGED_8' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_115'),
			'CUSTOM_STORING_PROLONGED_9' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_116'),
			'OPENED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_117'),
			'CANCELED_BY_SENDER_DEFAULT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_118'),
			'CANCELED_BY_SENDER' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_119'),
			'CANCELED_BY_OPERATOR' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_120'),
			'ID_ASSIGNED' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TA_121'),
		);

		return isset($attrs[$attr]) ? $attrs[$attr] : $attr;
	}

	/**
	 * @param string $operation
	 * @return string
	 */
	public static function getTrackingOper($operation)
	{
		$ops = array(
			'UNKNOWN' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_01'),
			'ACCEPTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_02'),
			'GIVING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_03'),
			'RETURNING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_04'),
			'DELIVERING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_05'),
			'SKIPPING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_06'),
			'STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_07'),
			'HOLDING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_08'),
			'PROCESSING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_09'),
			'IMPORTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_10'),
			'EXPORTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_11'),
			'CUSTOM_ACCEPTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_12'),
			'TRYING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_13'),
			'REGISTERING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_14'),
			'CUSTOM_LEGALIZING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_15'),
			'DELIGATING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_16'),
			'DESTROYING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_17'),
			'ACCOUNTING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_18'),
			'LOSS_REGISTRATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_19'),
			'CUSTOM_DUTY_RECEIVING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_20'),
			'DM_REGISTRATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_21'),
			'DM_DELIVERING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_22'),
			'DM_NON_DELIVERING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_23'),
			'TEMPORARY_STORING_ARRIVING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_24'),
			'PROLONGATION_CUSTOM_STORING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_25'),
			'OPENING' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_26'),
			'CANCELLATION' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_27'),
			'ID_ASSIGNMENT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_TO_28')
		);

		return isset($ops[$operation]) ? $ops[$operation] : $operation;
	}

	/**
	 * @param string $rank
	 * @return string
	 */
	public static function getMailRank($rank)
	{
		$ranks = array(
			'WO_RANK' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_01'),
			'GOVERNMENTAL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_02'),
			'MILITARY' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_03'),
			'OFFICIAL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_04'),
			'JUDICIAL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_05'),
			'PRESIDENTIAL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_06'),
			'CREDIT' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_REF_MR_07')
		);

		return isset($ranks[$rank]) ? $ranks[$rank] : $rank;
	}
}