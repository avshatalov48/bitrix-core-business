import {WorkflowStatus} from "../workflow/types";

export class TrackingEntry
{
	static UNKNOWN_ACTIVITY_TYPE = 0;
	static EXECUTE_ACTIVITY_TYPE = 1;
	static CLOSE_ACTIVITY_TYPE = 2;
	static CANCEL_ACTIVITY_TYPE = 3;
	static FAULT_ACTIVITY_TYPE = 4;
	static CUSTOM_ACTIVITY_TYPE = 5;
	static REPORT_ACTIVITY_TYPE = 6;
	static ATTACHED_ENTITY_TYPE = 7;
	static TRIGGER_ACTIVITY_TYPE = 8;
	static ERROR_ACTIVITY_TYPE = 9;
	static DEBUG_ACTIVITY_TYPE = 10;
	static DEBUG_AUTOMATION_TYPE = 11;
	static DEBUG_DESIGNER_TYPE = 12;
	static DEBUG_LINK_TYPE = 13;

	id: number;
	workflowId: string;
	#type: number;
	name: string;
	title: string;
	note: string;
	// TODO - convert string to Date
	datetime: string;
	#workflowStatus: number;

	get type(): number
	{
		return this.#type;
	}

	get workflowStatus(): number
	{
		return this.#workflowStatus;
	}

	set type(entryType: number)
	{
		if (TrackingEntry.getAllActivityTypes().includes(entryType))
		{
			this.#type = entryType;
		}
	}

	set workflowStatus(entryWorkflowStatus: number)
	{
		if (TrackingEntry.getAllWorkflowStatuses().includes(entryWorkflowStatus))
		{
			this.#workflowStatus = entryWorkflowStatus;
		}
	}

	isTriggerEntry(): boolean
	{
		return this.type === TrackingEntry.TRIGGER_ACTIVITY_TYPE;
	}

	static getAllActivityTypes(): Array<number>
	{
		return [
			TrackingEntry.UNKNOWN_ACTIVITY_TYPE,
			TrackingEntry.EXECUTE_ACTIVITY_TYPE,
			TrackingEntry.CLOSE_ACTIVITY_TYPE,
			TrackingEntry.CANCEL_ACTIVITY_TYPE,
			TrackingEntry.FAULT_ACTIVITY_TYPE,
			TrackingEntry.CUSTOM_ACTIVITY_TYPE,
			TrackingEntry.REPORT_ACTIVITY_TYPE,
			TrackingEntry.ATTACHED_ENTITY_TYPE,
			TrackingEntry.TRIGGER_ACTIVITY_TYPE,
			TrackingEntry.ERROR_ACTIVITY_TYPE,
			TrackingEntry.DEBUG_ACTIVITY_TYPE,
			TrackingEntry.DEBUG_AUTOMATION_TYPE,
			TrackingEntry.DEBUG_DESIGNER_TYPE,
			TrackingEntry.DEBUG_LINK_TYPE,
		];
	}

	static isKnownActivityType(typeId: number): boolean
	{
		return TrackingEntry.getAllActivityTypes().includes(typeId);
	}

	static getAllWorkflowStatuses(): Array<number>
	{
		return [
			WorkflowStatus.CREATED,
			WorkflowStatus.RUNNING,
			WorkflowStatus.COMPLETED,
			WorkflowStatus.SUSPENDED,
			WorkflowStatus.TERMINATED,
		];
	}

	static isKnownWorkflowStatus(statusId: number): boolean
	{
		return TrackingEntry.getAllWorkflowStatuses().includes(statusId);
	}
}