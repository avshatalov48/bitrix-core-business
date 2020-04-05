<?php
namespace Bitrix\Landing;

class Manifest extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'ManifestTable';

	/**
	 * dd new record.
	 * @param array $fields Params for add.
	 * @return \Bitrix\Main\Result
	 */
	public static function add($fields)
	{
		if (isset($fields['CODE']))
		{
			$res = self::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'=CODE' => $fields['CODE']
				)
			));
			if ($row = $res->fetch())
			{
				return parent::update($row['ID'], $fields);
			}
		}

		return parent::add($fields);
	}

	/**
	 * Get manifest of block by code.
	 * @param string $code Block code.
	 * @param bool $full Full row, not only manifest.
	 * @return array
	 */
	public static function getByCode($code, $full = false)
	{
		static $manifests = array();

		if (!isset($manifests[$code]))
		{
			$res = self::getList(array(
				'select' => array(
					'MANIFEST', 'CONTENT'
				),
				'filter' => array(
					'=CODE' => trim($code)
				)
 			));
			if ($row = $res->fetch())
			{
				$manifests[$code] = $row;
			}
			else
			{
				$manifests[$code] = array();
			}
		}

		return $full ? $manifests[$code] : $manifests[$code]['MANIFEST'];
	}
}