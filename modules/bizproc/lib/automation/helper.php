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

	public static function prepareUserSelectorEntities(array $documentType, $users, $config = [])
	{
		$result = [];
		$users = (array)$users;
		$documentUserFields = static::getDocumentFields($documentType, 'user');

		foreach ($users as $user)
		{
			if (!is_scalar($user))
				continue;

			if (substr($user, 0, 5) === "user_")
			{
				$user = intval(substr($user, 5));
				if (($user > 0) && !in_array($user, $result))
				{
					$result[] = array(
						'id'         => 'U'.$user,
						'entityId'   => $user,
						'name'       => htmlspecialcharsBx(self::getFormattedUserName($user)),
						'entityType' => 'users'
					);
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

			foreach ($documentUserFields as $id => $field)
			{
				if ($file !== $field['Expression'])
					continue;

				$result[] = array(
					'id' => $id,
					'expression' => $field['Expression'],
					'name' => $field['Name'],
					'type' => 'file'
				);
			}
		}
		return $result;
	}

	public static function convertExpressions($source, array $documentType)
	{
		$source = (string)$source;
		list($ids, $names) = static::getFieldsMap($documentType);

		$converter = function ($matches) use ($ids, $names)
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
			elseif (preg_match('/^A[_0-9]+$/', $matches['object']))
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
		list($ids, $names) = static::getFieldsMap($documentType);

		$converter = function ($matches) use ($ids, $names)
		{
			$matches['mixed'] = htmlspecialcharsback($matches['mixed']);

			if (strpos($matches['mixed'], '~') === 0)
			{
				$len = strpos($matches['mixed'], '#');
				$expression = ($len === false)
					? substr($matches['mixed'], 1)
					: substr($matches['mixed'], 1,$len - 1)
				;
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
					'BaseType' => $field['BaseType'],
					'Expression' => '{{'.$field['Name'].'}}',
					'SystemExpression' => '{=Document:'.$id.'}',
					'Options' => $field['Options']
				);
			}
		}
		return $resultFields;
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

		if (strpos($interval, '=dateadd(') === 0 || strpos($interval, '=workdateadd(') === 0)
		{
			if (strpos($interval, '=workdateadd(') === 0)
			{
				$interval = substr($interval, 13, -1); // cut =workdateadd(...)
				$result['workTime'] = true;
			}
			else
			{
				$interval = substr($interval, 9, -1); // cut =dateadd(...)
			}

			$arguments = explode(',', $interval);
			$result['basis'] = trim($arguments[0]);

			$arguments[1] = trim($arguments[1], '"\'');
			$result['type'] = strpos($arguments[1], '-') === 0 ? DelayInterval::TYPE_BEFORE : DelayInterval::TYPE_AFTER;

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

		return '='.$fn.'('.$interval['basis'].',"'.$add.'")';
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

	private static function getFormattedUserName($userID, $format = '', $htmlEncode = false)
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
			($by = 'id'),
			($order = 'asc'),
			array('ID'=> $userID),
			array(
				'FIELDS' => array(
					'ID',
					'NAME', 'SECOND_NAME', 'LAST_NAME',
					'LOGIN', 'TITLE', 'EMAIL'
				)
			)
		);

		$user = $dbUser ? $dbUser->Fetch() : null;
		return is_array($user) ? \CUser::FormatName($format, $user, true, $htmlEncode) : '';
	}
}