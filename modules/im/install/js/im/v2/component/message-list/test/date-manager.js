import { DateFormatter } from 'im.v2.lib.date-formatter';

import { DateManager } from '../src/classes/collection-manager/classes/date-manager';

describe('DateManager', () => {
	describe('getDateTitle', () => {
		it('should correctly handle timezone offsets', () => {
			// it is 25th October in UTC+0 timezone
			// and 26th October in UTC+2 timezone
			const date = new Date('2023-10-25T23:59:59.000Z');
			sinon.stub(date, 'getTimezoneOffset').returns(-120);

			const expectedTitle = 'some formatted date';
			sinon.stub(DateFormatter, 'formatByTemplate').returns(expectedTitle);

			const dateManager = new DateManager();
			dateManager.getDateTitle(date);

			assert.notEqual(dateManager.cachedDateGroups['2023-10-26'], undefined);
			assert.equal(dateManager.cachedDateGroups['2023-10-26'], expectedTitle);
		});
	});
});
