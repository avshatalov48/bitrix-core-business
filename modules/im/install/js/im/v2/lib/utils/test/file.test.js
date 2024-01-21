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
	describe('resizeToFitMaxSize', () => {
		it('function exists', () => {
			assert(Type.isFunction(Utils.file.resizeToFitMaxSize));
		});
		it('should return the same dimensions if they are already within maxSize', () => {
			const result = Utils.file.resizeToFitMaxSize(100, 200, 300);
			assert.deepStrictEqual(result, { width: 100, height: 200 });
		});

		it('should resize width to fit within maxSize', () => {
			const result = Utils.file.resizeToFitMaxSize(400, 200, 300);
			assert.deepStrictEqual(result, { width: 300, height: 150 });
		});

		it('should resize height to fit within maxSize', () => {
			const result = Utils.file.resizeToFitMaxSize(200, 400, 300);
			assert.deepStrictEqual(result, { width: 150, height: 300 });
		});

		it('should resize both width and height to fit within maxSize', () => {
			const result = Utils.file.resizeToFitMaxSize(500, 1000, 800);
			assert.deepStrictEqual(result, { width: 400, height: 800 });
		});

		it('should handle aspect ratio greater than 1', () => {
			const result = Utils.file.resizeToFitMaxSize(1000, 500, 800);
			assert.deepStrictEqual(result, { width: 800, height: 400 });
		});

		it('should handle aspect ratio less than 1', () => {
			const result = Utils.file.resizeToFitMaxSize(500, 1000, 800);
			assert.deepStrictEqual(result, { width: 400, height: 800 });
		});

		it('should handle square aspect ratio', () => {
			const result = Utils.file.resizeToFitMaxSize(200, 200, 100);
			assert.deepStrictEqual(result, { width: 100, height: 100 });
		});
	});
});
