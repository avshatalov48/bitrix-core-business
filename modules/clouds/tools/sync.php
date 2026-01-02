<?php
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../../..');
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

$childrenCount = intval($argv[1]);
if ($childrenCount <= 0)
{
	die("Specify number of workers\n");
}

$mod = null;
$children = [];
for ($i = 0; $i < $childrenCount; $i++)
{
	$children[$i] = pcntl_fork();
	if ($children[$i] === 0)
	{
		$mod = $i;
		break;
	}
	elseif ($children[$i] < 0)
	{
		//TODO finish all spawned children.
		die("Fork failed.\n");
	}
}

if (!isset($mod))
{
	while (pcntl_waitpid(0, $status) != -1)
	{
		$childStatus = pcntl_wexitstatus($status);
		echo 'A Child completed with status ' . $childStatus . "\n";
		foreach ($children as $childPid)
		{
			if ($childPid > 0)
			{
				echo 'Killing ' . $childPid . "\n";
				posix_kill($childPid, SIGINT);
			}
		}
		die("Dearest child has died.\n");
	}
}
else
{
	define('NO_KEEP_STATISTIC', true);
	define('NOT_CHECK_PERMISSIONS', true);
	define('BX_CRONTAB', true);
	define('BX_NO_ACCELERATOR_RESET', true);

	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
	while (@ob_end_clean());
	@set_time_limit(0);
	@ignore_user_abort(true);
	echo "\n";

	echo 'Start@' . date('Y-m-d H:i:s')
		. ' pid{' . getmypid() . '}'
		. ' mod{' . $mod . '}'
		. "\n";

	if (!\Bitrix\Main\Loader::includeModule('clouds'))
	{
		die("Module clouds is not installed.\n");
	}

	$lastId = 0;
	$startTime = date('Y-m-d');
	while ($startTime === date('Y-m-d'))
	{
		$taskList = \Bitrix\Clouds\CopyQueueTable::getList([
			'filter' => [
				'=STATUS' => 'Y',
				'>ID' => $lastId,
			],
			'limit' => $childrenCount * 200,
			'order' => ['ID' => 'ASC']
		]);
		$found = false;
		while ($task = $taskList->fetch())
		{
			if (($task['ID'] % $childrenCount) == $mod)
			{
				do
				{
					$found = true;
					if ($task['OP'] == \Bitrix\Clouds\CopyQueueTable::OP_RENAME)
					{
						$res = CCloudFailover::executeRenameTask($task);
					}
					elseif ($task['OP'] == \Bitrix\Clouds\CopyQueueTable::OP_COPY)
					{
						$res = CCloudFailover::executeCopyTask($task, true);
					}
					elseif ($task['OP'] == \Bitrix\Clouds\CopyQueueTable::OP_SYNC)
					{
						$res = CCloudFailover::executeCopyTask($task, false);
					}
					else
					{
						\Bitrix\Clouds\CopyQueueTable::delete($task['ID']);
						$res = 0;
					}

					echo 'task@' . date('Y-m-d H:i:s')
						. ' pid{' . getmypid() . '}'
						. ' mod{' . sprintf('%02d', $mod) . '}'
						. ' ' . $task['TIMESTAMP_X']->format('Y-m-d H:i:s')
						. ' ' . $task['OP']
						. ' ' . $task['ID']
						. ' ' . $res
						. "\n";

					$new = \Bitrix\Clouds\CopyQueueTable::getList([
						'filter' => ['=ID' => $task['ID']],
					])->fetch();
					if (!$new)
					{
						break;
					}

					if ($new['STATUS'] === 'N')
					{
						if (strpos($new['ERROR_MESSAGE'], 'source file does not exists'))
						{
							\Bitrix\Clouds\CopyQueueTable::delete($new['ID']);
							break;
						}
					}
				}
				while ($res === CCloudFailover::ST_CONTINUE);
			}

			$lastId = $task['ID'];
		}

		if (!$found)
		{
			$lastId = 0;
			$deleteStatus = $mod === 0 ? CCloudFailover::ST_CONTINUE : CCloudFailover::ST_FAILOVER; # only one worker
			for ($i = 0; $i < 60; $i++)
			{
				if ($deleteStatus === CCloudFailover::ST_CONTINUE)
				{
					$deleteTask = \Bitrix\Clouds\DeleteQueueTable::getList([
						'limit' => 1,
						'order' => ['ID' => 'ASC']
					])->fetch();
					if ($deleteTask)
					{
						$deleteStatus = CCloudFailover::executeDeleteQueue();
						echo 'task@' . date('Y-m-d H:i:s')
							. ' pid{' . getmypid() . '}'
							. ' mod{' . sprintf('%02d', $mod) . '}'
							. ' ' . $deleteTask['TIMESTAMP_X']->format('Y-m-d H:i:s')
							. ' D'
							. ' ' . $deleteTask['ID']
							. ' ' . $deleteStatus
							. "\n";
					}
				}
				sleep(1);
			}
		}
	}
}
