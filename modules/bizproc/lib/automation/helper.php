<?php

namespace Bitrix\Bizproc\Automation;

use Bitrix\Bizproc\Automation\Engine\DelayInterval;
use Bitrix\Disk;
use Bitrix\Main\Loader;
use Bitrix\Bizproc;

class Helper
{
	const CURRENT_DATE_BASIS = '{=System:Date}';
	const CURRENT_DATETIME_BASIS = '{=System:Now}';

	protected static $maps;
	protected static $documentFields;

	public static function prepareUserSelectorEntities(array $documentType, $users, $config = []): array
	{
		$result = [];
		$users = (array)$users;
		$documentUserFields = static::getDocumentFields($documentType, 'user');
		$documentUserGroups = self::getDocumentUserServiceGroups($documentType);

		foreach ($users as $user)
		{
			if (!is_scalar($user))
				continue;

			if (mb_substr($user, 0, 5) === "user_")
			{
				$user = intval(mb_substr($user, 5));
				if (($user > 0) && !in_array($user, $result))
				{
					$userInfo = self::getUserInfo($user);
					$result[] = [
						'id'         => 'U'.$user,
						'entityId'   => $user,
						'name'       => htmlspecialcharsBx($userInfo['fullName']),
						'photoSrc'       => $userInfo['photoSrc'],
						'url'       => $userInfo['url'],
						'entityType' => 'users',
					];
				}
			}
			elseif ($user === 'author' &&
				(
					isset($documentUserFields['ASSIGNED_BY_ID']) ||
					isset($documentUserFields['RESPONSIBLE_ID'])
				)
			)
			{
				$responsibleKey = isset($documentUserFields['ASSIGNED_BY_ID']) ? 'ASSIGNED_BY_ID' : 'RESPONSIBLE_ID';

				$result[] = array(
					'id'         => $documentUserFields[$responsibleKey]['Expression'],
					'entityId'   => $documentUserFields[$responsibleKey]['Expression'],
					'name'       => htmlspecialcharsBx($documentUserFields[$responsibleKey]['Name']),
					'entityType' => 'bpuserroles'
				);
			}
			elseif (isset($documentUserGroups[$user]))
			{
				$result[] = array(
					'id'         => $user,
					'entityId'   => $user,
					'name'       => htmlspecialcharsBx($documentUserGroups[$user]),
					'entityType' => 'bpuserroles'
				);
			}
			else
			{
				$found = false;
				foreach ($documentUserFields as $field)
				{
					if ($user === $field['Expression'] || $user === $field['SystemExpression'])
					{
						$result[] = array(
							'id'       => $field['Expression'],
							'entityId' => $field['Expression'],
							'name'     => htmlspecialcharsBx($field['Name']),
							'entityType' => 'bpuserroles'
						);
						$found = true;
					}
				}

				if (!$found && isset($config['additionalFields']))
				{
					foreach ($config['additionalFields'] as $field)
					{
						if ($user === $field['entityId'])
						{
							$result[] = array(
								'id'       => $field['id'],
								'entityId' => $field['entityId'],
								'name'     => htmlspecialcharsBx($field['name']),
								'entityType' => 'bpuserroles'
							);
						}
					}
				}
			}
		}
		return $result;
	}

	public static function getResponsibleUserExpression(array $documentType)
	{
		$documentUserFields = static::getDocumentFields($documentType, 'user');
		$result = null;

		if (isset($documentUserFields['ASSIGNED_BY_ID']) || isset($documentUserFields['RESPONSIBLE_ID']))
		{
			$responsibleKey = isset($documentUserFields['ASSIGNED_BY_ID']) ? 'ASSIGNED_BY_ID' : 'RESPONSIBLE_ID';
			$result = '{=Document:'.$responsibleKey.'}';
		}
		elseif (isset($documentUserFields['CREATED_BY']))
		{
			$result = '{=Document:CREATED_BY}';
		}
		return $result;
	}

