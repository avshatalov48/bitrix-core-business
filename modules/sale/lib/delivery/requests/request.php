<?
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class RequestTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATE datetime mandatory
 * <li> DELIVERY_ID int mandatory
 * <li> STATUS int optional
 * <li> EXTERNAL_ID string(100) optional
 * </ul>
 *
 * @package Bitrix\Sale\Delivery\Requests
 **/

class RequestTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_delivery_req';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_ID_FIELD'),
			),
			'DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_DATE_INSERT_FIELD'),
			),
			'DELIVERY_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_DELIVERY_ID_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_STATUS_FIELD'),
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateExternalId'),
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_EXTERNAL_ID_FIELD'),
			),
			'DELIVERY' => array(
				'data_type' => '\Bitrix\Sale\Delivery\Services\Table',
				'reference' => array('=this.DELIVERY_ID' => 'ref.ID'),
			)
		);
	}
	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
}
