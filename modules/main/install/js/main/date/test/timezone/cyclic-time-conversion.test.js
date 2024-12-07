import { BrowserTime } from '../../src/timezone/browser-time';
import { getTimestampFromDate } from '../../src/timezone/internal/helpers';
import { ServerTime } from '../../src/timezone/server-time';
import { UserTime } from '../../src/timezone/user-time';
import { assertDatesEqualIgnoringMilliseconds, resetAllFixtures, setFixtures } from './test-helpers';

describe('cyclic time conversion', () => {
	beforeEach(() => {
		resetAllFixtures();
		setFixtures();
	});

	it('should have same value at start and finish when start with browser', function() {
		const browserNow = BrowserTime.getDate();

		const browserNowAfterConversionCycle = UserTime.toBrowserDate(ServerTime.toUserDate(BrowserTime.toServerDate(browserNow)));

		assertDatesEqualIgnoringMilliseconds(browserNowAfterConversionCycle, browserNow);
	});

	it('should have same value at start and finish when start with user', function() {
		const userNow = UserTime.getDate();

		const userNowAfterConversionCycle = ServerTime.toUserDate(BrowserTime.toServerDate(UserTime.toBrowserDate(userNow)));

		assertDatesEqualIgnoringMilliseconds(userNowAfterConversionCycle, userNow);
	});

	it('should have same value at start and finish when start with server', function() {
		const serverNow = ServerTime.getDate();

		const serverNowAfterConversionCycle = BrowserTime.toServerDate(UserTime.toBrowserDate(ServerTime.toUserDate(serverNow)));

		assertDatesEqualIgnoringMilliseconds(serverNowAfterConversionCycle, serverNow);
	});

	it('should have same value at start and finish when start with utc timestamp', function() {
		const nowTimestamp = getTimestampFromDate(new Date());

		const nowTimestampAfterConversionCycle =
			UserTime.toUTCTimestamp(
				BrowserTime.toUserDate(
					ServerTime.toBrowserDate(
						ServerTime.getDate(
							nowTimestamp
						)
					)
				)
			)
		;

		assert.strictEqual(nowTimestampAfterConversionCycle, nowTimestamp);
	});
});
