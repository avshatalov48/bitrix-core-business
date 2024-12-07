import Uploader from '../src/uploader';

import { FileStatus } from '../src/enums/file-status';
import { FileOrigin } from '../src/enums/file-origin';
import { FileEvent } from '../src/enums/file-event';

import type { BaseEvent } from 'main.core.events';

import UploaderFile from '../src/uploader-file';
import createFileByType from './utils/create-file-by-type.es6';
import CustomLoadController from './utils/custom-load-controller.es6';
import CustomUploadController from './utils/custom-upload-controller.es6';
import { UploaderError } from 'ui.uploader.core';

describe('File Events', () => {
	it('should emit onLoadStart', (done) => {
		const uploader = new Uploader();
		uploader.addFile(createFileByType('tiff'), {
			events: {
				[FileEvent.LOAD_START]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.LOADING);
						assert.equal(file.isLoading(), true);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadProgress (client loading)', (done) => {
		const progressValues = [100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader();
		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.LOAD_PROGRESS]: handleProgress,
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);
						assert.equal(handleProgress.callCount, 1, 'onProgress Count');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadProgress (server loading)', (done) => {
		const progressValues = [12, 23, 34, 45, 56, 67, 78, 89, 100, 100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				loadControllerClass: CustomLoadController,
				/* loadControllerOptions: {
					raiseError: true,
					raiseErrorStep: 7
				}, */
			},
		});

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 452,
			type: 'image/png',
			preload: true,
			events: {
				[FileEvent.LOAD_PROGRESS]: handleProgress,
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.SERVER);
						assert.equal(handleProgress.callCount, 10, 'onProgress Count');
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadComplete (server loading)', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				loadControllerClass: CustomLoadController,
			},
		});

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 1041,
			type: 'image/png',
			events: {
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.SERVER);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadComplete (serverless)', (done) => {
		const uploader = new Uploader();
		uploader.addFile(createFileByType('tiff'), {
			events: {
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadComplete (pending to upload)', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			controller: 'fake-uploader',
		});

		uploader.addFile(createFileByType('tiff'), {
			events: {
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.PENDING);
						assert.equal(file.isReadyToUpload(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onLoadError (client loading)', (done) => {
		const uploader = new Uploader({
			acceptOnlyImages: true,
		});

		uploader.addFile(createFileByType('tiff'), {
			events: {
				[FileEvent.LOAD_ERROR]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.LOAD_FAILED);
						assert.equal(file.isFailed(), true);
						assert.equal(file.isLoadFailed(), true);

						const error: UploaderError = event.getData().error;
						assert.equal(error.getCode(), 'FILE_TYPE_NOT_ALLOWED');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onLoadComplete was emitted.'));
				},
			},
		});
	});

	it('should emit onLoadError (server loading)', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				loadControllerClass: CustomLoadController,
				loadControllerOptions: {
					raiseError: true,
					raiseErrorStep: 7,
				},
			},
		});

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 1041,
			type: 'image/png',
			preload: true,
			events: {
				[FileEvent.LOAD_ERROR]: (event: BaseEvent) => {
					try
					{
						const file: UploaderFile = event.getTarget();
						assert.equal(file.getStatus(), FileStatus.LOAD_FAILED);

						const error: UploaderError = event.getData().error;
						assert.equal(error.getCode(), 'CUSTOM_LOAD_ERROR');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onLoadComplete was emitted.'));
				},
			},
		});
	});

	it('should emit onLoadError (after abort)', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				loadControllerClass: CustomLoadController,
			},
		});

		let onProgressCount = 0;

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 452,
			type: 'image/png',
			preload: true,
			events: {
				[FileEvent.LOAD_PROGRESS]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					if (++onProgressCount < 3)
					{
						file.abort();
					}
				},
				[FileEvent.LOAD_ERROR]: (event: BaseEvent) => {
					try
					{
						const file: UploaderFile = event.getTarget();
						assert.equal(file.getStatus(), FileStatus.LOAD_FAILED);

						const error: UploaderError = event.getData().error;
						assert.equal(error.getCode(), 'FILE_LOAD_ABORTED');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onLoadComplete was emitted.'));
				},
			},
		});
	});

	it('should emit onBeforeUpload', (done) => {
		done();
	});

	it('should emit onUploadStart', (done) => {
		const uploader = new Uploader({
			controller: 'fake-controller',
		});
		uploader.addFile(createFileByType('tiff'), {
			events: {
				[FileEvent.UPLOAD_START]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.UPLOADING);
						assert.equal(file.isUploading(), true);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onUploadProgress', (done) => {
		const progressValues = [12, 23, 34, 45, 56, 67, 78, 89, 100, 100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
		});

		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.UPLOAD_PROGRESS]: handleProgress,
				[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);

						assert.equal(handleProgress.callCount, 10, 'onProgress Count');

						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onUploadComplete', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
		});

		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(file.isComplete(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onUploadError', (done) => {
		const progressValues = [12, 23, 34, 45, 56, 67, 78, 89, 100, 100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
				uploadControllerOptions: {
					raiseError: true,
					raiseErrorStep: 5,
				},
			},
		});

		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.UPLOAD_PROGRESS]: handleProgress,
				[FileEvent.UPLOAD_ERROR]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					const error: UploaderError = event.getData().error;
					try
					{
						assert.equal(file.getStatus(), FileStatus.UPLOAD_FAILED);
						assert.equal(file.isFailed(), true);
						assert.equal(file.getOrigin(), FileOrigin.CLIENT);
						assert.equal(error.getCode(), 'CUSTOM_UPLOAD_ERROR');
						assert.equal(handleProgress.callCount, 5, 'onProgress Count');
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});
	});

	it('should emit onUploadError (after abort)', (done) => {
		let progressCnt = 0;
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			progressCnt++;
			if (progressCnt === 5)
			{
				const file: UploaderFile = event.getTarget();
				file.abort();
			}
		});

		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
		});

		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.UPLOAD_PROGRESS]: handleProgress,
				[FileEvent.UPLOAD_ERROR]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					assert.equal(file.getStatus(), FileStatus.UPLOAD_FAILED);
					assert.equal(file.isFailed(), true);
					assert.equal(file.getOrigin(), FileOrigin.CLIENT);

					assert.equal(handleProgress.callCount, 5, 'onProgress Count');
					done();
				},
				[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onUploadComplete emitted.'));
				},
			},
		});
	});

	it('should emit onLoadControllerInit', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				loadControllerClass: CustomLoadController,
			},
		});

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 452,
			type: 'image/png',
			preload: true,
			events: {
				[FileEvent.LOAD_CONTROLLER_INIT]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					const controller: CustomLoadController = event.getData().controller;
					assert.equal(controller instanceof CustomLoadController, true);
					assert.equal(file.getStatus(), FileStatus.INIT);
					done();
				},
			},
		});
	});

	it('should emit onUploadControllerInit', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
		});

		uploader.addFile(createFileByType('gif'), {
			events: {
				[FileEvent.UPLOAD_CONTROLLER_INIT]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();
					const controller: CustomUploadController = event.getData().controller;
					assert.equal(controller instanceof CustomUploadController, true);
					assert.equal(file.getStatus(), FileStatus.INIT);

					done();
				},
			},
		});
	});

	it('should emit onRemoveComplete', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
		});

		const gif = createFileByType('gif');
		uploader.addFile(gif, {
			events: {
				[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getTarget();

					assert.equal(uploader.getFiles().length, 1);
					assert.equal(uploader.getTotalSize(), gif.size);

					file.remove();
				},
				[FileEvent.REMOVE_COMPLETE]: (event: BaseEvent) => {
					done();
				},
			},
		});
	});

	it('should emit onStateChange', () => {
		const changes = {
			name: 'new name',
			type: 'image/tiff',
			size: 1000,
			serverFileId: 123,
			downloadUrl: '/dddd/',
			width: 100,
			height: 200,
			clientPreviewUrl: null,
			clientPreviewWidth: 50,
			clientPreviewHeight: 55,
			serverPreviewUrl: null,
			serverPreviewWidth: 1000,
			serverPreviewHeight: 1000,
			progress: 80,
		};

		const properties = Object.keys(changes);
		const handleStateChange = sinon.stub().callsFake((event: BaseEvent) => {
			const { property, value } = event.getData();
			assert.equal(property, properties.shift());
			assert.equal(value, changes[property]);
		});

		const file = new UploaderFile(createFileByType('gif'), {
			events: {
				[FileEvent.STATE_CHANGE]: handleStateChange,
			},
		});

		assert.equal(file.getStatus(), FileStatus.INIT);

		file.setName(changes.name);
		file.setType(changes.type);
		file.setSize(changes.size);
		file.setServerFileId(changes.serverFileId);
		file.setDownloadUrl(changes.downloadUrl);

		file.setWidth(changes.width);
		file.setHeight(changes.height);
		file.setClientPreview(changes.clientPreviewUrl, changes.clientPreviewWidth, changes.clientPreviewHeight);
		file.setServerPreview(changes.serverPreviewUrl, changes.serverPreviewWidth, changes.serverPreviewHeight);
		file.setProgress(changes.progress);

		assert.equal(handleStateChange.callCount, Object.keys(changes).length);
	});

	it('should emit onStatusChange', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
		});

		const statuses = [
			FileStatus.ADDED,
			FileStatus.LOADING,
			FileStatus.PENDING,
			FileStatus.PREPARING,
			FileStatus.UPLOADING,
			FileStatus.COMPLETE,
		];

		const handleStatusChange = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.getStatus(), statuses.shift(), 'Wrong Status');
		});

		const gif = createFileByType('gif');
		uploader.addFile(gif, {
			events: {
				[FileEvent.STATUS_CHANGE]: handleStatusChange,
				[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					assert.equal(handleStatusChange.callCount, 6);
					done();
				},
			},
		});
	});

	it('should emit onStatusChange (only loading)', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			controller: 'fake-uploader',
		});

		const statuses = [
			FileStatus.ADDED,
			FileStatus.LOADING,
			FileStatus.PENDING,
		];

		const handleStatusChange = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.getStatus(), statuses.shift());
		});

		const gif = createFileByType('gif');
		uploader.addFile(gif, {
			events: {
				[FileEvent.STATUS_CHANGE]: handleStatusChange,
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					assert.equal(handleStatusChange.callCount, 3);
					done();
				},
			},
		});
	});

	it('should emit onStatusChange (only loading serverless)', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
		});

		const statuses = [
			FileStatus.ADDED,
			FileStatus.LOADING,
			FileStatus.COMPLETE,
		];

		const handleStatusChange = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getTarget();
			assert.equal(file.getStatus(), statuses.shift());
		});

		const gif = createFileByType('gif');
		uploader.addFile(gif, {
			events: {
				[FileEvent.STATUS_CHANGE]: handleStatusChange,
				[FileEvent.LOAD_COMPLETE]: (event: BaseEvent) => {
					assert.equal(handleStatusChange.callCount, 3);
					done();
				},
			},
		});
	});
});