	/**
	 * Get disk files information by file ids.
	 * @param int|array $attachments
	 * @return array
	 */
	public static function prepareDiskAttachments($attachments)
	{
		$result = array();

		if (!Loader::includeModule('disk'))
			return $result;

		foreach ((array)$attachments as $attachmentId)
		{
			$attachmentId = (int)$attachmentId;
			if ($attachmentId <= 0)
			{
				continue;
			}

			$file = Disk\File::loadById($attachmentId);
			if ($file)
			{
				$result[] = array(
					'id' => $file->getId(),
					'name' => $file->getName(),
					'size' => \CFile::FormatSize($file->getSize()),
					'type' => 'disk'
				);
			}
		}

		return $result;
	}

	/**
	 * Get files information from document fields.
	 * @param array $documentType
	 * @param $files
	 * @return array
	 */
	public static function prepareFileAttachments(array $documentType, $files)
	{
		$result = [];
		$files = (array)$files;
		$documentUserFields = static::getDocumentFields($documentType, 'file');

		foreach ($files as $file)
		{
			if (!is_scalar($file))
				continue;

			$found = false;
			foreach ($documentUserFields as $id => $field)
			{
				if ($file !== $field['Expression'])
					continue;

				$found = true;
				$result[] = array(
					'id' => $id,
					'expression' => $field['Expression'],
					'name' => $field['Name'],
					'type' => 'file'
				);
			}

			if (!$found && mb_strpos($file, '{') === 0)
			{
				$result[] = [
					'id' => $file,
					'expression' => $file,
					'name' => $file,
					'type' => 'file'
				];
			}
		}
		return $result;
	}

	public static function convertExpressions($source, array $documentType, $useTilda = true)
	{
		if (!$source)
		{
			return $source;
		}

		$pattern = \CBPActivity::ValueInlinePattern;
		[$mapIds, $mapNames, $mapObjectNames] = static::getExpressionsMaps($documentType);

		$converter = function ($matches) use ($mapIds, $mapNames, $mapObjectNames, $useTilda)
		{
			$mods = [];
			if (isset($matches['mod1']))
			{
				$mods[] = $matches['mod1'];
			}
			if (isset($matches['mod2']))
			{
				$mods[] = $matches['mod2'];
			}
			$modifiers = ($mods ? ' > ' . implode(',', $mods) : '');

			$objectName = $matches['object'];
			$fieldId = $matches['field'];

			if (in_array($objectName, $mapObjectNames))
			{
				$key = array_search($fieldId, $mapIds[$objectName]);
				if ($key !== false)
				{
					$fieldName = $mapNames[$objectName][$key];

					return '{{' . $fieldName . $modifiers . '}}';
				}
			}
			elseif ($useTilda && $objectName === 'Template')
			{
				return '{{~*:' . $fieldId . $modifiers . '}}';
			}
			elseif ($useTilda && $objectName === 'Constant')
			{
				return '{{~&:' . $fieldId . $modifiers . '}}';
			}
			elseif ($useTilda && preg_match('/^A[_0-9]+$/', $objectName))
			{
				return '{{~' . $objectName . ':' . $fieldId . $modifiers . '}}';
			}

			return $matches[0];
		};

		return preg_replace_callback($pattern, $converter, $source);
	}

	protected static function getExpressionsMaps($documentType): array
	{
		$mapIds = [];
		$mapNames = [];
		$mapObjectNames = [];

		$objectName = \Bitrix\Bizproc\Workflow\Template\SourceType::DocumentField;
		[$ids, $names] = static::getFieldsMap($documentType);
		$mapIds[$objectName] = $ids;
		$mapNames[$objectName] = $names;
		$mapObjectNames[] = $objectName;

		$objectName = \Bitrix\Bizproc\Workflow\Template\SourceType::GlobalVariable;
		[$ids, $names] = static::getGlobalsMap($objectName, $documentType);
		$mapIds[$objectName] = $ids;
		$mapNames[$objectName] = $names;
		$mapObjectNames[] = $objectName;

		$objectName = \Bitrix\Bizproc\Workflow\Template\SourceType::GlobalConstant;
		[$ids, $names] = static::getGlobalsMap($objectName, $documentType);
		$mapIds[$objectName] = $ids;
		$mapNames[$objectName] = $names;
		$mapObjectNames[] = $objectName;

		return [$mapIds, $mapNames, $mapObjectNames];
	}

