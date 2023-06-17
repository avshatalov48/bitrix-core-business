import 'im.v2.test';
import {Utils} from 'im.v2.lib.utils';
import {Type, Extension} from 'main.core';

describe('Utils.user', () => {
	describe('getCalendarLink', () => {
		let getSettingsStub;

		beforeEach(() => {
			getSettingsStub = sinon.stub(Extension, 'getSettings');
			getSettingsStub.returns({
				get: sinon.stub().returns('/company/personal/user/#user_id#/calendar/')
			});
		});

		afterEach(() => {
			getSettingsStub.restore();
		});

		it('function exists', () => {
			assert.equal(Type.isFunction(Utils.user.getCalendarLink), true);
		});
		it('should return a string with the user ID replaced in the path', () => {
			const userId = 123;
			const expectedResult = '/company/personal/user/123/calendar/';

			const result = Utils.user.getCalendarLink(userId);

			assert.equal(result, expectedResult);
		});

		it('should convert a string user ID to a number before replacing in the path', () => {
			const userId = '333';
			const expectedResult = '/company/personal/user/333/calendar/';

			const result = Utils.user.getCalendarLink(userId);
			assert.equal(result, expectedResult);
		});
	});
});
