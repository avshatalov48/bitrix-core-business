import { Type } from 'main.core';
import { TrackingEntry } from './tracking-entry';
import { RobotEntry } from './robot-entry';
import { TriggerEntry } from './trigger-entry';
import { TrackingEntryBuilder } from './tracking-entry-builder';
import { Document } from 'bizproc.automation';
import { TrackingStatus } from './types';
import { WorkflowStatus } from "../workflow/types";

export {
	RobotEntry,
	TriggerEntry,
	TrackingEntryBuilder,
	TrackingEntry,
	TrackingStatus,
}

export class Tracker
{
	#ajaxUrl: string;
	#document: ?Document;

	#triggerLogs: Object<string, TrackingEntry>;
	#robotLogs: Object<string, RobotEntry>;

	constructor(document: ?Document, ajaxUrl: string)
	{
		this.#ajaxUrl = ajaxUrl;
		this.#document = document;
	}

	init(log)
	{
		this.#triggerLogs = {};
		this.#robotLogs = {};

		this.addLogs(log);
	}

	reInit(log)
	{
		this.init(log);
	}

	addLogs(log: Object<string, Array<Object<string, any>>>)
	{
		if (!Type.isPlainObject(log))
		{
			log = {};
		}

		const logEntryBuilder = new TrackingEntryBuilder();

		for (const [statusId, entries] of Object.entries(log))
		{
			if (!Type.isArray(entries))
			{
				continue;
			}

			for (const rawEntry of entries)
			{
				const entry = logEntryBuilder.setLogEntry(rawEntry).build();

				if (entry.isTriggerEntry())
				{
					this.addTriggerEntry(entry);
				}
				else
				{
					this.addRobotEntry(entry);
					const robotEntry = this.#robotLogs[entry.name];

					if (!Type.isNil(this.#document))
					{
						const isRobotRunning = (robotEntry.status === TrackingStatus.RUNNING);
						const isWorkflowCompleted =
							(robotEntry.workflowStatus === WorkflowStatus.COMPLETED)
						;
						const isCurrentStatus = (this.#document.getCurrentStatusId() === statusId);

						const isRobotRunningAtAnotherStatus = isRobotRunning && !isCurrentStatus;
						const isRobotRunningAndCurrentWorkflowCompleted =
							isRobotRunning && isWorkflowCompleted && isCurrentStatus
						;

						if (isRobotRunningAtAnotherStatus || isRobotRunningAndCurrentWorkflowCompleted)
						{
							robotEntry.status = TrackingStatus.COMPLETED;
						}
					}
				}
			}
		}
	}

	addTriggerEntry(entry: TrackingEntry)
	{
		if (entry.isTriggerEntry())
		{
			this.#triggerLogs[entry.note] = new TriggerEntry(entry);
		}
	}

	addRobotEntry(entry: TrackingEntry)
	{
		if (entry.isTriggerEntry())
		{
			return;
		}

		if (!this.#robotLogs[entry.name])
		{
			this.#robotLogs[entry.name] = new RobotEntry([entry]);
		}
		else
		{
			this.#robotLogs[entry.name].addEntry(entry);
		}
	}

	getRobotLog(id: string): ?RobotEntry
	{
		return this.#robotLogs[id] || null;
	}

	getTriggerLog(id: string): ?TriggerEntry
	{
		return this.#triggerLogs[id] || null;
	}

	update(documentSigned: string)
	{
		return BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: this.#ajaxUrl,
			data: {
				ajax_action: 'get_log',
				document_signed: documentSigned
			},
			onsuccess: (response) => {
				if (response.DATA && response.DATA.LOG)
				{
					this.reInit(response.DATA.LOG);
				}
			}
		});
	}
}
