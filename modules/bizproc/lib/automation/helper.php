<?php
namespace Bitrix\Bizproc\Automation;

use Bitrix\Bizproc\Automation\Engine\DelayInterval;
use Bitrix\Disk;
use Bitrix\Main\Loader;

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
		$source = (string)$source;
		[$ids, $names] = static::getFieldsMap($documentType);

		$converter = function ($matches) use ($ids, $names, $useTilda)
		{
			$mods = [];
			if ($matches['mod1'])
			{
				$mods[] = $matches['mod1'];
			}
			if ($matches['mod2'])
			{
				$mods[] = $matches['mod2'];
			}

			if ($matches['object'] === 'Document')
			{
				$key = array_search($matches['field'], $ids);
				if ($key !== false)
				{
					$fieldName = $names[$key];
					return '{{'.$fieldName. ($mods? ' > '.implode(',', $mods) : '').'}}';
				}
			}
			elseif ($useTilda && $matches['object'] === 'Template')
			{
				return '{{~*:'.$matches['field']. ($mods? ' > '.implode(',', $mods) : '').'}}';
			}
			elseif ($useTilda && $matches['object'] === 'Constant')
			{
				return '{{~&:'.$matches['field']. ($mods? ' > '.implode(',', $mods) : '').'}}';
			}
			elseif ($useTilda && preg_match('/^A[_0-9]+$/', $matches['object']))
			{
				return '{{~'.$matches['object'].':'.$matches['field']. ($mods? ' > '.implode(',', $mods) : '').'}}';
			}

			return $matches[0];
		};

		$source = preg_replace_callback(
			\CBPActivity::ValueInlinePattern,
			$converter,
			$source
		);

		return $source;
	}

	public static function unConvertExpressions($source, array $documentType)
	{
		$source = (string)$source;
		[$ids, $names] = static::getFieldsMap($documentType);

		$converter = function ($matches) use ($ids, $names)
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
					$expression = 'Template'.$expression;
				}

				if (mb_strpos($expression, '&:') === 0)
				{
					$expression = ltrim($expression,'&');
					$expression = 'Constant'.$expression;
				}

				return '{='.trim($expression).'}';
			}

			$pairs = explode('>', $matches['mixed']);
			$fieldName = $fieldId = '';

			while (($pair = array_shift($pairs)) !== null)
			{
				$fieldName .= $fieldName? '>'.$pair : $pair;

				$key = array_search(trim($fieldName), $names);
				if ($key !== false)
				{
					$fieldId = $ids[$key];
					break;
				}
			}

			if (!$fieldId && mb_substr($fieldName, -10) === '_printable')
			{
				$fieldName = mb_substr($fieldName, 0,-10);
				$key = array_search(trim($fieldName), $names);
				if ($key !== false)
				{
					$fieldId = $ids[$key];
					$pairs[] = 'printable';
				}
			}

			if ($fieldId)
			{
				$mods = isset($pairs[0]) ? trim($pairs[0]) : '';
				return '{=Document:'.$fieldId.($mods? ' > '.$mods : '').'}';
			}

			return $matches[0];
		};

		$source = preg_replace_callback(
			'/\{\{(?<mixed>[^=].*?)\}\}/is',
			$converter,
			$source
		);

		return $source;
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
			$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
			static::$documentFields[$key] = $documentService->GetDocumentFields($documentType);
		}

		$resultFields = array();

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

				$resultFields[$id] = array(
					'Id' => $id,
					'Name' => $field['Name'],
					'Type' => $field['Type'],
					'BaseType' => $field['BaseType'] ?? $field['Type'],
					'Expression' => '{{'.$field['Name'].'}}',
					'SystemExpression' => '{=Document:'.$id.'}',
					'Options' => $field['Options'],
					'Multiple' => $field['Multiple'] ?? false,
				);
			}
		}
		return $resultFields;
	}

	private static function getDocumentUserServiceGroups(array $documentType)
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
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

	protected static function getFieldsMap(array $documentType)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$maps[$key]))
		{
			$id = $name = [];

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

	public static function parseDateTimeInterval($interval)
	{
		$interval = (string)$interval;
		$result = array(
			'basis' => null,
			'i' => 0,
			'h' => 0,
			'd' => 0,
			'workTime' => false
		);

		if (mb_strpos($interval, '=dateadd(') === 0 || mb_strpos($interval, '=workdateadd(') === 0)
		{
			if (mb_strpos($interval, '=workdateadd(') === 0)
			{
				$interval = mb_substr($interval, 13, -1); // cut =workdateadd(...)
				$result['workTime'] = true;
			}
			else
			{
				$interval = mb_substr($interval, 9, -1); // cut =dateadd(...)
			}

			$arguments = explode(',', $interval);
			$result['basis'] = trim($arguments[0]);

			$arguments[1] = trim($arguments[1], '"\'');
			$result['type'] = mb_strpos($arguments[1], '-') === 0 ? DelayInterval::TYPE_BEFORE : DelayInterval::TYPE_AFTER;

			preg_match_all('/\s*([\d]+)\s*(i|h|d)\s*/i', $arguments[1], $matches);
			foreach ($matches[0] as $i => $match)
			{
				$result[$matches[2][$i]] = (int)$matches[1][$i];
			}
		}
		elseif (\CBPDocument::IsExpression($interval))
			$result['basis'] = $interval;

		$minutes = $result['i'] + $result['h'] * 60 + $result['d'] * 60 * 24;

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
			&& $result['basis'] !== static::CURRENT_DATETIME_BASIS
			&& \CBPDocument::IsExpression($result['basis'])
		)
		{
			$result['type'] =  DelayInterval::TYPE_IN;
		}

		return $result;
	}

	public static function getDateTimeIntervalString($interval)
	{
		if (!$interval['basis'] || !\CBPDocument::IsExpression($interval['basis']))
			$interval['basis'] = static::CURRENT_DATE_BASIS;

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
		if (isset($interval['type']) && $interval['type'] == DelayInterval::TYPE_BEFORE)
			$add = '-';

		if ($days > 0)
			$add .= $days.'d';
		if ($hours > 0)
			$add .= $hours.'h';
		if ($minutes > 0)
			$add .= $minutes.'i';

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

		return '='.$fn.'('.$interval['basis'].',"'.$add.'"'.($worker ? ','.$worker : '').')';
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
				$cnt += count($template->getRobots());
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