export class WorkflowStatus
{
	static Created = 0;
	static Running = 1;
	static Completed = 2;
	static Suspended = 3;
	static Terminated = 4;
}

export class DebuggerState
{
	static Run = 0;
	static NextStep = 1;
	static Stop = 2;
	static Pause = 3;
	static Undefined = -1;
}