	public static function unConvertExpressions($source, array $documentType)
	{
		$pattern = '/\{\{(?<mixed>[^=].*?)\}\}/is';
		[$mapIds, $mapNames, $mapObjectNames] = static::getExpressionsMaps($documentType);

		$converter = function ($matches) use ($mapIds, $mapNames, $mapObjectNames)
		{
			$matches['mixed'] = htmlspecialcharsback($matches['mixed']);

			if (mb_strpos($matches['mixed'], '~') === 0)
			{
				$len = mb_strpos($matches['mixed'], '#');
				$expression = ($len === false)
					? mb_substr($matches['mixed'], 1)
					: mb_substr($matches['mixed'], 1, $len - 1)
				;

				if (mb_strpos($expression, '*:') === 0)
				{
					$expression = ltrim($expression,'*');
					$expression = 'Template' . $expression;
				}

				if (mb_strpos($expression, '&:') === 0)
				{
					$expression = ltrim($expression,'&');
					$expression = 'Constant' . $expression;
				}

				return '{=' . trim($expression) . '}';
			}

			$pairs = explode('>', $matches['mixed']);
			$fieldName = '';
			$fieldId = '';
			$objectName = '';

			while (($pair = array_shift($pairs)) !== null)
			{
				$fieldName .= $fieldName ? '>' . $pair : $pair;

				foreach ($mapObjectNames as $object)
				{
					$key = array_search(trim($fieldName), $mapNames[$object]);
					if ($key !== false)
					{
						$objectName = $object;
						$fieldId = $mapIds[$object][$key];
						break;
					}
				}

				if ($fieldId !== '')
				{
					break;
				}
			}

			if (!$fieldId && mb_substr($fieldName, -10) === '_printable')
			{
				$fieldName = mb_substr($fieldName, 0, -10);
				$key = array_search(trim($fieldName), $mapNames['Document']);
				if ($key !== false)
				{
					$objectName = 'Document';
					$fieldId = $mapIds['Document'][$key];
					$pairs[] = 'printable';
				}
			}

			if ($fieldId)
			{
				$mods = isset($pairs[0]) ? trim($pairs[0]) : '';
				$modifiers = $mods ? ' > ' . $mods : '';

				return '{=' . $objectName . ':' . $fieldId . $modifiers . '}';
			}

			return $matches[0];
		};

		return preg_replace_callback($pattern, $converter, $source);
	}

	public static function convertProperties(array $properties, array $documentType, $useTilda = true)
	{
		foreach ($properties as $code => $property)
		{
			if (is_array($property))
			{
				$properties[$code] = self::convertProperties($property, $documentType, $useTilda);
			}
			else
			{
				$properties[$code] = static::convertExpressions($property, $documentType, $useTilda);
			}
		}
		return $properties;
	}

	public static function unConvertProperties(array $properties, array $documentType)
	{
		foreach ($properties as $code => $property)
		{
			if (is_array($property))
			{
				$properties[$code] = self::unConvertProperties($property, $documentType);
			}
			else
			{
				$properties[$code] = static::unConvertExpressions($property, $documentType);
			}
		}
		return $properties;
	}

