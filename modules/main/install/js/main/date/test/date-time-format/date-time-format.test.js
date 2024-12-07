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
		});

		it('should choose the format with appropriate boundaries if multiple formats are provided', () => {
			const formats = [
				["tommorow", "H:i:s"],
				["s" , "\\s\\e\\c"],
				["H", "\\h\\o\\u\\r\\s"],
				["d", "\\d\\a\\y\\s"],
				["m", "\\m\\o\\n\\t\\h\\s"],
				["m13", "13 \\m\\o\\n\\t\\h\\s"],
				["-", "\\v\\e\\r\\y \\l\\o\\n\\g \\a\\g\\o"]
			];

			assert.equal(DateTimeFormat.format(formats, tomorrow, now), '15:00:55');
			assert.equal(DateTimeFormat.format(formats, twoSecondsAgo, now), 'sec');
			assert.equal(DateTimeFormat.format(formats, twoHoursAgo, now), 'hours');
			assert.equal(DateTimeFormat.format(formats, twoDaysAndSixHoursAgo, now), 'days');
			assert.equal(DateTimeFormat.format(formats, twoMonthsAgo, now), 'months');
			assert.equal(DateTimeFormat.format(formats, yearAgo, now), '13 months');
			assert.equal(DateTimeFormat.format(formats, twoYearsAgo, now), 'very long ago');
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
	});
});
