import { Type } from 'main.core';
import { TrackingEntry } from './tracking-entry';
import { WorkflowStatus } from "../workflow/types";

export class TrackingEntryBuilder
{
	#defaultSettings = {
		id: TrackingEntry.UNKNOWN_ACTIVITY_TYPE,
		workflowId: '',
		type: TrackingEntry.EXECUTE_ACTIVITY_TYPE,
		name: '',
		title: '',
		datetime: '',
		note: '',
		workflowStatus: WorkflowStatus.CREATED,
	};
	#entrySettings: Object<string, string | number>;

	constructor()
	{
		this.#entrySettings = this.#defaultSettings;
	}

	setLogEntry(logEntry: Object<string, any>): this
	{
		this.#entrySettings = Object.assign({}, this.#defaultSettings);
		logEntry = Object.assign({}, logEntry);

		if (Type.isStringFilled(logEntry['ID']))
		{
			logEntry['ID'] = parseInt(logEntry['ID']);
		}
		if (Type.isStringFilled(logEntry['TYPE']))
		{
			logEntry['TYPE'] = parseInt(logEntry['TYPE']);
		}

		if (Type.isNumber(logEntry['ID']))
		{
			this.#entrySettings.id = logEntry['ID'];
		}
		if (Type.isStringFilled(logEntry['WORKFLOW_ID']))
		{
			this.#entrySettings.workflowId = logEntry['WORKFLOW_ID'];
		}
		if (Type.isNumber(logEntry['TYPE']) && TrackingEntry.isKnownActivityType(logEntry['TYPE']))
		{
			this.#entrySettings.type = logEntry['TYPE'];
		}
		if (Type.isStringFilled(logEntry['MODIFIED']))
		{
			this.#entrySettings.datetime = logEntry['MODIFIED'];
		}
		if (Type.isNumber(logEntry['WORKFLOW_STATUS']) && TrackingEntry.isKnownWorkflowStatus(logEntry['WORKFLOW_STATUS']))
		{
			this.#entrySettings.workflowStatus = logEntry['WORKFLOW_STATUS'];
		}

		this.#entrySettings.name = String(logEntry['ACTION_NAME']);
		this.#entrySettings.title = String(logEntry['ACTION_TITLE']);
		this.#entrySettings.note = String(logEntry['ACTION_NOTE']);

		return this;
	}

	setStatus(status: string): this
	{
		this.#entrySettings.status = status;

		return this;
	}

	build(): TrackingEntry
	{
		const entry = new TrackingEntry();

		entry.id = this.#entrySettings.id;
		entry.workflowId = this.#entrySettings.workflowId;
		entry.type = this.#entrySettings.type;
		entry.name = this.#entrySettings.name;
		entry.title = this.#entrySettings.title;
		entry.note = this.#entrySettings.note;
		entry.datetime = this.#entrySettings.datetime;
		entry.workflowStatus = this.#entrySettings.workflowStatus;

		return entry;
	}
}