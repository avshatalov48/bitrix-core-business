<?
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Request_Query query()
 * @method static EO_Request_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Request_Result getById($id)
 * @method static EO_Request_Result getList(array $parameters = [])
 * @method static EO_Request_Entity getEntity()
 * @method static \Bitrix\Sale\Delivery\Requests\EO_Request createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Delivery\Requests\EO_Request_Collection createCollection()
 * @method static \Bitrix\Sale\Delivery\Requests\EO_Request wakeUpObject($row)
 * @method static \Bitrix\Sale\Delivery\Requests\EO_Request_Collection wakeUpCollection($rows)
 */

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
			'CREATED_BY' => array(
				'data_type' => 'integer',
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateExternalId'),
				'title' => Loc::getMessage('SALE_DLVR_REQ_TBL_EXTERNAL_ID_FIELD'),
			),
			'EXTERNAL_STATUS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalStatus'),
			),
			'EXTERNAL_STATUS_SEMANTIC' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalStatusSemantic'),
			),
			'DELIVERY' => array(
				'data_type' => '\Bitrix\Sale\Delivery\Services\Table',
				'reference' => array('=this.DELIVERY_ID' => 'ref.ID'),
			),
			(new ArrayField('EXTERNAL_PROPERTIES'))
				->configureSerializationPhp()
				->configureUnserializeCallback(function ($value) {
					return unserialize(
						$value,
						['allowed_classes' => false]
					);
				}),
			(new OneToMany('SHIPMENTS', ShipmentTable::class, 'REQUEST'))
				->configureJoinType('inner'),
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

	/**
	 * @return array
	 */
	public static function validateExternalStatus()
	{
		return [
			new Entity\Validator\Length(null, 255),
		];
	}

	/**
	 * @return array
	 */
	public static function validateExternalStatusSemantic()
	{
		return [
			new Entity\Validator\Length(null, 50),
		];
	}
}
