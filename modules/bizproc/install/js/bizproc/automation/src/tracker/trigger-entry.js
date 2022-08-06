import { TrackingEntry } from 'bizproc.automation';
import { TrackingStatus } from './types';

export class TriggerEntry
{
	id: string = '';
	status: number = TrackingStatus.COMPLETED;
	// TODO - change string to Date when Date appear in TrackingEntry
	modified: ?string = undefined;

	constructor(entry: TrackingEntry)
	{
		if (entry.isTriggerEntry())
		{
			this.id = entry.note;
			this.modified = entry.datetime;
		}
	}
}