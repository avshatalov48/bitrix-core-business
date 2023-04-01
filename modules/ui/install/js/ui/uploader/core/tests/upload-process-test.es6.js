import Uploader from '../src/uploader';
import createFileByType from './utils/create-file-by-type.es6';
import CustomUploadController from './utils/custom-upload-controller.es6';
import { UploaderEvent } from '../src/enums/uploader-event';
// import mock from 'xhr-mock';

describe('Upload Controller', () => {
	describe('Custom Upload Controller', () => {
		it('should upload a file', (done) => {
			const progressValues = [19, 37, 56, 74, 93, 100];
			const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
				const { progress } = event.getData();
				assert.equal(progress, progressValues.shift(), 'progress values');
			});

			const uploader = new Uploader({
				serverOptions : {
					chunkSize: 7,
					forceChunkSize: true,
					uploadControllerClass: CustomUploadController
				},
				events: {
					[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
					[UploaderEvent.FILE_ERROR]: (event) => {
						done(event.getData().error);
					},
					[UploaderEvent.FILE_COMPLETE]: () => {
						try
						{
							assert.equal(handleProgress.callCount, 6, 'onProgress Count');
							done();
						}
						catch (exception)
						{
							done(exception);
						}
					}
				}
			});

			const file = new File(['<html><body><b>Hello</b></body></html>'], 'index2.html', { type: 'text/html' });
			uploader.addFile(file);
		});

		it('should raise an error', (done) => {
			const progressValues = [12, 23, 34, 45, 56, 67, 78, 89, 100, 100];
			const handleProgress = sinon.stub().callsFake((event: BaseEvent) => {
				const { progress } = event.getData();
				assert.equal(progress, progressValues.shift(), 'progress values');
			});

			const uploader = new Uploader({
				serverOptions : {
					chunkSize: 50,
					forceChunkSize: true,
					uploadControllerClass: CustomUploadController,
					uploadControllerOptions: {
						raiseError: true,
						raiseErrorStep: 7
					},
				},
				events: {
					[UploaderEvent.FILE_UPLOAD_PROGRESS]: handleProgress,
					[UploaderEvent.FILE_ERROR]: (event) => {
						const { error } = event.getData();

						assert.equal(handleProgress.callCount, 7, 'onProgress Count');
						assert.equal(error.getCode(), 'CUSTOM_UPLOAD_ERROR');
						done();
					},
				}
			});

			uploader.addFile(createFileByType('gif'));
		});
	});

	describe('Default Upload Controller', () => {

		//beforeEach(() => mock.setup());
		//afterEach(() => mock.teardown());

		it('should send chunks', () => {
			/*const uploader = new Uploader({
				serverOptions : {
					chunkSize: 7,
					forceChunkSize: true,
				},
				[UploaderEvent.FILE_ERROR]: () => {
					console.log('error');
				},
			});*/

			/*		mock.setup();

					mock.post(/.*!/, (req, res) => {

						console.log('1');
						//expect(req.header('Content-Type')).toEqual('application/json');
						//expect(req.body()).toEqual('{"data":{"name":"John"}}');
						//return res.status(201).body('{"data":{"id":"abc-123"}}');
					});*/
			// Content-Length: 1048576
			// Content-Range: bytes 3145728-4194303/5489445
			// Content-Type: image/jpeg

			// /bitrix/services/main/ajax.php?filename=DSCF3028.JPG&folderId=8814&generateUniqueName=true&token=&action=disk.api.content.upload
			// {"status":"success","data":{"token":"baaa78d1b9c8204bb60547c296fa1045"},"errors":[]}

			// /bitrix/services/main/ajax.php?filename=DSCF3028.JPG&folderId=8814&generateUniqueName=true&contentId=baaa78d1b9c8204bb60547c296fa1045&action=disk.api.file.createByContent
			//

			/*const file = new File(['<html><body><b>Hello</b></body></html>'], 'index.html', { type: 'text/html' });
			uploader.addFile(file);
			setTimeout(() => {
				uploader.start();
			}, 100);*/

		});
	});
});
