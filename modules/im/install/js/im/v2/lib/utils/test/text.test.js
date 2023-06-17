import 'im.v2.test';
import {Utils} from 'im.v2.lib.utils';
import {Type} from 'main.core';

describe('Utils.text', () => {
	describe('isUuidV4', () => {
		it('function exists', () => {
			assert(Type.isFunction(Utils.text.isUuidV4));
		});
		it('returns false for incorrect values', () => {
			assert.equal(Utils.text.isUuidV4(), false);
			assert.equal(Utils.text.isUuidV4('1'), false);
			assert.equal(Utils.text.isUuidV4(1), false);
			assert.equal(Utils.text.isUuidV4({}), false);
			assert.equal(Utils.text.isUuidV4('0eb4bcb3149d414e56193031bcdfd3756edc'), false);
		});
		it('returns true for correct uuid v4', () => {
			assert.equal(Utils.text.isUuidV4('0eb4bcb3-49d4-4e56-9303-bcdfd3756edc'), true);
			assert.equal(Utils.text.isUuidV4('0f6d3bf3-6a7a-4768-b5e9-eeb6d41124de'), true);
			assert.equal(Utils.text.isUuidV4('1bd94fe9-f37e-47fd-b32c-8685aab5b37f'), true);
		});
	});
});
