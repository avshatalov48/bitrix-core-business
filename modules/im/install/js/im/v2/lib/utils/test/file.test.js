import 'im.v2.test';

import { Utils } from 'im.v2.lib.utils';
import { Type } from 'main.core';

describe('Utils.file', () => {
	describe('getShortFileName', () => {
		it('function exists', () => {
			assert(Type.isFunction(Utils.file.getShortFileName));
		});
		it('should return the same filename if the filename length is less than max length', () => {
			const fileName = 'test.txt';
			const maxLength = 20;
			const result = Utils.file.getShortFileName(fileName, maxLength);
			assert.equal(result, fileName);
		});

		it('should return a truncated filename if the filename length is greater than max length', () => {
			const fileName = 'veryLongFileName.txt';
			const maxLength = 10;
			const result = Utils.file.getShortFileName(fileName, maxLength);
			assert.equal(result, 'veryL...me.txt');
		});

		it('should return a truncated filename for a long file name with spaces', () => {
			const fileName = 'very Long File Name.docx';
			const maxLength = 10;
			const result = Utils.file.getShortFileName(fileName, maxLength);
			assert.equal(result, 'very...me.docx');
		});

		it('should return the same filename if the filename without extension length is less than or equal to max length', () => {
			const fileName = 'short.txt';
			const maxLength = 6;
			const result = Utils.file.getShortFileName(fileName, maxLength);
			assert.equal(result, fileName);
		});

		it('should return "undefined" if the file name is missing', () => {
			const fileName = undefined;
			const maxLength = 20;
			const result = Utils.file.getShortFileName(fileName, maxLength);
			assert.equal(result, fileName);
		});
	});
});
