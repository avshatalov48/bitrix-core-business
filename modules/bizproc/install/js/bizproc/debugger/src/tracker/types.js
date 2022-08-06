export type TrackingRow = {
	Id: number,
	WorkflowId: string,
	Type: number,
	Name: string,
	Title: string,
	Note: string,
	DateTime: string,
};

export class TrackingType
{
	static Unknown = 0;
	static ExecuteActivity = 1;
	static CloseActivity = 2;
	static CancelActivity = 3;
	static FaultActivity = 4;
	static Custom = 5;
	static Report = 6;
	static AttachedEntity = 7;
	static Trigger = 8;
	static Error = 9;
	static Debug = 10;
	static DebugAutomation = 11;
	static DebugDesigner = 12;
	static DebugLink = 13;
}