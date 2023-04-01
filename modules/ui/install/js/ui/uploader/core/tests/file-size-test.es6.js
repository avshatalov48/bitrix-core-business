import Uploader from '../src/uploader';
import { UploaderEvent } from '../src/enums/uploader-event';
import { BaseEvent } from 'main.core.events';
import { BaseError } from 'main.core';
import createFileByType from './utils/create-file-by-type.es6';

describe('File Size Validation', () => {
	describe('maxFileSize', () => {
		it('should not accept a file more than maxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1025);

							assert.ok(error instanceof BaseError, 'error is empty');
							assert.equal(error.getCode(), 'MAX_FILE_SIZE_EXCEEDED');
							assert.equal(file.isFailed(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1025)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});

		it('should accept a file less than maxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1023);

							assert.ifError(error);

							console.log(file.getStatus());
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1023)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});

		it('should accept a file that equals maxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1024);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1024)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});

	});

	describe('minFileSize', () => {
		it('should not accept a file less than minFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				minFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1023);

							assert.ok(error instanceof BaseError, 'error is empty');
							assert.equal(error.getCode(), 'MIN_FILE_SIZE_EXCEEDED');
							assert.equal(file.isFailed(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1023)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});

		it('should accept a file more than minFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				minFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1025);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1025)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});


		it('should accept a file that equals minFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				minFileSize: 1024,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getName(), 'nulls.txt');
							assert.equal(file.getType(), 'text/plain');
							assert.equal(file.getSize(), 1024);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const blob = new Blob(['0'.repeat(1024)], { type: 'text/plain' });
			uploader.addFile(blob, { name: 'nulls.txt' });
		});

	});

	describe('imageMaxFileSize', () => {
		it('should not accept an image more than imageMaxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMaxFileSize: 451,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(file.getType(), 'image/gif');
							assert.equal(file.getSize(), 452);

							assert.ok(error instanceof BaseError, 'error is empty');
							assert.equal(error.getCode(), 'IMAGE_MAX_FILE_SIZE_EXCEEDED');
							assert.equal(file.isFailed(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const gif = createFileByType('gif');
			uploader.addFile(gif);
		});

		it('should accept an image less than imageMaxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMaxFileSize: 453,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getType(), 'image/gif');
							assert.equal(file.getSize(), 452);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const gif = createFileByType('gif');
			uploader.addFile(gif);
		});

		it('should accept an image that equals imageMaxFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMaxFileSize: 452,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(file.getType(), 'image/gif');
							assert.equal(file.getSize(), 452);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const gif = createFileByType('gif');
			uploader.addFile(gif);
		});

	});

	describe('imageMinFileSize', () => {
		it('should not accept an image less than imageMinFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMinFileSize: 1042,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							assert.equal(file.getType(), 'image/png');
							assert.equal(file.getSize(), 1041);

							assert.ok(error instanceof BaseError, 'error is empty');
							assert.equal(error.getCode(), 'IMAGE_MIN_FILE_SIZE_EXCEEDED');
							assert.equal(file.isFailed(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const png = createFileByType('png');
			uploader.addFile(png);
		});

		it('should accept am image more than imageMinFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMinFileSize: 1040,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getType(), 'image/png');
							assert.equal(file.getSize(), 1041);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const png = createFileByType('png');
			uploader.addFile(png);
		});


		it('should accept an image that equals imageMinFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				imageMinFileSize: 1041,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();

						try
						{
							assert.equal(file.getType(), 'image/png');
							assert.equal(file.getSize(), 1041);

							assert.ifError(error);
							assert.equal(file.isComplete(), true);

							done();
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			const png = createFileByType('png');
			uploader.addFile(png);
		});

	});

	describe('maxTotalFileSize', () => {
		it('should not accept files less than maxTotalFileSize', (done) => {
			const uploader = new Uploader({
				autoUpload: false,
				maxTotalFileSize: 350,
				multiple: true,
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const { file, error } = event.getData();
						try
						{
							if (['first.txt', 'second.txt', 'third.txt', 'sixth'].includes(file.getName()))
							{
								assert.ifError(error);
								assert.equal(file.isComplete(), true);
							}
							else if (['fourth.txt', 'fifth.txt', 'sixth.txt', 'seventh.txt'].includes(file.getName()))
							{
								assert.ok(error instanceof BaseError, 'error is empty');
								assert.equal(error.getCode(), 'MAX_TOTAL_FILE_SIZE_EXCEEDED');
								assert.equal(file.isFailed(), true);

								if (file.getName() === 'sixth.txt')
								{
									done();
								}
							}
							else
							{
								assert.fail('Something gone wrong.');
							}
						}
						catch(exception)
						{
							done(exception);
						}
					},
				}
			});

			['first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh'].forEach(name => {
				setTimeout(() => {
					uploader.addFile(new File(
						['1'.repeat(name === 'sixth' ? 50 : 100)],
						`${name}.txt`,
						{ type: 'text/plain' }
					));
				}, 100);
			});
		});
	});
});
