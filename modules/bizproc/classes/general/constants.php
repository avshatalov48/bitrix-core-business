<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CBPActivityExecutionStatus
{
	const Initialized = 0;
	const Executing = 1;
	const Canceling = 2;
	const Closed = 3;
	const Faulting = 4;

	public static function Out($v)
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

	public static function Out($v)
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

	public static function Out($v)
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
}

class CBPActivityExecutorOperationType
{
	const Execute = 0;
	const Cancel = 1;
	const HandleFault = 2;

	public static function Out($v)
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
	const None = 0;		//    0
	const Create = 1;	//    1
	const Edit = 2;		//   10
	const Delete = 4;	//  100
	const Automation = 8;	// 1000
	const Manual = 16;	// 10000

	public static function Out($v)
	{
		$result = "";

		if ($v == self::None)
			$result .= "None";

		if (($v & self::Create) != 0)
		{
			if (strlen($result) > 0)
				$result .= ", ";
			$result .= "Create";
		}

		if (($v & self::Edit) != 0)
		{
			if (strlen($result) > 0)
				$result .= ", ";
			$result .= "Edit";
		}

		if (($v & self::Delete) != 0)
		{
			if (strlen($result) > 0)
				$result .= ", ";
			$result .= "Delete";
		}

		if (($v & self::Automation) != 0)
		{
			if (strlen($result) > 0)
				$result .= ", ";
			$result .= "Automation";
		}

		if (($v & self::Manual) != 0)
		{
			if (strlen($result) > 0)
				$result .= ", ";
			$result .= "Manual";
		}

		return $result;
	}
}

class CBPCanUserOperateOperation
{
	const ViewWorkflow = 0;
	const StartWorkflow = 1;
	const CreateWorkflow = 4;
	const WriteDocument = 2;
	const ReadDocument = 3;
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
			return "ScopeDocument";
		return "ScopeWorkflow";
	}
}

class CBPTaskStatus
{
	const Running = 0;
	const CompleteYes = 1;
	const CompleteNo = 2;
	const CompleteOk = 3;
	const Timeout = 4;
	const CompleteCancel = 5;
}

class CBPTaskUserStatus
{
	const Waiting = 0;
	const Yes = 1;
	const No = 2;
	const Ok = 3;
	const Cancel = 4;

	public static function resolveStatus($name)
	{
		switch (strtolower((string)$name))
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
		return array(
			self::Subordinate => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_SUBORDINATE'),
			self::AllEmployees => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_ALL_EMPLOYEES'),
			self::None => Loc::getMessage('BPCG_CONSTANTS_DELEGATION_TYPE_NONE')
		);
	}
}