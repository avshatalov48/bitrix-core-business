<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class DiscountGroupTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DISCOUNT_ID int mandatory
 * <li> ACTIVE string(1) optional
 * <li> GROUP_ID int mandatory
 * <li> DISCOUNT reference to {@link \Bitrix\Sale\Internals\DiscountTable}
 * </ul>
 *
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DiscountGroup_Query query()
 * @method static EO_DiscountGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DiscountGroup_Result getById($id)
 * @method static EO_DiscountGroup_Result getList(array $parameters = [])
 * @method static EO_DiscountGroup_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_DiscountGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_DiscountGroup_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_DiscountGroup wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_DiscountGroup_Collection wakeUpCollection($rows)
 */

class DiscountGroupTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_discount_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('DISCOUNT_GROUP_ENTITY_ID_FIELD')
			)),
			'DISCOUNT_ID' => new Main\Entity\IntegerField('DISCOUNT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_GROUP_ENTITY_DISCOUNT_ID_FIELD')
			)),
			'ACTIVE' => new Main\Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('DISCOUNT_GROUP_ENTITY_ACTIVE_FIELD')
			)),
			'GROUP_ID' => new Main\Entity\IntegerField('GROUP_ID', array(
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_GROUP_ENTITY_GROUP_ID_FIELD')
			)),
			'DISCOUNT' => new Main\Entity\ReferenceField(
				'DISCOUNT',
				'Bitrix\Sale\Internals\Discount',
				array('=this.DISCOUNT_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}

	/**
	 * Delete user group list by discount.
	 *
	 * @param int $discount			Discount id.
	 * @return void
	 */
	public static function deleteByDiscount($discount)
	{
		$discount = (int)$discount;
		if ($discount <= 0)
			return;
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('DISCOUNT_ID').' = '.$discount
		);
	}

	/**
	 * Update user group list by discount.
	 *
	 * @param int $discount			Discount id.
	 * @param array $groupList		User group list.
	 * @param string $active		Discount active flag.
	 * @param bool $clear			Clear old values.
	 * @return bool
	 */
	public static function updateByDiscount($discount, $groupList, $active, $clear)
	{
		$discount = (int)$discount;
		if ($discount <= 0)
			return false;
		$clear = ($clear === true);
		if ($clear)
		{
			self::deleteByDiscount($discount);
		}
		if (is_array($groupList))
		{
			$active = (string)$active;
			if ($active != 'Y' && $active != 'N')
			{
				$discountIterator = self::getList(array(
					'select' => array('ACTIVE'),
					'filter' => array('=ID' => $discount)
				));
				if ($discountActive = $discountIterator->fetch())
				{
					$active = $discountActive['ACTIVE'];
				}
				unset($discountActive, $discountIterator);
			}
			if ($active == 'Y' || $active == 'N')
			{
				if (empty($groupList))
					$groupList[] = -1;

				foreach ($groupList as &$group)
				{
					$fields = array(
						'DISCOUNT_ID' => $discount,
						'ACTIVE' => $active,
						'GROUP_ID' => $group
					);
					$result = self::add($fields);
				}
				unset($group);
			}
		}
		return true;
	}

	/**
	 * Change active flag in table by discount.
	 *
	 * @param int $discount			Discount id.
	 * @param string $active		Discount active flag.
	 * @return void
	 */
	public static function changeActiveByDiscount($discount, $active)
	{
		$discount = (int)$discount;
		$active = (string)$active;
		if ($discount <= 0 || ($active != 'Y' && $active != 'N'))
			return;
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'update '.$helper->quote(self::getTableName()).
			' set '.$helper->quote('ACTIVE').' = \''.$active.'\' where '.
			$helper->quote('DISCOUNT_ID').' = '.$discount
		);
	}

	/**
	 * Returns discount list by user groups.
	 *
	 * @param array $groupList			User group list.
	 * @param array $filter				Additional filter.
	 * @return array
	 */
	public static function getDiscountByGroups($groupList, $filter = array())
	{
		$result = array();
		if (!empty($groupList) && is_array($groupList))
		{
			Main\Type\Collection::normalizeArrayValuesByInt($groupList);
			if (!empty($groupList))
			{
				if (!is_array($filter))
					$filter = array();

				$groupRows = array_chunk($groupList, 500);
				foreach ($groupRows as &$row)
				{
					$filter['@GROUP_ID'] = $row;

					$groupIterator = self::getList(array(
						'select' => array('DISCOUNT_ID'),
						'filter' => $filter
					));
					while ($group = $groupIterator->fetch())
					{
						$group['DISCOUNT_ID'] = (int)$group['DISCOUNT_ID'];
						$result[$group['DISCOUNT_ID']] = true;
					}
					unset($group, $groupIterator);
				}
				unset($row, $groupRows);
				if (!empty($result))
					$result = array_keys($result);
			}
		}
		return $result;
	}

	/**
	 * Return active discounts by user group list.
	 *
	 * @param array $groupList			User group list.
	 * @return array
	 */
	public static function getActiveDiscountByGroups($groupList)
	{
		return self::getDiscountByGroups($groupList, array('ACTIVE' => 'Y'));
	}
}