	/**
	 * Get document fields for usage in robots designer.
	 * @param array $documentType Bizproc document type.
	 * @param null|string $typeFilter
	 * @return array
	 */
	public static function getDocumentFields(array $documentType, $typeFilter = null)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$documentFields[$key]))
		{
			$documentService = \CBPRuntime::getRuntime()->getDocumentService();
			try
			{
				static::$documentFields[$key] = $documentService->GetDocumentFields($documentType);
			}
			catch (\Exception $exception)
			{
				static::$documentFields[$key] = [];
			}
		}

		$resultFields = [];

		if (is_array(static::$documentFields[$key]))
		{
			foreach (static::$documentFields[$key] as $id => $field)
			{
				if ($field['Type'] === 'UF:boolean')
				{
					//Mark as bizproc boolean type
					$field['Type'] = $field['BaseType'] = 'bool';
				}

				if ($field['Type'] === 'UF:date')
				{
					//Mark as bizproc date type
					$field['Type'] = $field['BaseType'] = 'date';
				}

				if ($typeFilter !== null && $field['Type'] !== $typeFilter)
					continue;

				$field['Name'] = trim($field['Name']);

				$resultFields[$id] = [
					'Id' => $id,
					'Name' => $field['Name'],
					'Type' => $field['Type'],
					'BaseType' => $field['BaseType'] ?? $field['Type'],
					'Expression' => '{{' . $field['Name'] . '}}',
					'SystemExpression' => '{=Document:' . $id . '}',
					'Options' => $field['Options'] ?? null,
					'Settings' => $field['Settings'] ?? null,
					'Multiple' => $field['Multiple'] ?? false,
				];
			}
		}

		return $resultFields;
	}

	/** Get global variables for usage in robots designer */
	public static function getGlobalVariables(array $documentType): array
	{
		$globalVariables = Bizproc\Workflow\Type\GlobalVar::getAll($documentType);

		$result = [];
		$visibilityNames = Bizproc\Workflow\Type\GlobalVar::getVisibilityFullNames($documentType);
		foreach ($globalVariables as $id => $variable)
		{
			$name = trim($variable['Name']);
			$visibilityName = $visibilityNames[$variable['Visibility']];

			$result[$id] = [
				'Id' => $id,
				'Name' => $name,
				'Type' => $variable['Type'],
				'BaseType' => $variable['Type'],
				'Expression' => '{{' . $visibilityName . ': ' . $name . '}}',
				'SystemExpression' => '{=' . Bizproc\Workflow\Template\SourceType::GlobalVariable . ':' . $id . '}',
				'Options' => $variable['Options'] ?? null,
				'Multiple' => $variable['Multiple'] ?? false,
				'Visibility' => $variable['Visibility'],
				'VisibilityName' => $visibilityName,
			];
		}

		return $result;
	}

	/** Get global constants for usage in robots designer */
	public static function getGlobalConstants(array $documentType): array
	{
		$globalConstants = Bizproc\Workflow\Type\GlobalConst::getAll($documentType);

		$result = [];
		$visibilityNames = Bizproc\Workflow\Type\GlobalConst::getVisibilityFullNames($documentType);
		foreach ($globalConstants as $id => $constant)
		{
			$name = trim($constant['Name']);
			$visibilityName = $visibilityNames[$constant['Visibility']];

			$result[$id] = [
				'Id' => $id,
				'Name' => $name,
				'Type' => $constant['Type'],
				'BaseType' => $constant['Type'],
				'Expression' => '{{' . $visibilityName . ': ' . $name . '}}',
				'SystemExpression' => '{=' . Bizproc\Workflow\Template\SourceType::GlobalConstant . ':' . $id . '}',
				'Options' => $constant['Options'] ?? null,
				'Multiple' => $constant['Multiple'] ?? false,
				'Visibility' => $constant['Visibility'],
				'VisibilityName' => $visibilityName,
			];
		}

		return $result;
	}

	private static function getDocumentUserServiceGroups(array $documentType)
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		return $documentService->GetAllowableUserGroups($documentType);
	}

	public static function getDocumentUserGroups(array $documentType): array
	{
		$docGroups = self::getDocumentUserServiceGroups($documentType);
		$groups = [];

		if ($docGroups)
		{
			foreach ($docGroups as $id => $groupName)
			{
				if (!$groupName || mb_strpos($id, 'group_') === 0)
				{
					continue;
				}

				$groups[] = [
					'id' => preg_match('/^[0-9]+$/', $id) ? 'G'.$id : $id,
					'name' => $groupName
				];
			}
		}

		return $groups;
	}

	public static function isDocumentUserGroup(string $value, array $documentType): bool
	{
		$documentGroups = static::getDocumentUserGroups($documentType);
		if (empty($documentGroups))
		{
			return false;
		}

		foreach ($documentGroups as $group)
		{
			if ($group['id'] === $value)
			{
				return true;
			}
		}

		return false;
	}

	protected static function getFieldsMap(array $documentType)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$maps[$key]))
		{
			$id = [];
			$name = [];

			$fields = static::getDocumentFields($documentType);
			foreach ($fields as $field)
			{
				$id[] = $field['Id'];
				$name[] = $field['Name'];
			}

			static::$maps[$key] = [$id, $name];
		}

		return static::$maps[$key];
	}

	protected static function getGlobalsMap(string $type, array $documentType)
	{
		switch ($type)
		{
			case \Bitrix\Bizproc\Workflow\Template\SourceType::GlobalConstant:
				$key = 'globals@const@' . implode('@', $documentType);
				if (isset(static::$maps[$key]))
				{
					return static::$maps[$key];
				}
				$globals = static::getGlobalConstants($documentType);
				break;
			case \Bitrix\Bizproc\Workflow\Template\SourceType::GlobalVariable:
				$key = 'globals@var@' . implode('@', $documentType);
				if (isset(static::$maps[$key]))
				{
					return static::$maps[$key];
				}
				$globals = static::getGlobalVariables($documentType);
				break;
			default:
				return [];
		}

		$ids = [];
		$names = [];

		foreach ($globals as $id => $property)
		{
			$ids[] = $id;
			$names[] = $property['VisibilityName'] . ': ' . trim($property['Name']);
		}

		static::$maps[$key] = [$ids, $names];

		return static::$maps[$key];
	}

	public static function parseDateTimeInterval($interval)
	{
		$interval = ltrim((string)$interval, '=');
		$result = [
			'basis' => null,
			'workTime' => false,
			'inTime' => null,
		];

		$values = [
			'i' => 0,
			'h' => 0,
			'd' => 0,
		];

		if (mb_strpos($interval, 'settime(') === 0)
		{
			$interval = mb_substr($interval, 8, -1); // cut settime(...)
			$intervalParts = explode(')', $interval);
			$arguments = explode(',', array_pop($intervalParts));

			$userOffset = count($arguments) > 3 ? array_pop($arguments) : 0;
			$minute = array_pop($arguments);
			$hour = array_pop($arguments);

			$interval = implode(')', $intervalParts) . implode(',', $arguments);
			$result['inTime'] = [(int)$hour, (int)$minute];

			if ($userOffset > 0)
			{
				$result['inTime'][] = (int)$userOffset;
			}
		}

		if (mb_strpos($interval, 'dateadd(') === 0 || mb_strpos($interval, 'workdateadd(') === 0)
		{
			if (mb_strpos($interval, 'workdateadd(') === 0)
			{
				$interval = mb_substr($interval, 12, -1); // cut workdateadd(...)
				$result['workTime'] = true;
			}
			else
			{
				$interval = mb_substr($interval, 8, -1); // cut dateadd(...)
			}

			$arguments = explode(',', $interval);
			$result['basis'] = trim($arguments[0]);

			$arguments[1] = trim(($arguments[1] ?? ''), '"\'');
			$result['type'] = mb_strpos($arguments[1], '-') === 0 ? DelayInterval::TYPE_BEFORE : DelayInterval::TYPE_AFTER;

			preg_match_all('/\s*([\d]+)\s*(i|h|d)\s*/i', $arguments[1], $matches);
			foreach ($matches[0] as $i => $match)
			{
				$values[$matches[2][$i]] = (int)$matches[1][$i];
			}
		}
		elseif (\CBPDocument::IsExpression($interval))
		{
			$result['basis'] = $interval;

			if ($result['basis'] !== static::CURRENT_DATETIME_BASIS)
			{
				$result['type'] = DelayInterval::TYPE_IN;
			}
			else
			{
				$result['type'] = DelayInterval::TYPE_AFTER;
			}
		}

		$minutes = $values['i'] + $values['h'] * 60 + $values['d'] * 60 * 24;

		if ($minutes % 1440 === 0)
		{
			$result['value'] = $minutes / 1440;
			$result['valueType'] = 'd';
		}
		elseif ($minutes % 60 === 0)
		{
			$result['value'] = $minutes / 60;
			$result['valueType'] = 'h';
		}
		else
		{
			$result['value'] = $minutes;
			$result['valueType'] = 'i';
		}

		if (
			!$result['value']
			&& (
				$result['basis'] !== static::CURRENT_DATETIME_BASIS
				|| $result['inTime']
			)
			&& \CBPDocument::IsExpression($result['basis'])
		)
		{
			$result['type'] =  DelayInterval::TYPE_IN;
		}

		return $result + $values;
	}

	public static function getDateTimeIntervalString($interval)
	{
		if (empty($interval['basis']) || !\CBPDocument::IsExpression($interval['basis']))
		{
			$interval['basis'] = static::CURRENT_DATE_BASIS;
		}

		$days = isset($interval['d']) ? (int)$interval['d'] : 0;
		$hours = isset($interval['h']) ? (int)$interval['h'] : 0;
		$minutes = isset($interval['i']) ? (int)$interval['i'] : 0;

		if (isset($interval['value']) && isset($interval['valueType']))
		{
			switch ($interval['valueType'])
			{
				case 'i':
					$minutes = (int)$interval['value'];
					break;
				case 'h':
					$hours = (int)$interval['value'];
					break;
				case 'd':
					$days = (int)$interval['value'];
					break;
			}
		}

		$add = '';

		if ($days > 0)
			$add .= $days.'d';
		if ($hours > 0)
			$add .= $hours.'h';
		if ($minutes > 0)
			$add .= $minutes.'i';

		if ($add && isset($interval['type']) && $interval['type'] === DelayInterval::TYPE_BEFORE)
		{
			$add = '-' . $add;
		}

		$fn = !empty($interval['workTime']) ? 'workdateadd' : 'dateadd';

		if ($fn === 'workdateadd' && $add === '')
		{
			$add = '0d';
		}

		$worker = '';
		if ($fn === 'workdateadd' && isset($interval['worker']))
		{
			$worker = $interval['worker'];
		}

		$result = $interval['basis'];
		$isFunctionInResult = false;
		if ($add)
		{
			$result = $fn . '(' . $interval['basis'] . ',"' . $add . '"' . ($worker ? ',' . $worker : '') . ')';
			$isFunctionInResult = true;
		}

		if (isset($interval['inTime']))
		{
			$result = sprintf(
				'settime(%s, %d, %d%s)',
				$result,
				$interval['inTime'][0] ?? 0,
				$interval['inTime'][1] ?? 0,
				isset($interval['inTime'][2]) ? ", {$interval['inTime'][2]}" : '',
			);
			$isFunctionInResult = true;
		}

		return $isFunctionInResult ? '=' . $result : $result;
	}

	public static function parseTimeString($time)
	{
		$pairs = preg_split('#[\s:]+#', $time);
		$pairs[0] = (int)$pairs[0];
		$pairs[1] = (int)$pairs[1];

		if (count($pairs) === 3)
		{
			if ($pairs[2] == 'pm' && $pairs[0] < 12)
				$pairs[0] += 12;
			if ($pairs[2] == 'am' && $pairs[0] == 12)
				$pairs[0] = 0;
		}

		return array('h' => $pairs[0], 'i' => $pairs[1]);
	}

	public static function countAllRobots(array $documentType, array $statuses): int
	{
		$cnt = 0;
		foreach ($statuses as $status)
		{
			$template = new Engine\Template($documentType, $status);
			if ($template->getId() > 0)
			{
				$cnt += count($template->getActivatedRobots());
			}
		}

		return $cnt;
	}

	private static function getUserInfo($userID, $format = '', $htmlEncode = false)
	{
		$userID = intval($userID);
		if($userID <= 0)
		{
			return '';
		}

		$format = strval($format);
		if($format === '')
		{
			$format = \CSite::GetNameFormat(false);
		}

		$dbUser = \CUser::GetList(
			'id',
			'asc',
			array('ID'=> $userID),
			array(
				'FIELDS' => array(
					'ID',
					'NAME', 'SECOND_NAME', 'LAST_NAME',
					'LOGIN', 'TITLE', 'EMAIL',
					'PERSONAL_PHOTO'
				)
			)
		);

		$user = $dbUser ? $dbUser->Fetch() : null;

		return [
			'fullName' => $user ? \CUser::FormatName($format, $user, true, $htmlEncode) : '',
			'photoSrc' => $user ? \CBPViewHelper::getUserPhotoSrc($user) : null,
			'url' => sprintf('/company/personal/user/%s/', $userID),
		];
	}
}
