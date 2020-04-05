<?
namespace Bitrix\Scale;

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
* Class ActionModifyer
* @package Bitrix\Scale
*/
class ActionModifyer
{
	/**
	 * MYSQL_ADD_SLAVE action modifyer
	 * @param string $actionId - action idenifyer
	 * @param array $actionParams - action parameterss
	 * @param string $hostname - server hostname
	 * @param array $userParamsValues
	 * @return array - modifyed action params
	 * @throws NeedMoreUserInfoException
	 */
	public static function mysqlAddSlave($actionId, $actionParams, $hostname, $userParamsValues)
	{
		$action =  new Action("MYSQL_ADD_SLAVE_MODIFYER", array(
				"START_COMMAND_TEMPLATE" => "sudo -u root /opt/webdir/bin/bx-mysql -a options -o json",
				"LOG_LEVEL" => Logger::LOG_LEVEL_DISABLE
			),
			"",
			array()
		);

		$action->start();
		$actRes = $action->getResult();

		$needModeInfo = false;

		if(isset($actRes["MYSQL_ADD_SLAVE_MODIFYER"]["OUTPUT"]["DATA"]["params"]["options"])
			&& is_array($actRes["MYSQL_ADD_SLAVE_MODIFYER"]["OUTPUT"]["DATA"]["params"]["options"])
		)
		{
			foreach($actRes["MYSQL_ADD_SLAVE_MODIFYER"]["OUTPUT"]["DATA"]["params"]["options"] as $option)
			{
				if($option == "cluster_password" || $option == "replica_password")
				{
					$actionParams["START_COMMAND_TEMPLATE"] .=" --".$option."=".\Bitrix\Scale\Helper::generatePass();
				}
				elseif($option == "mysql_password")
				{
					$actionParams["START_COMMAND_TEMPLATE"] .=" --".$option."=##USER_PARAMS:MYSQL_PASS##";

					if(!isset($actionParams["USER_PARAMS"]))
						$actionParams["USER_PARAMS"] = array();

					$actionParams["USER_PARAMS"]["MYSQL_PASS"] = array(
							"NAME" => Loc::getMessage("SCALE_AM_MYAR_MYSQL_PASS"),
							"TYPE" => "PASSWORD",
							"REQUIRED" => "Y",
							"VERIFY_TWICE" => "Y"
					);

					$needModeInfo = true;
				}
			}

			if($needModeInfo)
				throw new NeedMoreUserInfoException("Need more user's info", $actionParams);
		}

		return $actionParams;
	}

	/**
	 * MYSQL_ADD_SLAVE, MYSQL_CHANGE_MASTER, MYSQL_DEL_SLAVE actions modifier/
	 * @param string $actionId - action idenifyer
	 * @param array $actionParams - action parameters
	 * @param string $hostname - server hostname
	 * @param array $userParamsValues
	 * @return array - modifyed action params
	 * @throws NeedMoreUserInfoException
	 */
	public static function checkExtraDbExist($actionId, $actionParams, $hostname, $userParamsValues)
	{
		if($actionId == "MYSQL_ADD_SLAVE" || $actionId == "MYSQL_CHANGE_MASTER")
			$hostname = ServersData::getDbMasterHostname();

		if(Helper::isExtraDbExist($hostname))
		{
			$actionParams["CHECK_EXTRA_DB_USER_ASK"] = "Y";
			throw new NeedMoreUserInfoException("Need more user's info", $actionParams);
		}

		return $actionParams;
	}

	/**
	 * SET_EMAIL_SETTINGS modifier
	 * @param string $actionId
	 * @param array $actionParams
	 * @param string $hostname
	 * @param array $userParamsValues
	 * @return array mixed
	 */
	public static function emailSettingsModifier($actionId, $actionParams, $hostname, $userParamsValues)
	{
		if($actionId != 'SET_EMAIL_SETTINGS')
			return $actionParams;

		if($userParamsValues['USE_AUTH'] == 'Y')
			$pattern = '/(--8<--AUTH_BEGIN----|----AUTH_END--8<--)/';
		else
			$pattern = '/--8<--AUTH_BEGIN----.*----AUTH_END--8<--/';

		$actionParams['START_COMMAND_TEMPLATE'] = preg_replace($pattern, '', $actionParams['START_COMMAND_TEMPLATE']);
		return $actionParams;
	}

	/**
	 * SITE_CREATE_LINK modifier
	 * @param string $actionId
	 * @param array $actionParams
	 * @param string $hostname
	 * @param array $userParamsValues
	 * @return array mixed
	 */
	public static function siteCreateLinkModifier($actionId, $actionParams, $hostname, $userParamsValues)
	{
		if($actionId != 'SITE_CREATE_LINK')
			return $actionParams;

		if(empty($userParamsValues['KERNEL_SITE']))
			return $actionParams;

		$siteId = $userParamsValues['KERNEL_SITE'];
		$sites = SitesData::getList();

		if(empty($sites[$siteId]))
			return $actionParams;

		$actionParams['START_COMMAND_TEMPLATE'] =  str_replace(
			'##MODIFYER:KERNEL_ROOT##',
			$sites[$siteId]['DocumentRoot'],
			$actionParams['START_COMMAND_TEMPLATE']
		);

		return $actionParams;
	}
}
