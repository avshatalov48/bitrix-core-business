import { createDateFromTimestamp } from '../../../src/timezone/internal/helpers';
import { ServerTime } from '../../../src/timezone/server-time';
import { assertDatesEqualIgnoringMilliseconds, getFixedValues, resetAllFixtures, setFixtures } from '../test-helpers';

describe('Server Time', () => {
	beforeEach(() => {
		resetAllFixtures();
		setFixtures();
	});

	describe('getTimestamp', () => {
		it('should return timestamp that will create correct absolute time when passed to Date object', function() {
			const serverTimestamp= ServerTime.getTimestamp();

			assertDatesEqualIgnoringMilliseconds(
				// when we pass this timestamp to 'new Date', it should create an object with time as if it was in server TZ
				createDateFromTimestamp(serverTimestamp),
				getFixedValues().serverNow
			);
		});
	});

	describe('getDate', () => {
		it('should return current date in server perspective when no args passed', function() {
			assertDatesEqualIgnoringMilliseconds(
				ServerTime.getDate(),
				getFixedValues().serverNow
			);
		});

		it('should return date with given time in server perspective when args provided', function() {
			const timestamp = 1709105585; // Wed Feb 28 2024 07:33:05 GMT+0000

			assertDatesEqualIgnoringMilliseconds(
				ServerTime.getDate(timestamp),
				new Date(2024, 1, 28, 10, 33, 5),
			);
		});
	});

	describe('time conversion', () => {
		describe('toUser', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					ServerTime.toUser(getFixedValues().serverNow),
					getFixedValues().userNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					ServerTime.toUser(getFixedValues().serverNowTimestamp),
					getFixedValues().userNowTimestamp
				);
			});
		});

		describe('toUserDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					ServerTime.toUserDate(getFixedValues().serverNow),
					getFixedValues().userNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					ServerTime.toUserDate(getFixedValues().serverNowTimestamp),
					getFixedValues().userNow
				);
			});
		});

		describe('toBrowser', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					ServerTime.toBrowser(getFixedValues().serverNow),
					getFixedValues().browserNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					ServerTime.toBrowser(getFixedValues().serverNowTimestamp),
					getFixedValues().browserNowTimestamp
				);
			});
		});

		describe('toBrowserDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					ServerTime.toBrowserDate(getFixedValues().serverNow),
					getFixedValues().browserNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					ServerTime.toBrowserDate(getFixedValues().serverNowTimestamp),
					getFixedValues().browserNow
				);
			});
		});
	});
});
