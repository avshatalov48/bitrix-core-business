import { DateTimeFormat } from '../../src/date-time-format';

global.loadMessages({
	langFile: `${__dirname}/../../../../../../lang/en/date_format.php`, // main/lang/en/date_format.php
});

const BX = {
	Main: {
		Date: DateTimeFormat,
	}
};

const now = new Date(2022, 11, 3, 15, 0, 55);

const tomorrow = new Date(2022, 11, 4, 15, 0, 55);
const twoSecondsAgo = new Date(2022, 11, 3, 15, 0, 53);
const twoHoursAgo = new Date(2022, 11, 3, 13, 0, 55);
const twoDaysAndSixHoursAgo = new Date(2022, 11, 1, 9, 0, 55);
const twoMonthsAgo = new Date(2022, 9, 3, 15, 0, 55);
const yearAgo = new Date(2021, 11, 3, 15, 0, 55);
const twoYearsAgo = new Date(2020, 11, 3, 15, 0, 55);

describe('DateTimeFormat', () => {
	describe('format', () => {
		it('should format the date ', () => {
			assert.equal(DateTimeFormat.format('d-m-Y H:i:s', twoDaysAndSixHoursAgo, now), '01-12-2022 09:00:55');
			assert.equal(DateTimeFormat.format('^d-m-Y H:i:s', twoDaysAndSixHoursAgo, now), '01-12-2022 09:00:55');
			assert.equal(DateTimeFormat.format('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', twoDaysAndSixHoursAgo, now), '09:12:55 m is month');
			assert.equal(DateTimeFormat.format('Hago | dago | sago | iago | mago | Yago', twoYearsAgo, now), '17520 hours ago | 730 days ago | 63072000 seconds ago | 1051200 minutes ago | 23 months ago | 2 years ago');
			assert.equal(DateTimeFormat.format('sago | iago', 1_320_271_200, now), '349801255 seconds ago | 5830020 minutes ago');
		});

		it('should handle english formats', () => {
			const tests = {
				'm/d/Y': '12/03/2022',
				'm/d/Y h:i:s a': '12/03/2022 03:00:55 pm',
				'n/j/Y': '12/3/2022',
				'M j, Y': 'Dec 3, 2022',
				'F j, Y': 'December 3, 2022',
				'l, F j, Y': 'Saturday, December 3, 2022',
				'M j': 'Dec 3',
				'l, F j': 'Saturday, December 3',
				'D, F j': 'Sat, December 3',
				'D, M j': 'Sat, Dec 3',
				'g:i a': '3:00 pm',
				'g:i:s a': '3:00:55 pm',
			};

			Object.entries(tests).forEach(([format, answer]) => {
				assert.equal(DateTimeFormat.format(format, now), answer);
			});
		});

		it('should handle russian formats ', () => {
			const tests = {
				'd.m.Y': '03.12.2022',
				'd.m.Y H:i:s': '03.12.2022 15:00:55',
				'j M Y': '3 Dec 2022',
				'j F Y': '3 December 2022',
				'l, j F Y': 'Saturday, 3 December 2022',
				'j F': '3 December',
				'j M': '3 Dec',
				'l, j F': 'Saturday, 3 December',
				'D, j F': 'Sat, 3 December',
				'D, j M': 'Sat, 3 Dec',
				'H:i': '15:00',
				'H:i:s': '15:00:55',
			};

			Object.entries(tests).forEach(([format, answer]) => {
				assert.equal(DateTimeFormat.format(format, now), answer);
			});
		});

		it('should choose the format with appropriate boundaries if multiple formats are provided', () => {
			const formats = [
				['tommorow', 'H:i:s'],
				['s', '\\s\\e\\c'],
				['H', '\\h\\o\\u\\r\\s'],
				['d', '\\d\\a\\y\\s'],
				['m', '\\m\\o\\n\\t\\h\\s'],
				['m13', '13 \\m\\o\\n\\t\\h\\s'],
				['-', '\\v\\e\\r\\y \\l\\o\\n\\g \\a\\g\\o'],
			];

			assert.equal(DateTimeFormat.format(formats, tomorrow, now), '15:00:55');
			assert.equal(DateTimeFormat.format(formats, twoSecondsAgo, now), 'sec');
			assert.equal(DateTimeFormat.format(formats, twoHoursAgo, now), 'hours');
			assert.equal(DateTimeFormat.format(formats, twoDaysAndSixHoursAgo, now), 'days');
			assert.equal(DateTimeFormat.format(formats, twoMonthsAgo, now), 'months');
			assert.equal(DateTimeFormat.format(formats, yearAgo, now), '13 months');
			assert.equal(DateTimeFormat.format(formats, twoYearsAgo, now), 'very long ago');

			const format = [
				['-', 'd.m.Y H:i:s'],
				['s300', 'sago'],
				['H', 'Hago'],
				['d', 'dago'],
				['m', 'mago'],
			];

			assert.equal(DateTimeFormat.format(format, new Date(2007, 2, 2, 9, 58, 0), new Date(2007, 2, 2, 10, 0, 0)), '120 seconds ago');
			assert.equal(DateTimeFormat.format(format, new Date(2007, 2, 2, 0, 0, 0), new Date(2007, 2, 2, 10, 0, 0)), '10 hours ago');
			assert.equal(DateTimeFormat.format(format, new Date(2007, 2, 1, 0, 0, 0), new Date(2007, 2, 2, 10, 0, 0)), '1 day ago');
			assert.equal(DateTimeFormat.format(format, new Date(2007, 2, 3, 0, 0, 0), new Date(2007, 2, 2, 10, 0, 0)), '03.03.2007 00:00:00');
		});

		it('should use overloaded loc message storage', () => {
			//clone global object
			const CustomDate = Object.create(BX.Main.Date);
			CustomDate._getMessage = (phrase) => phrase;

			//cloned object should get loc phrases via the overloaded method
			assert.equal(CustomDate.format('D', now, now), 'DOW_6');
			//global object should not be affected by these manipulations
			assert.notEqual(BX.Main.Date.format('D', now, now), 'DOW_6');
		});

		it('should handle kanji', () => {
			const DAY_SHORT_MONTH_FORMAT = 'Mj\\日';
			const LONG_DATE_FORMAT = 'Y\\年Fj\\日';

			assert.equal(DateTimeFormat.format(DAY_SHORT_MONTH_FORMAT, now), 'Dec3日');
			assert.equal(DateTimeFormat.format(LONG_DATE_FORMAT, now), '2022年December3日');
		});

		it('should handle interesting cases', () => {
			const vietnamese = 'l, j F \\n\\ă\\m Y';
			const brazilian = 'l, j \\d\\e F \\d\\e Y';

			assert.equal(DateTimeFormat.format(vietnamese, now), 'Saturday, 3 December năm 2022');
			assert.equal(DateTimeFormat.format(brazilian, now), 'Saturday, 3 de December de 2022');
		});

		it('should handle backslashes', () => {
			const tests = {
				'Y\\m\\d': '2022md',
				'Y\\\\m\\\\d': '2022\\12\\03',
				'Y\\\\\\m\\\\\\d': '2022\\m\\d',
				'Y\\\\\\\\m\\\\\\\\d': '2022\\\\12\\\\03',
			};

			Object.entries(tests).forEach(([format, answer]) => {
				assert.equal(DateTimeFormat.format(format, now), answer);
			});
		});
	});
});
