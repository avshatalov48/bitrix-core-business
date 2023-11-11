import createFileByType from './utils/create-file-by-type.es6';
import getImageSize from '../src/helpers/image-size/get-image-size';

describe('Images Size', () => {
	it('should get size from a gif file', (done) => {
		const gif = createFileByType('gif');
		getImageSize(gif).then((size) => {
			try
			{
				assert.equal(size.width, 32);
				assert.equal(size.height, 16);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a png file', (done) => {
		const png = createFileByType('png');
		getImageSize(png).then((size) => {
			try
			{
				assert.equal(size.width, 100);
				assert.equal(size.height, 100);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a png file (fried)', (done) => {
		const png = createFileByType('png_fried');
		getImageSize(png).then((size) => {
			try
			{
				assert.equal(size.width, 128);
				assert.equal(size.height, 68);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a bmp file', (done) => {
		const bmp = createFileByType('bmp');
		getImageSize(bmp).then((size) => {
			try
			{
				assert.equal(size.width, 24);
				assert.equal(size.height, 22);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a webpVP8 file', (done) => {
		const webp = createFileByType('webpVP8');
		getImageSize(webp).then((size) => {
			try
			{
				assert.equal(size.width, 1);
				assert.equal(size.height, 1);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a webpVP8L file', (done) => {
		const webp = createFileByType('webpVP8L');
		getImageSize(webp).then((size) => {
			try
			{
				assert.equal(size.width, 367);
				assert.equal(size.height, 187);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a webpVP8X file', (done) => {
		const webp = createFileByType('webpVP8X');
		getImageSize(webp).then((size) => {
			try
			{
				assert.equal(size.width, 367);
				assert.equal(size.height, 187);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should get size from a jpeg file', (done) => {
		const webp = createFileByType('jpg');
		getImageSize(webp).then((size) => {
			try
			{
				assert.equal(size.width, 250);
				assert.equal(size.height, 167);
				done();
			}
			catch (exception)
			{
				done(exception);
			}
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done(error);
		});
	});

	it('should return a error for an unsupported image', (done) => {
		const unsupportedImage = createFileByType('unsupported-image');
		getImageSize(unsupportedImage).then((size) => {
			assert.equal(size.width, 2);
			assert.equal(size.height, 1);
		}).catch((error) => {
			assert.ok(error instanceof Error, 'error is empty');
			done();
		});
	});
});
