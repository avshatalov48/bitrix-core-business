import Uploader from '../src/uploader';
import { UploaderEvent } from '../src/enums/uploader-event';

import createFileByType from './utils/create-file-by-type.es6';
import CustomUploadController from './utils/custom-upload-controller.es6';
import { BaseEvent } from 'main.core.events';
import { UploaderFile } from 'ui.uploader.core';
import Server from '../src/backend/server';
// import mock from 'xhr-mock';

describe('File Upload', () => {
	it('should invoke onComplete callback', (done) => {
		const progressValues = [19, 37, 56, 74, 93, 100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			autoUpload: false,
			serverOptions: {
				chunkSize: 7,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
					file.upload({
						onComplete: (event) => {
							assert.equal(file.isComplete(), true);
							done();
						},
					});
				},
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_ERROR]: (event) => {
					done(event.getData().error);
				},
				[UploaderEvent.FILE_COMPLETE]: () => {
					try
					{
						assert.equal(handleProgress.callCount, 6, 'onProgress Count');
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		const file = new File(['<html><body><b>Hello</b></body></html>'], 'index2.html', { type: 'text/html' });
		uploader.addFile(file);
	});

	it('should invoke onError callback', (done) => {
		const progressValues = [19, 37, 56, 74, 93, 100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			autoUpload: false,
			serverOptions: {
				chunkSize: 7,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
				uploadControllerOptions: {
					raiseError: true,
					raiseErrorStep: 5,
				},
			},
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
					file.upload({
						onError: (event) => {
							assert.equal(file.isUploadFailed(), true);
							done();
						},
					});
				},
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_ERROR]: (event) => {
					const { error } = event.getData();
					assert.equal(error.getCode(), 'CUSTOM_UPLOAD_ERROR');
				},
				[UploaderEvent.FILE_COMPLETE]: () => {
					try
					{
						assert.equal(handleProgress.callCount, 5, 'onProgress Count');
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		const file = new File(['<html><body><b>Hello</b></body></html>'], 'index2.html', { type: 'text/html' });
		uploader.addFile(file);
	});

	it('should invoke callbacks', (done) => {
		const progressValues = {
			'hello.html': [19, 37, 56, 74, 93, 100],
			'image.gif': [12, 23, 34, 45, 56, 67, 78, 89, 100, 100],
		};

		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { file, progress } = event.getData();
			assert.equal(progress, progressValues[file.getName()].shift(), 'progress values');
		});

		const handleHelloOnComplete = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.isComplete(), true);
		});

		const handleHelloOnError = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.isUploadFailed(), true);
		});

		const handleGifOnComplete = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.isComplete(), true);
		});

		const handleGifOnError = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.isUploadFailed(), true);
		});

		const uploader = new Uploader({
			controller: 'fake.controller',
			autoUpload: false,
			multiple: true,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
					if (file.getName() === 'hello.html')
					{
						file.setUploadController(new CustomUploadController(
							new Server({
								chunkSize: 7,
								forceChunkSize: true,
							}),
							{
								raiseError: true,
								raiseErrorStep: 5,
							},
						));

						file.upload({
							onComplete: handleHelloOnComplete,
							onError: handleHelloOnError,
						});
					}
					else if (file.getName() === 'image.gif')
					{
						file.setUploadController(new CustomUploadController(
							new Server({
								chunkSize: 50,
								forceChunkSize: true,
							}),
						));

						setTimeout(() => {
							file.upload({
								onComplete: handleGifOnComplete,
								onError: handleGifOnError,
							});
						}, 100);
					}
				},
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_ERROR]: (event) => {
					try
					{
						const { error } = event.getData();
						assert.equal(error.getCode(), 'CUSTOM_UPLOAD_ERROR');
					}
					catch (exception)
					{
						done(exception);
					}
				},
				onUploadComplete: () => {
					try
					{
						setTimeout(() => {
							assert.equal(handleHelloOnComplete.callCount, 0);
							assert.equal(handleHelloOnError.callCount, 1);
							assert.equal(handleGifOnComplete.callCount, 1);
							assert.equal(handleGifOnError.callCount, 0);
							assert.equal(handleProgress.callCount, 15, 'onProgress Count');

							uploader.getFile('gif').upload({
								onComplete: handleGifOnComplete,
								onError: handleGifOnError,
							});

							uploader.getFile('html').upload({
								onComplete: handleHelloOnComplete,
								onError: handleHelloOnError,
							});

							assert.equal(handleHelloOnComplete.callCount, 0);
							assert.equal(handleHelloOnError.callCount, 2);
							assert.equal(handleGifOnComplete.callCount, 2);
							assert.equal(handleGifOnError.callCount, 0);

							uploader.getFile('gif').upload({
								onComplete: handleGifOnComplete,
								onError: handleGifOnError,
							});

							uploader.getFile('html').upload({
								onComplete: handleHelloOnComplete,
								onError: handleHelloOnError,
							});

							assert.equal(handleHelloOnComplete.callCount, 0);
							assert.equal(handleHelloOnError.callCount, 3);
							assert.equal(handleGifOnComplete.callCount, 3);
							assert.equal(handleGifOnError.callCount, 0);

							done();
						}, 0); // callbacks are invoked after onUploadComplete
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFiles([
			[new File(['<html><body><b>Hello</b></body></html>'], 'hello.html', { type: 'text/html' }), { id: 'html' }],
			[createFileByType('gif'), { id: 'gif' }],
		]);
	});
});
