import createFileByType from './utils/create-file-by-type.es6';
import Uploader from '../src/uploader';
import { BaseEvent } from 'main.core.events';
import { BaseError } from 'main.core';
import { UploaderEvent } from '../src/enums/uploader-event';

describe('Image Size Validation', () => {
	const png = createFileByType('png'); // 100 x 100
	const gif = createFileByType('gif'); // 32 x 16
	const jpg = createFileByType('jpg'); // 250 x 167
	const text = createFileByType('text');

	it('should accept non-image files', (done) => {
		const uploader = new Uploader({
			imageMinWidth: 10,
			imageMinHeight: 10,
			imageMaxWidth: 100,
			imageMaxHeight: 100,
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();
						assert.ifError(error);
						assert.equal(file.isComplete(), true);
						assert.equal(file.isImage(), false);

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFile(text);
	});

	it('should not accept wrong image files', (done) => {
		const uploader = new Uploader({
			imageMinWidth: 10,
			imageMinHeight: 10,
			imageMaxWidth: 100,
			imageMaxHeight: 100,
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'IMAGE_TYPE_NOT_SUPPORTED');
						assert.equal(file.isFailed(), true, 'failed');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFile(new File(['123'], 'image.png', { type: 'image/png' }));
	});

	it('should accept files that have max/min dimensions', (done) => {
		const uploader = new Uploader({
			imageMinWidth: 32,
			imageMinHeight: 16,
			imageMaxWidth: 250,
			imageMaxHeight: 167,
			autoUpload: false,
			multiple: true,
		});

		const listener = sinon.stub().callsFake((event: BaseEvent) => {
			const { file, error } = event.getData();
			try
			{
				assert.ifError(error);
				assert.equal(file.isComplete(), true);

				if (listener.callCount === 2)
				{
					done();
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD, listener);
		uploader.addFiles([gif, jpg]);
	});

	['imageMinWidth', 'imageMinHeight'].forEach((option) => {
		it(`should accept files less than ${option}`, (done) => {
			const uploader = new Uploader({
				[option]: 50,
				autoUpload: false,
				multiple: true,
			});

			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (['png', 'jpg'].includes(file.getExtension()))
					{
						acceptCount++;
						assert.ifError(error);
						assert.equal(file.isComplete(), true);
					}
					else
					{
						failedCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'IMAGE_IS_TOO_SMALL');
						assert.equal(file.isFailed(), true);
					}

					if (listener.callCount === 3)
					{
						assert.equal(acceptCount, 2);
						assert.equal(failedCount, 1);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			uploader.subscribe(UploaderEvent.FILE_ADD, listener);
			uploader.addFiles([gif, png, jpg]);
		});
	});

	['imageMaxWidth', 'imageMaxHeight'].forEach((option) => {
		it(`should accept files less than ${option}`, (done) => {
			const uploader = new Uploader({
				[option]: 150,
				autoUpload: false,
				multiple: true,
			});

			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (['png', 'gif'].includes(file.getExtension()))
					{
						acceptCount++;
						assert.ifError(error);
						assert.equal(file.isComplete(), true, 'ready to upload');
					}
					else
					{
						failedCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'IMAGE_IS_TOO_BIG');
						assert.equal(file.isFailed(), true, 'failed');
					}

					if (listener.callCount === 3)
					{
						assert.equal(acceptCount, 2);
						assert.equal(failedCount, 1);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			uploader.subscribe(UploaderEvent.FILE_ADD, listener);
			uploader.addFiles([gif, png, jpg]);
		});
	});
});
