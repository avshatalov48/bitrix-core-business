import { sendAnalyticsData } from './functions';
import { type AnalyticsOptions } from './types';

export type { AnalyticsOptions };

export function sendData(data: AnalyticsOptions): void
{
	/** @see BX.ajax.runAction */
	/** @see processAnalyticsDataToGetParameters() */
	sendAnalyticsData(data);
}
