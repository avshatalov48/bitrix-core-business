import type { OffsetInterface } from '../offset';
import { Offset } from '../offset';
import { getTimestampFromDate } from './helpers';

let offset: OffsetInterface = Offset;
let now: ?number = null;

export function getOffset(): OffsetInterface
{
	return offset;
}

export function setOffset(newOffset: OffsetInterface): void
{
	offset = newOffset;
}

export function resetOffset(): void
{
	offset = Offset;
}

export function getNowTimestamp(): number
{
	return now ?? getTimestampFromDate(new Date());
}

export function setNow(timestamp: number): void
{
	now = timestamp;
}

export function resetNow(): void
{
	now = null;
}
