import Uploader from '../src/uploader';
import createFileByType from './utils/create-file-by-type.es6';
import CustomUploadController from './utils/custom-upload-controller.es6';
import { UploaderEvent } from '../src/enums/uploader-event';
import type { BaseEvent } from 'main.core.events';
import { FileOrigin, FileStatus, UploaderError } from 'ui.uploader.core';
import UploaderFile from '../src/uploader-file';
import CustomLoadController from './utils/custom-load-controller.es6';

describe('Uploader File Events', () => {
	it('should emit File:onBeforeAdd', (done) => {
		const handleBeforeAdd = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getData().file;

			if (file.getType() === 'image/tiff')
			{
				event.preventDefault();
			}

			assert.equal(file.getStatus(), FileStatus.INIT);
		});

		const uploader = new Uploader({
			autoUpload: false,
			multiple: true,
			events: {
				[UploaderEvent.FILE_BEFORE_ADD]: handleBeforeAdd,
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(handleBeforeAdd.callCount, 2);
						assert.equal(uploader.getFiles().length, 1);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFile(createFileByType('tiff'));
		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onAddStart', (done) => {
		const handleAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getData().file;
			assert.equal(file.getStatus(), FileStatus.ADDED);
		});

		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD_START]: handleAddStart,
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
					try
					{
						assert.equal(file.getStatus(), FileStatus.COMPLETE);
						assert.equal(handleAddStart.callCount, 1);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onLoadStart', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_LOAD_START]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onLoadProgress', (done) => {
		const progressValues = [100];
		const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
			const { progress } = event.getData();
			assert.equal(progress, progressValues.shift(), 'progress values');
		});

		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_LOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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
		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onLoadComplete (serverless)', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onLoadComplete', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			controller: 'fake-controller',
			events: {
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onComplete (after load)', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onComplete (after upload)', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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
		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onUploadStart', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_START]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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
		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onUploadProgress', (done) => {
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
			events: {
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onUploadComplete', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onError (after client loading)', (done) => {
		const uploader = new Uploader({
			acceptOnlyImages: true,
			events: {
				[UploaderEvent.FILE_ERROR]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onLoadComplete was emitted.'));
				},
			},
		});

		uploader.addFile(createFileByType('tiff'));
	});

	it('should emit File:onError (after server loading)', (done) => {
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
			events: {
				[UploaderEvent.FILE_ERROR]: (event: BaseEvent) => {
					try
					{
						const file: UploaderFile = event.getData().file;
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
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					done(new Error('onLoadComplete was emitted.'));
				},
			},
		});

		uploader.addFile(1041, {
			id: 'my-file',
			name: 'image.png',
			size: 1041,
			type: 'image/png',
			preload: true,
		});
	});

	it('should emit File:onError (after uploading)', (done) => {
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
			events: {
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
				[UploaderEvent.FILE_ERROR]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;
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

		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onRemove', (done) => {
		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					const file: UploaderFile = event.getData().file;

					assert.equal(uploader.getFiles().length, 1);
					assert.equal(uploader.getTotalSize(), gif.size);

					file.remove();
				},
				[UploaderEvent.FILE_REMOVE]: (event: BaseEvent) => {
					done();
				},
			},
		});

		const gif = createFileByType('gif');
		uploader.addFile(gif);
	});

	it('should emit File:onStatusChange', (done) => {
		const statuses = [
			FileStatus.ADDED,
			FileStatus.LOADING,
			FileStatus.PENDING,
			FileStatus.UPLOADING,
			FileStatus.COMPLETE,
		];

		const handleStatusChange = sinon.stub().callsFake((event: BaseEvent) => {
			const file: UploaderFile = event.getData().file;
			assert.equal(file.getStatus(), statuses.shift(), 'Wrong Status');
		});

		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_STATUS_CHANGE]: handleStatusChange,
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					assert.equal(handleStatusChange.callCount, 5);
					done();
				},
			},
		});

		uploader.addFile(createFileByType('gif'));
	});

	it('should emit File:onStateChange', (done) => {
		const changes = {
			name: 'file new name',
			type: 'image/jpeg',
			size: 1010,
			serverFileId: 123,
			downloadUrl: '/dddd/',
			width: 150,
			height: 230,
			clientPreviewUrl: null,
			clientPreviewWidth: 50,
			clientPreviewHeight: 55,
			serverPreviewUrl: null,
			serverPreviewWidth: 1000,
			serverPreviewHeight: 1000,
			progress: 85,
		};

		const properties = Object.keys(changes);
		const handleStateChange = sinon.stub().callsFake((event: BaseEvent) => {
			const { property, value } = event.getData();
			assert.equal(property, properties.shift());
			assert.equal(value, changes[property]);
		});

		const uploader = new Uploader({
			serverOptions: {
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					uploader.subscribe(UploaderEvent.FILE_STATE_CHANGE, handleStateChange);

					const file: UploaderFile = event.getData().file;
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

					try
					{
						assert.equal(handleStateChange.callCount, Object.keys(changes).length);
						done();
					}
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFile(createFileByType('gif'));
	});
});
