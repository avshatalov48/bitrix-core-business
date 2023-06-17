import { DateTimeFormat } from '../../src/date-time-format';

const BX = {
	Main: {
		Date: DateTimeFormat,
	}
};

/*
The test uses formats that don't rely on Loc.getMessage, AM_PM and any global/external values.
Therefore, only a limited number of formats and combinations is tested.
 */

const now = 1670068855;

const tomorrow = now + 3600 * 24;
const twoSecondsAgo = now - 2;
const twoHoursAgo = now - 3600 * 2;
const twoDaysAgo = now - 3600 * 24 * 2 - 3600 * 6;
const twoMonthsAgo = now - 3600 * 24 * 30 * 2;
const yearAgo = now - 3600 * 24 * 365;
const twoYearsAgo = now - 3600 * 24 * 365 * 2;

describe('DateTimeFormat', () => {
	describe('format', () => {
		it('should format the date ', () => {
			assert.equal(DateTimeFormat.format('d-m-Y H:i:s', twoDaysAgo, now), '01-12-2022 09:00:55');
			assert.equal(DateTimeFormat.format('^d-m-Y H:i:s', twoDaysAgo, now), '01-12-2022 09:00:55');
			assert.equal(DateTimeFormat.format('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', twoDaysAgo, now), '09:12:55 m is month');
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
			assert.equal(DateTimeFormat.format(formats, twoDaysAgo, now), 'days');
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
