<?php

use Bitrix\Main\Localization\Loc;

class CBPActivityExecutionStatus
{
	const Initialized = 0;
	const Executing = 1;
	const Canceling = 2;
	const Closed = 3;
	const Faulting = 4;

	public static function out($v)
	{
		$result = "";

		switch ($v)
		{
			case self::Initialized:
				$result = "Initialized";
				break;
			case self::Executing:
				$result = "Executing";
				break;
			case self::Canceling:
				$result = "Canceling";
				break;
			case self::Closed:
				$result = "Closed";
				break;
			case self::Faulting:
				$result = "Faulting";
				break;
			default:
				throw new Exception("UnknownActivityExecutionStatus");
		}

		return $result;
	}
}

class CBPActivityExecutionResult
{
	const None = 0;
	const Succeeded = 1;
	const Canceled = 2;
	const Faulted = 3;
	const Uninitialized = 4;

	public static function out($v)
	{
		$result = "";

		switch ($v)
		{
			case self::None:
				$result = "None";
				break;
			case self::Succeeded:
				$result = "Succeeded";
				break;
			case self::Canceled:
				$result = "Canceled";
				break;
			case self::Faulted:
				$result = "Faulted";
				break;
			case self::Uninitialized:
				$result = "Uninitialized";
				break;
			default:
				throw new Exception("UnknownActivityExecutionResult");
		}

		return $result;
	}
}

class CBPWorkflowStatus
{
	const Created = 0;
	const Running = 1;
	const Completed = 2;
	const Suspended = 3;
	const Terminated = 4;

	public static function out($v)
	{
		$result = "";

		switch ($v)
		{
			case self::Created:
				$result = "Created";
				break;
			case self::Running:
				$result = "Running";
				break;
			case self::Completed:
				$result = "Completed";
				break;
			case self::Suspended:
				$result = "Suspended";
				break;
			case self::Terminated:
				$result = "Terminated";
				break;
			default:
				throw new Exception("UnknownWorkflowStatus");
		}

		return $result;
	}

	public static function isFinished(int $status): bool
	{
		return $status === static::Completed || $status === static::Terminated;
	}
}

class CBPActivityExecutorOperationType
{
	const Execute = 0;
	const Cancel = 1;
	const HandleFault = 2;

	public static function out($v)
	{
		$result = "";

		switch ($v)
		{
			case self::Execute:
				$result = "Execute";
				break;
			case self::Cancel:
				$result = "Running";
				break;
			case self::HandleFault:
				$result = "HandleFault";
				break;
			default:
				throw new Exception("UnknownActivityExecutorOperationType");
		}

		return $result;
	}
}

class CBPDocumentEventType
{
	const None = 0;
	const Create = 1;
	const Edit = 2;
	const Delete = 4;
	const Automation = 8;
	const Manual = 16;
	const Script = 32;
	const Debug = 64;

	public static function out($v)
	{
		$result = [];

		if ($v == self::None)
		{
			$result[] = "None";
		}

		if (($v & self::Create) != 0)
		{
			$result[] = "Create";
		}

		if (($v & self::Edit) != 0)
		{
			$result[] = "Edit";
		}

		if (($v & self::Delete) != 0)
		{
			$result[] = "Delete";
		}

		if (($v & self::Automation) != 0)
		{
			$result[] = "Automation";
		}

		if (($v & self::Manual) != 0)
		{
			$result[] = "Manual";
		}

		if (($v & self::Script) != 0)
		{
			$result[] = "Script";
		}

		if (($v & self::Debug) != 0)
		{
			$result[] = "Debug";
		}

		return implode(', ', $result);
	}
}

class CBPCanUserOperateOperation
{
	const ViewWorkflow = 0;
	const StartWorkflow = 1;
	const CreateWorkflow = 4;
	const CreateAutomation = 5;
	const WriteDocument = 2;
	const ReadDocument = 3;
	const DebugAutomation = 6;
}

class CBPSetPermissionsMode
{
	const Hold = 1;
	const Rewrite = 2;
	const Clear = 3;

	const ScopeWorkflow = 1;
	const ScopeDocument = 2;

	public static function outMode($v)
	{
		$result = "";
		switch ($v)
		{
			case self::Rewrite:
				$result = "Rewrite";
				break;
			case self::Clear:
				$result = "Clear";
				break;
			default:
				$result = "Hold";
		}
		return $result;
	}

	public static function outScope($v)
	{
		if ($v == self::ScopeDocument)
		{
			return "ScopeDocument";
		}
		return "ScopeWorkflow";
	}
}

class CBPTaskStatus
{
	public const Running = 0;
	public const CompleteYes = 1;
	public const CompleteNo = 2;
	public const CompleteOk = 3;
	public const Timeout = 4;
	public const CompleteCancel = 5;

	public static function isSuccess(int $status)
	{
		return $status === self::CompleteYes || $status === self::CompleteOk;
	}
}

class CBPTaskUserStatus
{
	public const Waiting = 0;
	public const Yes = 1;
	public const No = 2;
	public const Ok = 3;
	public const Cancel = 4;

	public static function resolveStatus($name)
	{
		switch (mb_strtolower((string)$name))
		{
			case '0':
			case 'waiting':
				return self::Waiting;
			case '1':
			case 'yes':
				return self::Yes;
			case '2':
			case 'no':
				return self::No;
			case '3':
			case 'ok':
				return self::Ok;
			case '4':
			case 'cancel':
				return self::Cancel;
		}
		return null;
	}

	public static function isPositive(int $status): bool
	{
		return $status === self::Yes || $status === self::Ok;
	}

	public static function isNegative(int $status): bool
	{
		return $status === self::No || $status === self::Cancel;
	}
}

class CBPTaskChangedStatus
{
	const Add = 1;
	const Update = 2;
	const Delegate = 3;
	const Delete = 4;
}

class CBPTaskDelegationType
{
	const Subordinate = 0; // default value
	const AllEmployees = 1;
	const None = 2;

	public static function getSelectList()
	{
		return [
			self::Subordinate => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_SUBORDINATE'),
			self::AllEmployees => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_ALL_EMPLOYEES'),
			self::None => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_NONE'),
		];
	}
}
