<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access;


class AccessCode
{
	public const
		ACCESS_DIRECTOR = 'AD',
		ACCESS_EMPLOYEE = 'AE';

	public const
		AC_DEPARTMENT 		= '^(D)(\d+)$',
		AC_ALL_DEPARTMENT 	= '^(DR)(\d+)$',
		AC_INTRANET_USER 	= '^(IU)(\d+)$',
		AC_GROUP 			= '^(G)(\d+)$',
		AC_USER 			= '^(U)(\d+)$',
		AC_SOCNETGROUP 		= '^(SG)(\d+)(_[AEK])?$',
		AC_SOCNETUSER 		= '^(SU)(\d+)(_M)?$',
		AC_CHAT				= '^(CHAT)(\d+)$',
		AC_ACCESS_DIRECTOR	= '^('.self::ACCESS_DIRECTOR.')(\d+)?$',
		AC_ACCESS_EMPLOYEE	= '^('.self::ACCESS_EMPLOYEE.')(\d+)?$';

	public const
		TYPE_USER 				= 'users',
		TYPE_USER_ALL			= 'users_all',
		TYPE_GROUP	 			= 'groups',
		TYPE_SOCNETGROUP 		= 'sonetgroups',
		TYPE_DEPARTMENT			= 'departments',
		TYPE_ACCESS_DIRECTOR	= 'access_director',
		TYPE_ACCESS_EMPLOYEE	= 'access_employee',
		TYPE_CHAT				= 'chat',
		TYPE_OTHER				= 'other';

	public static $map = [
		self::AC_DEPARTMENT 		=> self::TYPE_DEPARTMENT,
		self::AC_ALL_DEPARTMENT 	=> self::TYPE_DEPARTMENT,
		self::AC_INTRANET_USER 		=> self::TYPE_USER,
		self::AC_GROUP 				=> self::TYPE_GROUP,
		self::AC_USER 				=> self::TYPE_USER,
		self::AC_SOCNETGROUP 		=> self::TYPE_SOCNETGROUP,
		self::AC_SOCNETUSER 		=> self::TYPE_OTHER,
		self::AC_CHAT 				=> self::TYPE_CHAT,
		self::AC_ACCESS_DIRECTOR	=> self::TYPE_ACCESS_DIRECTOR,
		self::AC_ACCESS_EMPLOYEE	=> self::TYPE_ACCESS_EMPLOYEE,
	];

	private $accessCode;

	private $parsed = false;
	private $entityType = self::TYPE_OTHER;
	private $entityPrefix = '';
	private $entityId = 0;

	/**
	 * @param $code
	 * @return bool
	 */
	public static function isValid($code): bool
	{
		foreach (self::$map as $pattern => $type)
		{
			if (preg_match('/'. $pattern .'/', $code, $matches))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $accessCode
	 */
	public function __construct(string $accessCode)
	{
		$this->accessCode = $accessCode;
		$this->parse();
	}

	/**
	 * @return string|null
	 */
	public function getSignature(): ?string
	{
		if (!$this->parsed)
		{
			return null;
		}
		return $this->entityPrefix . $this->entityId;
	}

	/**
	 * @return string
	 */
	public function getEntityType(): string
	{
		return $this->entityType;
	}

	/**
	 * @return string
	 */
	public function getEntityPrefix(): string
	{
		return $this->entityPrefix;
	}

	/**
	 * @return int
	 */
	public function getEntityId(): int
	{
		return $this->entityId;
	}

	/**
	 * @return void
	 */
	private function parse()
	{
		foreach (self::$map as $pattern => $type)
		{
			if (preg_match('/'. $pattern .'/', $this->accessCode, $matches))
			{
				$this->parsed = true;
				$this->entityType = $type;
				$this->entityPrefix = (string) $matches[1];
				if (array_key_exists('2', $matches))
				{
					$this->entityId = (int) $matches[2];
				}
				return;
			}
		}
	}
}