<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage security
 * @copyright 2001-2013 Bitrix
 */

/**
 * Class CSecurityUserTest
 * @since 14.0.3
 */
class CSecurityUserTest
	extends CSecurityBaseTest
{
	protected $internalName = 'UsersTest';
	/** @var CSecurityTemporaryStorage */
	protected $sessionData = null;
	protected $maximumExecutionTime = 0.0;
	protected $savedMaxExecutionTime = 0.0;

	public function __construct()
	{
		IncludeModuleLangFile(__FILE__);
		$this->savedMaxExecutionTime = ini_get("max_execution_time");
		if ($this->savedMaxExecutionTime <= 0)
		{
			$phpMaxExecutionTime = 30;
		}
		else
		{
			$phpMaxExecutionTime = $this->savedMaxExecutionTime - 2;
		}
		$this->maximumExecutionTime = time() + $phpMaxExecutionTime;
		set_time_limit(0);
	}

	public function __destruct()
	{
		set_time_limit($this->savedMaxExecutionTime);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function check(array $params = [])
	{
		if (count($this->tests))
		{
			return parent::check($params);
		}

		$this->initializeParams($params);
		$testID = $this->getParam('TEST_ID', $this->internalName);
		$sessionData = new CSecurityTemporaryStorage($testID);

		if (!$sessionData->isExists('current_user'))
		{
			$user = static::getNextUser(0);
			$passwordId = 0;
		}
		else
		{
			$user = static::getNextUser($sessionData->getInt('current_user'));
			$passwordId = $sessionData->getInt('current_password');
		}

		if ($user && (int)$user['ID'] > 0)
		{
			$userChecked = true;
			$passwordDictionary = static::getPasswordDictionary();
			$hash = $user['PASSWORD'];
			for ($i = $passwordId, $max = count($passwordDictionary); $i < $max; $i++)
			{
				if ($this->isTimeOut())
				{
					$sessionData->setData('current_password', $i);
					$userChecked = false;
					break;
				}
				if (\Bitrix\Main\Security\Password::equals($hash, $passwordDictionary[$i]))
				{
					$sessionData->pushToArray('weak_users', (int)$user['ID']);
					break;
				}
			}

			if ($userChecked)
			{
				$sessionData->setData('current_user', (int)$user['ID']);
			}
			else
			{
				$sessionData->setData('current_user', (int)$user['ID'] - 1);
			}

			$result = [
				'name' => $this->getName(),
				'timeout' => 1,
				'in_progress' => true,
			];
		}
		else
		{
			$weakUsers = $sessionData->getArray('weak_users');
			$sessionData->flushData();
			$result = [
				'name' => $this->getName(),
				'problem_count' => !empty($weakUsers) ? 1 : 0,
				'errors' => [
					[
						'title' => GetMessage('SECURITY_SITE_CHECKER_ADMIN_WEAK_PASSWORD'),
						'critical' => CSecurityCriticalLevel::HIGHT,
						'detail' => GetMessage('SECURITY_SITE_CHECKER_ADMIN_WEAK_PASSWORD_DETAIL'),
						'recommendation' => $result = GetMessage('SECURITY_SITE_CHECKER_ADMIN_WEAK_PASSWORD_RECOMMENDATIONS'),
						'additional_info' => !empty($weakUsers) ? static::formatRecommendation($weakUsers) : '',
					],
				],
				'status' => empty($weakUsers),
			];
		}

		return $result;
	}

	/**
	 * @param array $weakUsers
	 * @return string
	 */
	protected static function formatRecommendation(array $weakUsers)
	{
		$result = getMessage('SECURITY_SITE_CHECKER_ADMIN_WEAK_PASSWORD_USER_LIST');
		foreach (static::getUsersLogins($weakUsers) as $id => $login)
		{
			$result .= sprintf(
				'<br><a href="/bitrix/admin/user_edit.php?ID=%d" target="_blank">%s<a/>',
				$id, htmlspecialcharsbx($login)
			);
		}

		return $result;
	}

	/**
	 * @param int $id
	 * @return int
	 */
	protected static function getNextUser($id)
	{
		$result = null;
		$users = static::getAdminUserList(1, $id);
		if ($user = $users->fetch())
		{
			$result = $user;
		}

		return $result;
	}

	/**
	 * @param int[] $ids
	 * @return array
	 */
	protected static function getUsersLogins(array $ids)
	{
		if (empty($ids))
		{
			return [];
		}

		$dbUser = CUser::GetList(
			'ID',
			'ASC',
			[
				'ID' => implode('|', $ids),
				'ACTIVE' => 'Y',
			],
			[
				'FIELDS' => 'LOGIN',
			]
		);

		$result = [];
		if ($dbUser)
		{
			while ($user = $dbUser->fetch())
			{
				$result[$user['ID']] = $user['LOGIN'];
			}
		}

		return $result;
	}

	protected static function getPasswordDictionary()
	{
		static $passwords = null;

		if (is_null($passwords))
		{
			$passwords = file($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/security/data/passwordlist.txt',
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		}

		return $passwords;
	}

	/**
	 * @param int $limit
	 * @param int $minId
	 * @return CDBResult
	 */
	protected static function getAdminUserList($limit = 0, $minId = 0)
	{
		$dbUser = CUser::GetList(
			'ID',
			'ASC',
			[
				'GROUPS_ID' => 1,
				'>ID' => $minId,
				'ACTIVE' => 'Y',
			],
			[
				'FIELDS' => 'ID',
				'NAV_PARAMS' => [
					'nTopCount' => $limit,
				],
			]
		);

		if ($dbUser)
		{
			return $dbUser;
		}
		else
		{
			return new CDBResult([]);
		}
	}

	/**
	 * @return bool
	 */
	protected function isTimeOut()
	{
		return (time() >= $this->maximumExecutionTime);
	}
}