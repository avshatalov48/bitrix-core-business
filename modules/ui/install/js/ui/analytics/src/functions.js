import { ajax as Ajax, Extension, Type } from 'main.core';
import type { AnalyticsOptions } from './types';

function isValidAnalyticsData(analytics: AnalyticsOptions): boolean
{
	if (!Type.isPlainObject(analytics))
	{
		console.error('BX.UI.Analytics: {analytics} must be an object.');

		return false;
	}

	const requiredFields = ['event', 'tool', 'category'];
	for (const field of requiredFields)
	{
		if (!Type.isStringFilled(analytics[field]))
		{
			console.error(`BX.UI.Analytics: The "${field}" property in the "analytics" object must be a non-empty string.`);

			return false;
		}
	}

	const additionalFields = ['p1', 'p2', 'p3', 'p4', 'p5'];
	for (const field of additionalFields)
	{
		const value = analytics[field];
		if (!Type.isStringFilled(value))
		{
			continue;
		}

		if (value.split('_').length > 2)
		{
			console.error(`BX.UI.Analytics: The "${field}" property (${value}) in the "analytics" object must be a string containing a single underscore.`);

			return false;
		}
	}

	return true;
}

function buildUrlByData(data: AnalyticsOptions): string
{
	const url = new URL('/_analytics/', window.location.origin);

	const queryParams = [];
	for (const [key, value] of Object.entries(data))
	{
		queryParams.push(`st[${key}]=${encodeURIComponent(value)}`);
	}
	url.search = queryParams.join('&');

	return url.toString();
}

export function sendAnalyticsData(analytics: AnalyticsOptions): void
{
	if (!isValidAnalyticsData(analytics))
	{
		return;
	}

	const settings = Extension.getSettings('ui.analytics');
	const collectData = settings.get('collectData', false);
	if (!collectData)
	{
		return;
	}

	void Ajax({
		method: 'GET',
		url: buildUrlByData(analytics),
	});
}
