import Uploader from '../src/uploader';
import createFileByType from './utils/create-file-by-type.es6';
import CustomUploadController from './utils/custom-upload-controller.es6';
import { UploaderEvent } from '../src/enums/uploader-event';
import { UploaderStatus } from '../src/enums/uploader-status';
import type { BaseEvent } from 'main.core.events';
import { BaseError } from 'main.core';

describe('Uploader Events', () => {

	it('should emit onUploadComplete', (done) => {
		const uploader = new Uploader({
			serverOptions : {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: () => {
					assert.equal(uploader.getStatus(), UploaderStatus.STARTED);
					assert.equal(uploader.getUploadingFileCount(), 1);
				},
				[UploaderEvent.UPLOAD_COMPLETE]: (event) => {
					assert.equal(uploader.getUploadingFileCount(), 0);
					assert.equal(uploader.getStatus(), UploaderStatus.STOPPED);
					done();
				},
			}
		});

		assert.equal(uploader.getStatus(), UploaderStatus.STOPPED);
		uploader.addFile(createFileByType('gif'));
	});

	it('should emit onUploadStart', (done) => {
		const handleUploadStart = sinon.stub().callsFake((event: BaseEvent) => {
			assert.equal(uploader.getStatus(), UploaderStatus.STARTED);
			assert.equal(uploader.getPendingFileCount(), 1);
		});

		const uploader = new Uploader({
			autoUpload: false,
			serverOptions : {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.UPLOAD_START]: handleUploadStart,
				[UploaderEvent.UPLOAD_COMPLETE]: (event) => {
					assert.equal(handleUploadStart.callCount, 1);
					assert.equal(uploader.getUploadingFileCount(), 0);
					assert.equal(uploader.getStatus(), UploaderStatus.STOPPED);
					done();
				},
			}
		});

		assert.equal(handleUploadStart.callCount, 0);
		uploader.addFile(createFileByType('gif'));
		assert.equal(handleUploadStart.callCount, 0);

		setTimeout(() => {
			assert.equal(handleUploadStart.callCount, 0);
			uploader.start();
		}, 100);
	});

	it('should emit onError', (done) => {
		const uploader = new Uploader({
			maxFileCount: 1,
			multiple: true,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					assert.fail('A file was added to the uploader.');
				},
				[UploaderEvent.ERROR]: (event: BaseEvent) => {
					try
					{
						const { error } = event.getData();
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'MAX_FILE_COUNT_EXCEEDED');
						done();
					}
					catch(exception)
					{
						done(exception);
					}
				}
			}
		});

		uploader.addFiles([createFileByType('json'), createFileByType('gif')]);
	});

	it('should emit onMaxFileCountExceeded', (done) => {
		const uploader = new Uploader({
			maxFileCount: 3,
			multiple: true,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					assert.fail('A file was added to the uploader.');
				},
				[UploaderEvent.MAX_FILE_COUNT_EXCEEDED]: (event: BaseEvent) => {
					try
					{
						const { error } = event.getData();
						assert.ok(error instanceof BaseError, 'error is empty');
						assert.equal(error.getCode(), 'MAX_FILE_COUNT_EXCEEDED');
						done();
					}
					catch(exception)
					{
						done(exception);
					}
				}
			}
		});

		uploader.addFiles([
			createFileByType('json'),
			createFileByType('gif'),
			createFileByType('png'),
			createFileByType('jpg')
		]);
	});

	it('should emit onDestroy', (done) => {
		const uploader = new Uploader({
			serverOptions : {
				chunkSize: 50,
				forceChunkSize: true,
				uploadControllerClass: CustomUploadController,
			},
			events: {
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: () => {
					uploader.destroy();
				},
				[UploaderEvent.DESTROY]: (event) => {
					done();
				},
			}
		});

		uploader.addFiles([createFileByType('gif')]);
	});
});