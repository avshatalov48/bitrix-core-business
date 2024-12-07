import { sendAnalyticsData } from './functions';
import { type AnalyticsOptions as ao } from './types';

export function sendData(data: AnalyticsOptions): void
{
	/** @see BX.ajax.runAction */
	/** @see processAnalyticsDataToGetParameters() */
	sendAnalyticsData(data);
}

export type AnalyticsOptions = ao;
