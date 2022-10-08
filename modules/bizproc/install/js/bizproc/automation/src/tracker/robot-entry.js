import {Type} from 'main.core';
import { TrackingEntry } from 'bizproc.automation';
import { TrackingStatus } from './types';
import {WorkflowStatus} from "../workflow/types";

export class RobotEntry
{
	id: string = '';
	status: string = TrackingStatus.WAITING;
	// TODO - change string to Date when Date appear in TrackingEntry
	modified: ?string = undefined;
	notes: Array<string> = [];
	errors: Array<string> = [];
	#entryId: number = -1;
	workflowStatus: number = WorkflowStatus.CREATED;

	constructor(entries: ?Array<TrackingEntry>)
	{
		if (Type.isArray(entries))
		{
			for (const entry of entries)
			{
				this.addEntry(entry);
			}
		}
	}

	addEntry(entry: TrackingEntry)
	{
		this.id = entry.name;

		if (this.#entryId < entry.id)
		{
			this.#entryId = entry.id;
			this.modified = entry.datetime;
			this.workflowStatus = entry.workflowStatus;

			if (entry.type === TrackingEntry.CLOSE_ACTIVITY_TYPE)
			{
				this.status = TrackingStatus.COMPLETED;
			}
			else
			{
				this.status = TrackingStatus.RUNNING;
			}
		}

		if (entry.type === TrackingEntry.ERROR_ACTIVITY_TYPE)
		{
			this.errors.push(entry.note);
		}
		else if (entry.type === TrackingEntry.CUSTOM_ACTIVITY_TYPE)
		{
			this.notes.push(entry.note);
		}
	}
}