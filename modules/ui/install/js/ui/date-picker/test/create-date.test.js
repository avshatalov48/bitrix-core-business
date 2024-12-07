import { createDate } from '../src/helpers/create-date';
import { getDate } from '../src/helpers/get-date';

// MM/DD/YYYY H:MI:SS T
// MM/DD/YYYY

describe('Create date', () => {
	it('Should be a function', () => {
		const date = createDate('03/20/2020', 'MM/DD/YYYY');
		const { day, month, year } = getDate(date);

		assert.equal(day, 20);
		assert.equal(month, 2);
		assert.equal(year, 2020);
	});
});
