<?php
namespace Bitrix\Sale\Domain\Verification\Internals;

use Bitrix\Main;

/**
 * Class DomainVerificationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DOMAIN string(255) mandatory
 * <li> PATH string(255) mandatory
 * <li> CONTENT string optional
 * <li> ENTITY string(1024) mandatory
 * </ul>
 *
 * @package Bitrix\Main
 **/

class DomainVerificationTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_domain_verification';
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
			),
			'DOMAIN' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateDomain'),
			),
			'PATH' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validatePath'),
			),
			'CONTENT' => array(
				'data_type' => 'text',
			),
			'ENTITY' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateEntity'),
			),
		);
	}

	/**
	 * Returns validators for DOMAIN field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateDomain()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for PATH field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validatePath()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for ENTITY field.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateEntity()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1024),
		);
	}
}