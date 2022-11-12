import {Uploader} from 'im.lib.uploader';
import {Type} from 'main.core';
import 'im.test';

describe('Uploader', () => {
	it('Should be a function', () => {
		assert(Type.isFunction(Uploader));
	});

	describe('calculateChunkSize', () => {
		it('should return 100 Mb chunk size for cloud B24, even if we want more', () => {
			Uploader.prototype.isCloud = 'Y';
			const chunkSize = Uploader.prototype.calculateChunkSize(1024 * 1024 * 500);
			assert.equal(chunkSize, 1024 * 1024 * 100);
		});

		it('should return 5 Mb chunk size for cloud B24, even if we want less', () => {
			Uploader.prototype.isCloud = 'Y';
			const chunkSize = Uploader.prototype.calculateChunkSize(1024 * 1024 * 2);
			assert.equal(chunkSize, 1024 * 1024 * 5);
		});

		it('should return 10 Mb chunk size for cloud B24', () => {
			Uploader.prototype.isCloud = 'Y';
			const chunkSize = Uploader.prototype.calculateChunkSize(1024 * 1024 * 10);
			assert.equal(chunkSize, 1024 * 1024 * 10);
		});

		it('should return chunk size based on php settings (min from 2 options) for self hosted B24', () => {
			Uploader.prototype.isCloud = 'N';
			Uploader.prototype.phpUploadMaxFilesize = 1024 * 1024 * 10;
			Uploader.prototype.phpPostMaxSize = 1024 * 1024 * 5;

			const chunkSize = Uploader.prototype.calculateChunkSize(1024 * 1024 * 40);
			assert.equal(chunkSize, 1024 * 1024 * 5);
		});

		it('should return 1 Mb chunk size for self hosted B24, even if we want more', () => {
			Uploader.prototype.isCloud = 'N';
			Uploader.prototype.phpUploadMaxFilesize = 1024 * 1024 * 10;
			Uploader.prototype.phpPostMaxSize = 1024 * 1024 * 5;

			const chunkSize = Uploader.prototype.calculateChunkSize(1);
			assert.equal(chunkSize, 1024 * 1024);
		});

		it('should return not changed chunk size if we have undefined value for isCloud param (widget case)', () => {
			Uploader.prototype.isCloud = undefined;
			const initialChunkSize = 1024 * 1024 * 100;
			const calculatedChunkSize = Uploader.prototype.calculateChunkSize(initialChunkSize);
			assert.equal(calculatedChunkSize, initialChunkSize);
		});
	});
});