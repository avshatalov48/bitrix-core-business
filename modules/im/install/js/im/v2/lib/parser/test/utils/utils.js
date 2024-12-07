import 'im.v2.test';

import { ParserUtils } from '../../src/utils/utils';

describe('ParserUtils', () => {
	describe('getDialogIdFromFinalContextTag', () => {
		it('should return dialogId from user context tag', () => {
			const dialogId = ParserUtils.getDialogIdFromFinalContextTag('3/123');
			assert.equal(dialogId, '3');
		});

		it('should return dialogId from chat context tag', () => {
			const dialogId = ParserUtils.getDialogIdFromFinalContextTag('chat3/123');
			assert.equal(dialogId, 'chat3');
		});

		it('should return an empty string for incorrect context tag', () => {
			const invalidTags = [
				'#chat/123',
				'qwe$123',
				'chat3/123/123',
			];

			invalidTags.forEach((contextTag) => {
				assert.equal(ParserUtils.getDialogIdFromFinalContextTag(contextTag), '');
			});
		});
	});
});
