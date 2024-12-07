import { BrowserTime } from '../../../src/timezone/browser-time';
import { createDateFromTimestamp, getTimestampFromDate } from '../../../src/timezone/internal/helpers';
import { assertDatesEqualIgnoringMilliseconds, getFixedValues, resetAllFixtures, setFixtures } from '../test-helpers';

describe('Browser Time', () => {
	beforeEach(resetAllFixtures);

	describe('getTimestamp', () => {
		it('should return current utc timestamp', function() {
			assert.strictEqual(BrowserTime.getTimestamp(), getTimestampFromDate(new Date()));
		});
	});

	describe('getDate', () => {
		it('should return current date when no args passed', function() {
			const now = new Date();
			const nowFromBrowserTime = BrowserTime.getDate();

			assertDatesEqualIgnoringMilliseconds(nowFromBrowserTime, now);
		});

		it('should return date with given time when args provided', function() {
			const timestamp = 1670068855;

			const now = createDateFromTimestamp(timestamp);
			const nowFromBrowserTime = BrowserTime.getDate(timestamp);

			assertDatesEqualIgnoringMilliseconds(nowFromBrowserTime, now);
		});
	});

	describe('time conversion', () => {
		beforeEach(setFixtures);

		describe('toUser', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					BrowserTime.toUser(getFixedValues().browserNow),
					getFixedValues().userNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					BrowserTime.toUser(getFixedValues().browserNowTimestamp),
					getFixedValues().userNowTimestamp,
				);
			});
		});

		describe('toUserDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					BrowserTime.toUserDate(getFixedValues().browserNow),
					getFixedValues().userNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					BrowserTime.toUserDate(getFixedValues().browserNowTimestamp),
					getFixedValues().userNow,
				);
			});
		});

		describe('toServer', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					BrowserTime.toServer(getFixedValues().browserNow),
					getFixedValues().serverNowTimestamp,
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					BrowserTime.toServer(getFixedValues().browserNowTimestamp),
					getFixedValues().serverNowTimestamp
				);
			});
		});

		describe('toServerDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					BrowserTime.toServerDate(getFixedValues().browserNow),
					getFixedValues().serverNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					BrowserTime.toServerDate(getFixedValues().browserNowTimestamp),
					getFixedValues().serverNow
				);
			});
		});
	});
});
