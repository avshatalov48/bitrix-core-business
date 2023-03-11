<?php

namespace Bitrix\Bizproc\Automation\Component;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

abstract class Base extends \CBitrixComponent implements Errorable
{
	/** @var ErrorCollection */
	protected $errorCollection;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	public static function getTemplateViewData(array $template, $documentType)
	{
		foreach ($template['ROBOTS'] as $i => $robot)
		{
			$template['ROBOTS'][$i]['viewData'] = static::getRobotViewData($robot, $documentType);
		}

		return $template;
	}

	public static function getRobotViewData($robot, array $documentType): array
	{
		$availableRobots = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);
		$result = [
			'responsibleLabel' => '',
			'responsibleUrl' => '',
			'responsibleId' => 0,
		];

		$type = mb_strtolower($robot['Type']);
		if (isset($availableRobots[$type]) && isset($availableRobots[$type]['ROBOT_SETTINGS']))
		{
			$settings = $availableRobots[$type]['ROBOT_SETTINGS'];

			if (!empty($settings['RESPONSIBLE_TO_HEAD']) && $robot['Properties'][$settings['RESPONSIBLE_TO_HEAD']] === 'Y')
			{
				$result['responsibleLabel'] = Loc::getMessage('BIZPROC_AUTOMATION_COMPONENT_BASE_TO_HEAD');
			}

			if (isset($settings['RESPONSIBLE_PROPERTY']))
			{
				$users = static::getUsersFromResponsibleProperty($robot, $settings['RESPONSIBLE_PROPERTY']);
				$usersLabel = \CBPHelper::UsersArrayToString($users, [], $documentType, false);

				if ($result['responsibleLabel'] && $usersLabel)
				{
					$result['responsibleLabel'] .= ', ';
				}
				$result['responsibleLabel'] .= $usersLabel;

				if ($users && count($users) === 1 && $users[0] && mb_strpos($users[0], 'user_') === 0)
				{
					$id = (int) \CBPHelper::StripUserPrefix($users[0]);
					$result['responsibleUrl'] = \CComponentEngine::MakePathFromTemplate(
						'/company/personal/user/#user_id#/',
						array('user_id' => $id)
					);
					$result['responsibleId'] = $id;
				}
			}
		}

		return $result;
	}

	protected static function getUsersFromResponsibleProperty(array $robot, $propertyName): ?array
	{
		$value = null;
		$props = $robot['Properties'];
		$path = explode('.', $propertyName);

		foreach ($path as $chain)
		{
			$value = ($props && is_array($props) && isset($props[$chain])) ? $props[$chain] : null;
			$props = $value;
		}

		return $value ? (array)$value : null;
	}
}