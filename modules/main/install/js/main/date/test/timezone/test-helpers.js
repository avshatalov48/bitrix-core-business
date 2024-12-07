import { Text } from 'main.core';
import { getNowTimestamp, resetNow, resetOffset, setNow, setOffset } from '../../src/timezone/internal/di';
import { createDateFromTimestamp, getTimestampFromDate } from '../../src/timezone/internal/helpers';

export function assertDatesEqualIgnoringMilliseconds(actual: Date, expected: Date)
{
	assert.strictEqual(actual.getFullYear(), expected.getFullYear());
	assert.strictEqual(actual.getMonth(), expected.getMonth());
	assert.strictEqual(actual.getDate(), expected.getDate());
	assert.strictEqual(actual.getHours(), expected.getHours());
	assert.strictEqual(actual.getMinutes(), expected.getMinutes());
	assert.strictEqual(actual.getSeconds(), expected.getSeconds());
}

export function resetAllFixtures()
{
	resetNow();
	resetOffset();
}

export function setFixtures()
{
	setOffset({
		SERVER_TO_UTC: 10800, // server in MSK (GMT+3)
		USER_TO_SERVER: 7200, // user in Yekaterinburg (GMT+5)
		get BROWSER_TO_UTC(): number
		{
			const offset = Text.toInteger((new Date()).getTimezoneOffset() * 60);

			return -offset;
		},
	});

	setNow(1670068855); // 03-12-2022 12:00:55 UTC
}

export function getFixedValues()
{
	const userNow = new Date(2022, 11, 3, 17, 0, 55);
	const serverNow = new Date(2022, 11, 3, 15, 0, 55);
	const browserNow = createDateFromTimestamp(getNowTimestamp());

	const utcNowTimestamp = getNowTimestamp();

	return {
		userNow,
		userNowTimestamp: getTimestampFromDate(userNow),
		serverNow,
		serverNowTimestamp: getTimestampFromDate(serverNow),
		browserNow,
		browserNowTimestamp: getTimestampFromDate(browserNow),
		utcNowTimestamp,
	};
}
