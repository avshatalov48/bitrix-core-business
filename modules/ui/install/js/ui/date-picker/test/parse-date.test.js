import { createDate } from '../src/helpers/create-date';
import { parseDate } from '../src/helpers/parse-date';
import { getDate } from '../src/helpers/get-date';

describe('Parse date', () => {
	it('Should parse regular formats', () => {
		const { day: currentDay, month: currentMonth, year: currentYear } = getDate(createDate(new Date()));
		const tests = [
			['03/20/2020', 'MM/DD/YYYY', [20, 2, 2020, 0, 0, 0]],
			['09/13/1984 13:34:56', 'MM/DD/YYYY HH:MI:SS', [13, 8, 1984, 13, 34, 56]],
			['09/13/1984 13:34', 'MM/DD/YYYY HH:MI:SS', [13, 8, 1984, 13, 34, 0]],
			['05/01/2025 5:10:05 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 5, 10, 5]],
			['05/01/2025 05:10:05 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 5, 10, 5]],
			['05/01/2025 5:10:15 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 17, 10, 15]],
			['05/01/2025 05:10:35 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 17, 10, 35]],
			['21.03.2024 18:31:10', 'DD.MM.YYYY HH:MI:SS', [21, 2, 2024, 18, 31, 10]],
			['07.09.2024 16:33', 'DD.MM.YYYY HH:MI:SS', [7, 8, 2024, 16, 33, 0]],
			['2020/09/13 15:59:34', 'YYYY/MM/DD HH:MI:SS', [13, 8, 2020, 15, 59, 34]],
			['05/17/1984', 'MM/DD/YYYY', [17, 4, 1984, 0, 0, 0]],
			['07.07.2024', 'DD.MM.YYYY', [7, 6, 2024, 0, 0, 0]],
			['1983/01/01', 'YYYY/MM/DD', [1, 0, 1983, 0, 0, 0]],
			['01/02/1989', 'DD/MM/YYYY', [1, 1, 1989, 0, 0, 0]],
			['2019-12-17', 'YYYY-MM-DD', [17, 11, 2019, 0, 0, 0]],
			['1990/31/08', 'YYYY/DD/MM', [31, 7, 1990, 0, 0, 0]],
			['1990/08/31', 'YYYY/MM/DD', [31, 7, 1990, 0, 0, 0]],
			['31/08/1990', 'DD/MM/YYYY', [31, 7, 1990, 0, 0, 0]],
			['08/31/1990', 'MM/DD/YYYY', [31, 7, 1990, 0, 0, 0]],
			['08/1990/31', 'MM/YYYY/DD', [31, 7, 1990, 0, 0, 0]],
			['28/02/2035', 'DD/MM/YYYY', [28, 1, 2035, 0, 0, 0]],
			['05-06-2023', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
			['31/05/1988 13:10:59', 'DD/MM/YYYY HH:MI:SS', [31, 4, 1988, 13, 10, 59]],
			['2014-01-01 10:17:13', 'YYYY-MM-DD HH:MI:SS', [1, 0, 2014, 10, 17, 13]],
			['30/06/1967 2:23:00 am', 'DD/MM/YYYY H:MI:SS T', [30, 5, 1967, 2, 23, 0]],
			['30/06/1967 02:23:00 am', 'DD/MM/YYYY H:MI:SS T', [30, 5, 1967, 2, 23, 0]],
			['30/06/1967 2:23:00 pm', 'DD/MM/YYYY H:MI:SS T', [30, 5, 1967, 14, 23, 0]],
			['30/06/1967 02:23:00 pm', 'DD/MM/YYYY H:MI:SS T', [30, 5, 1967, 14, 23, 0]],
			['01/07/1867 12:59:00 am', 'DD/MM/YYYY H:MI:SS T', [1, 6, 1867, 0, 59, 0]],
			['01/07/1867 12:00:00 am', 'DD/MM/YYYY H:MI:SS T', [1, 6, 1867, 0, 0, 0]],
			['01/07/1867 12:00:00 pm', 'DD/MM/YYYY H:MI:SS T', [1, 6, 1867, 12, 0, 0]],
			['02-08-2000 1:00:57 am', 'DD-MM-YYYY H:MI:SS T', [2, 7, 2000, 1, 0, 57]],
			['02-08-2000 1:00:57 pm', 'DD-MM-YYYY H:MI:SS T', [2, 7, 2000, 13, 0, 57]],
			['05:15:00', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 5, 15, 0]],
			['15:00:59', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 15, 0, 59]],
			['23:12:01', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 23, 12, 1]],
			['00:00:00', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 0, 0, 0]],
			['03:01:12', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 3, 1, 12]],
			['09:20:05', 'HH:MI:SS', [currentDay, currentMonth, currentYear, 9, 20, 5]],
			['10:59:00 am', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 10, 59, 0]],
			['10:59:00 pm', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 22, 59, 0]],
			['7:13:44 am', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 7, 13, 44]],
			['12:15:00 am', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 0, 15, 0]],
			['12:00:00 am', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 0, 0, 0]],
			['12:00:00 pm', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 12, 0, 0]],
			['1:00:00 am', 'H:MI:SS T', [currentDay, currentMonth, currentYear, 1, 0, 0]],
		];

		verifyTests(tests);
	});

	it('Should parse dates without seconds', () => {
		const tests = [
			['05/01/2025 5:10 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 17, 10, 0]],
			['05/01/2025 05:10 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 17, 10, 0]],
			['05/01/2025 5:10 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 5, 10, 0]],
			['05/01/2025 05:10 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 5, 10, 0]],
			['05/01/2025 05:10 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 5, 10, 0]],
			['05/01/2025 12:10 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 0, 10, 0]],
			['05/01/2025 12:10 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 12, 10, 0]],
			['05/01/2025 12:00 pm', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 12, 0, 0]],
			['05/01/2025 12:00 am', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 0, 0, 0]],
		];

		verifyTests(tests);
	});

	it('Should parse date format with time', () => {
		const tests = [
			['05-06-2023 16:33', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
			['05-06-2023 16:33:10', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
			['05-06-2023 12:00 am', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
			['05-06-2023 5:00 am', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
			['05-06-2023 06:00:14 am', 'DD-MM-YYYY', [5, 5, 2023, 0, 0, 0]],
		];

		verifyTests(tests);
	});

	it('Should parse datetime format without time', () => {
		const tests = [
			['31/05/1988', 'DD/MM/YYYY HH:MI:SS', [31, 4, 1988, 0, 0, 0]],
			['2014-01-01', 'YYYY-MM-DD HH:MI:SS', [1, 0, 2014, 0, 0, 0]],
			['05/01/2025', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 0, 0, 0]],
			['05/01/2025', 'MM/DD/YYYY H:MI:SS T', [1, 4, 2025, 0, 0, 0]],
			['31/05/1988', 'DD/MM/YYYY HH:MI', [31, 4, 1988, 0, 0, 0]],
			['2014-01-01', 'YYYY-MM-DD HH:MI', [1, 0, 2014, 0, 0, 0]],
			['05/01/2025', 'MM/DD/YYYY H:MI T', [1, 4, 2025, 0, 0, 0]],
			['05/01/2025', 'MM/DD/YYYY H:MI T', [1, 4, 2025, 0, 0, 0]],
		];

		verifyTests(tests);
	});

	it('Should parse dates with month names', () => {
		global.loadMessages({
			langFile: `${__dirname}/../../../../../../main/lang/ru/date_format.php`, // main/lang/en/date_format.php
		});

		const tests = [
			['Май 21, 1984', 'MMMM DD, YYYY', [21, 4, 1984, 0, 0, 0]],
			['May 21, 1984', 'MMMM DD, YYYY', [21, 4, 1984, 0, 0, 0]],
			['21 Мая, 1984', 'DD MMMM, YYYY', [21, 4, 1984, 0, 0, 0]],
			['1 Октябрь 1984', 'DD MMMM YYYY', [1, 9, 1984, 0, 0, 0]],
			['1 Октября 1984', 'DD MMMM YYYY', [1, 9, 1984, 0, 0, 0]],
			['1 Окт 1984', 'DD MMMM YYYY', [1, 9, 1984, 0, 0, 0]],
			['Окт 1, 1984', 'MMMM DD, YYYY', [1, 9, 1984, 0, 0, 0]],
			['Ноябрь - 2024', 'MMMM - YYYY', [1, 10, 2024, 0, 0, 0]],
		];

		verifyTests(tests);
	});

	it('Should parse short formats', () => {
		const { year: currentYear, month: currentMonth } = getDate(createDate(new Date()));

		const tests = [
			['2025', 'YYYY', [1, 0, 2025, 0, 0, 0]],
			['10', 'DD', [10, currentMonth, currentYear, 0, 0, 0]],
			['05', 'MM', [1, 4, currentYear, 0, 0, 0]],
			['05-2019', 'MM-YYYY', [1, 4, 2019, 0, 0, 0]],
			['2019 15 5:04:17', 'YYYY DD H:MI:SS', [15, 0, 2019, 5, 4, 17]],
		];

		verifyTests(tests);
	});

	it('Should not parse invalid dates', () => {
		const tests = [
			['sssss', 'MM/DD/YYYY'],
			['ss/ss', 'MM/DD/YYYY'],
			['', 'MM/DD/YYYY'],
			[' / / ', 'MM/DD/YYYY'],
			['13/10/1900', 'MM/DD/YYYY'], // wrong month
			['12/34/1900', 'MM/DD/YYYY'], // wrong day
			['12/10/19000', 'MM/DD/YYYY'], // wrong year
			['12/10/19', 'MM/DD/YYYY'], // wrong year
			['05/01/2025 12:60 am', 'MM/DD/YYYY H:MI:SS T'],
			['05/01/2025 24:00 am', 'MM/DD/YYYY H:MI:SS T'],
			['05/01/2025 00:00:AA am', 'MM/DD/YYYY H:MI:SS T'],
		];

		for (const test of tests)
		{
			const date = parseDate(test[0], test[1]);
			assert.equal(date, null);
		}
	});
});

function verifyTests(tests)
{
	for (const test of tests)
	{
		const [dateString, dateFormat, result] = test;
		const [dayExpected, monthExpected, yearExpected, hoursExpected, minutesExpected, secondsExpected] = result;

		const date = parseDate(dateString, dateFormat);
		const { day, month, year, hours, minutes, seconds } = getDate(date);

		assert.equal(day, dayExpected, `day: ${dateString}`);
		assert.equal(month, monthExpected, `month: ${dateString}`);
		assert.equal(year, yearExpected, `year: ${dateString}`);
		assert.equal(hours, hoursExpected, `hours: ${dateString}`);
		assert.equal(minutes, minutesExpected, `minutes: ${dateString}`);
		assert.equal(seconds, secondsExpected, `seconds: ${dateString}`);
	}
}
