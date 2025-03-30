/* eslint-disable unicorn/no-abusive-eslint-disable */
/* eslint-disable */
import { DurationFormat } from 'main.date';

global.loadMessages({
	langFile: `${__dirname}/../../../../../../lang/en/date_format.php`, // main/lang/en/date_format.php
});
global.window.BX.message['FD_UNIT_ORDER'] = 'Y m d H i s';
global.window.BX.message['FD_SEPARATOR'] = ',&#32;';
global.window.BX.message['FD_SEPARATOR_SHORT'] = '&#32;';

const s = 1000;
const i = 60000;
const H = 3_600_000;
const d = 86_400_000;
const m = 2_678_400_000;
const Y = 31_536_000_000;

describe('DurationFormat', () => {
	describe('format', () => {
		it('should format duration', () => {
			const duration = 2 * H + 24 * i + 30 * s;

			const tests = [
				{
					options: null,
					expected: '2 hours, 24 minutes, 30 seconds',
				},
				{
					options: { style: 'short' },
					expected: '2 h 24 m 30 s',
				},
				{
					options: { format: 'd H i' },
					expected: '2 hours, 24 minutes',
				},
				{
					options: { format: 'i s' },
					expected: '144 minutes, 30 seconds',
				},
			];

			tests.forEach(({ options, expected }) => {
				assert.equal(new DurationFormat(duration).format(options), expected);
			});
		});

		it('should format all units (long)', () => {
			const duration = 1234567891000;

			const tests = [
				{
					options: { style: 'long' },
					expected: '39 years, 4 months, 28 days, 23 hours, 31 minutes, 31 seconds',
				},
				{
					options: { format: 'Y', style: 'long' },
					expected: '39 years',
				},
				{
					options: { format: 'm', style: 'long' },
					expected: '460 months',
				},
				{
					options: { format: 'd', style: 'long' },
					expected: '14288 days',
				},
				{
					options: { format: 'H', style: 'long' },
					expected: '342935 hours',
				},
				{
					options: { format: 'i', style: 'long' },
					expected: '20576131 minutes',
				},
				{
					options: { format: 's', style: 'long' },
					expected: '1234567891 seconds',
				},
			];

			tests.forEach(({ options, expected }) => {
				assert.equal(new DurationFormat(duration).format(options), expected);
			});
		});

		it('should format all units (long)', () => {
			const duration = 1234567891000;

			const tests = [
				{
					options: { style: 'short' },
					expected: '39 y 4 mon 28 d 23 h 31 m 31 s',
				},
				{
					options: { format: 'Y', style: 'short' },
					expected: '39 y',
				},
				{
					options: { format: 'm', style: 'short' },
					expected: '460 mon',
				},
				{
					options: { format: 'd', style: 'short' },
					expected: '14288 d',
				},
				{
					options: { format: 'H', style: 'short' },
					expected: '342935 h',
				},
				{
					options: { format: 'i', style: 'short' },
					expected: '20576131 m',
				},
				{
					options: { format: 's', style: 'short' },
					expected: '1234567891 s',
				},
			];

			tests.forEach(({ options, expected }) => {
				assert.equal(new DurationFormat(duration).format(options), expected);
			});
		});
	});

	describe('formatClosest', () => {
		it('should format suitable duration', () => {
			const duration = H + 24 * i + 30 * s;

			const tests = [
				{
					options: null,
					expected: '1 hour',
				},
				{
					options: { format: 'i s' },
					expected: '84 minutes',
				},
			];

			tests.forEach(({ options, expected }) => {
				assert.equal(new DurationFormat(duration).formatClosest(options), expected);
			});
		});
	});

	describe('getUnitDurations', () => {
		it('haven\'t changed by any mistake', () => {
			const durations = DurationFormat.getUnitDurations();

			assert.equal(durations.Y, Y);
			assert.equal(durations.m, m);
			assert.equal(durations.d, d);
			assert.equal(durations.H, H);
			assert.equal(durations.i, i);
			assert.equal(durations.s, s);
		});
	});
});
