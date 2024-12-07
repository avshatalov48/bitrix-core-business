import { createDateFromTimestamp } from '../../../src/timezone/internal/helpers';
import { UserTime } from '../../../src/timezone/user-time';
import { assertDatesEqualIgnoringMilliseconds, getFixedValues, resetAllFixtures, setFixtures } from '../test-helpers';

describe('User Time', () => {
	beforeEach(() => {
		resetAllFixtures();
		setFixtures();
	});

	describe('getTimestamp', () => {
		it('should return timestamp that will create correct absolute time when passed to Date object', function() {
			const userTimestamp= UserTime.getTimestamp();

			assertDatesEqualIgnoringMilliseconds(
				// when we pass this timestamp to 'new Date', it should create an object with time as if it was in user TZ
				createDateFromTimestamp(userTimestamp),
				getFixedValues().userNow
			);
		});
	});

	describe('getDate', () => {
		it('should return current date in user perspective when no args passed', function() {
			assertDatesEqualIgnoringMilliseconds(
				UserTime.getDate(),
				getFixedValues().userNow
			);
		});

		it('should return date with given time in user perspective when args provided', function() {
			const timestamp = 1709105585; // Wed Feb 28 2024 07:33:05 GMT+0000

			assertDatesEqualIgnoringMilliseconds(
				UserTime.getDate(timestamp),
				new Date(2024, 1, 28, 12, 33, 5),
			);
		});
	});

	describe('time conversion', () => {
		describe('toUTCTimestamp', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					UserTime.toUTCTimestamp(getFixedValues().userNow),
					getFixedValues().utcNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					UserTime.toUTCTimestamp(getFixedValues().userNowTimestamp),
					getFixedValues().utcNowTimestamp
				);
			});
		});

		describe('toServer', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					UserTime.toServer(getFixedValues().userNow),
					getFixedValues().serverNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					UserTime.toServer(getFixedValues().userNowTimestamp),
					getFixedValues().serverNowTimestamp
				);
			});
		});

		describe('toServerDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					UserTime.toServerDate(getFixedValues().userNow),
					getFixedValues().serverNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					UserTime.toServerDate(getFixedValues().userNowTimestamp),
					getFixedValues().serverNow
				);
			});
		});

		describe('toBrowser', () => {
			it('should return correct timestamp when date object is passed', function() {
				assert.strictEqual(
					UserTime.toBrowser(getFixedValues().userNow),
					getFixedValues().browserNowTimestamp
				);
			});

			it('should return correct timestamp when timestamp is passed', function() {
				assert.strictEqual(
					UserTime.toBrowser(getFixedValues().userNowTimestamp),
					getFixedValues().browserNowTimestamp
				);
			});
		});

		describe('toBrowserDate', () => {
			it('should return correct date object when date object is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					UserTime.toBrowserDate(getFixedValues().userNow),
					getFixedValues().browserNow
				);
			});

			it('should return correct date object when timestamp is passed', function() {
				assertDatesEqualIgnoringMilliseconds(
					UserTime.toBrowserDate(getFixedValues().userNowTimestamp),
					getFixedValues().browserNow
				);
			});
		});
	});
});
