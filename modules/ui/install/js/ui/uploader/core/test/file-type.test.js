import isResizableImage from '../src/helpers/is-resizable-image';
import Uploader from '../src/uploader';
import { UploaderEvent } from '../src/enums/uploader-event';

import { BaseEvent } from 'main.core.events';
import { BaseError } from 'main.core';
import createFileByType from './utils/create-file-by-type.es6';

describe('File Type Validation', () => {
	// mime types: image/png, image/jpeg, image/gif
	// extensions: .png, .jpg
	// wildcards: image/*

	const png = createFileByType('png');
	const gif = createFileByType('gif');
	const jpg = createFileByType('jpg');
	const text = createFileByType('text');
	const csv = createFileByType('csv');
	const css = createFileByType('css');
	const json = createFileByType('json');
	const tiff = createFileByType('tiff');

	describe('Mime Types', () => {
		it('should accept only png, gif and jpg', (done) => {
			const imageUploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['image/png', 'image/gif', 'image/jpg'],
				multiple: true,
			});
			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (['png', 'gif', 'jpg'].includes(file.getExtension()))
					{
						acceptCount++;
						assert.ifError(error);
						assert.equal(file.isImage(), true);
						assert.equal(file.isComplete(), true);
					}
					else
					{
						failedCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');
						assert.equal(file.isFailed(), true);
					}

					if (listener.callCount === 7)
					{
						assert.equal(acceptCount, 3);
						assert.equal(failedCount, 4);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			imageUploader.subscribe(UploaderEvent.FILE_ADD, listener);
			imageUploader.addFiles([png, jpg, gif, css, json, text, csv]);
		});

		it('should accept only css, json, text and csv', (done) => {
			const imageUploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['text/css', 'application/json', 'text/plain', 'text/csv'],
				multiple: true,
			});

			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (isResizableImage(file.getName(), file.getType()))
					{
						acceptCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');
						assert.equal(file.isFailed(), true);
					}
					else
					{
						failedCount++;
						assert.ifError(error);
						assert.equal(file.isComplete(), true);
					}

					if (listener.callCount === 7)
					{
						assert.equal(acceptCount, 3);
						assert.equal(failedCount, 4);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			imageUploader.subscribe(UploaderEvent.FILE_ADD, listener);
			imageUploader.addFiles([png, json, text, jpg, gif, css, csv]);
		});
	});

	describe('Extensions', () => {
		it('should accept only .png, .gif, .csv and .jpg extensions', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['.png', '.gif', '.csv', '.jpg'],
				multiple: true,
			});

			let acceptCount = 0;
			let failCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (file.isImage() || file.getExtension() === 'csv')
					{
						acceptCount++;
						assert.ifError(error);
						assert.equal(file.isComplete(), true);
					}
					else
					{
						failCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');
						assert.equal(file.isFailed(), true);
					}

					if (listener.callCount === 7)
					{
						assert.equal(acceptCount, 4);
						assert.equal(failCount, 3);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			uploader.subscribe(UploaderEvent.FILE_ADD, listener);
			uploader.addFiles([png, jpg, gif, css, json, text, csv]);
		});
	});

	describe('WildCards', () => {
		it('should accept only images', (done) => {
			const imageUploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['image/*'],
				multiple: true,
			});
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					assert.ifError(error);
					assert.equal(file.isComplete(), true);
					if (listener.callCount === 3)
					{
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			imageUploader.subscribe(UploaderEvent.FILE_ADD, listener);
			imageUploader.addFile(png);
			imageUploader.addFile(jpg);
			imageUploader.addFile(gif);
		});

		it('should deny all images expect images', (done) => {
			const imageUploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['image/*'],
				multiple: true,
			});

			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					assert.ok(error instanceof BaseError, 'error is empty');
					assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');
					assert.equal(file.isFailed(), true);

					if (listener.callCount === 3)
					{
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			imageUploader.subscribe(UploaderEvent.FILE_ADD, listener);
			imageUploader.addFile(text);
			imageUploader.addFile(json);
			imageUploader.addFile(csv);
		});
	});

	describe('All together', () => {
		it('should accept only css, json, text and csv', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: ['image/png', '.gif', '.jpg', 'application/*'],
				multiple: true,
			});

			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (['png', 'gif', 'jpg', 'json'].includes(file.getExtension()))
					{
						acceptCount++;
						assert.ifError(error);
						assert.equal(file.isComplete(), true);
					}
					else
					{
						failedCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');
						assert.equal(file.isFailed(), true);
					}

					if (listener.callCount === 7)
					{
						assert.equal(acceptCount, 4);
						assert.equal(failedCount, 3);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			uploader.subscribe(UploaderEvent.FILE_ADD, listener);
			uploader.addFiles([png, json, text, jpg, gif, css, csv]);
		});
	});

	describe('Empty list', () => {
		it('should accept all files', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				acceptedFileTypes: [],
				multiple: true,
			});
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					assert.ifError(error);
					assert.equal(file.isComplete(), true);
					if (listener.callCount === 7)
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
			uploader.addFiles([png, json, text, jpg, gif, css, csv]);
		});
	});

	describe('Accept Only Images', () => {
		it('should accept only images', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				acceptOnlyImages: true,
				multiple: true,
			});

			let acceptCount = 0;
			let failedCount = 0;
			const listener = sinon.stub().callsFake((event: BaseEvent) => {
				const { file, error } = event.getData();
				try
				{
					if (['jpg', 'bmp', 'jpeg', 'jpe', 'gif', 'png', 'webp'].includes(file.getExtension()))
					{
						acceptCount++;
						assert.equal(false, error instanceof BaseError, `${file.getName()}: ${error ? error.toString() : ''}`);
						assert.equal(file.isComplete(), true);
					}
					else
					{
						failedCount++;
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED', file.getName());
						assert.equal(file.isFailed(), true);
					}

					if (listener.callCount === 8)
					{
						assert.equal(acceptCount, 3);
						assert.equal(failedCount, 5);
						done();
					}
				}
				catch (exception)
				{
					done(exception);
				}
			});

			uploader.subscribe(UploaderEvent.FILE_ADD, listener);
			uploader.addFiles([png, json, text, jpg, gif, css, csv, tiff]);
		});

		it('should raise an error for an unsupported image', (done) => {
			const unsupportedImage = createFileByType('unsupported-image');
			const uploader = new Uploader({
				acceptOnlyImages: true,
				multiple: true,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(error.getCode(), 'IMAGE_TYPE_NOT_SUPPORTED');
							assert.equal(file.isFailed(), true);
							done();
						}
						catch (exception)
						{
							done(exception);
						}
					},
				},
			});

			uploader.addFile(unsupportedImage);
		});
	});
});
