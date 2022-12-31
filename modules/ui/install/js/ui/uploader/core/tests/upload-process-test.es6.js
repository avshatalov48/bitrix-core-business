import Uploader from '../src/uploader';
import AbstractUploadController from '../src/backend/abstract-upload-controller';
import Server from '../src/backend/server';
// import mock from 'xhr-mock';

describe('Upload Controller', () => {

	describe('Default Upload Controller', () => {

		//beforeEach(() => mock.setup());
		//afterEach(() => mock.teardown());

		it('should send chunks', () => {
			/*const uploader = new Uploader({
				serverOptions : {
					chunkSize: 7,
					forceChunkSize: true,
				},
				'File:onError': () => {
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

	describe('Custom Upload Controller', () => {

		class UploadController extends AbstractUploadController
		{
			constructor(server: Server)
			{
				super(server);
			}

			upload(file: File)
			{
				const chunkSize = this.getServer().getChunkSize();
				const fileSize = file.size;
				let chunkOffset = 0;
				let uploadedBytes = 0;

				const upload = () => {
					if (chunkOffset === 0 && fileSize <= chunkSize)
					{
						chunkOffset = fileSize;
						uploadedBytes = fileSize;
					}
					else
					{
						const currentChunkSize = Math.min(chunkSize, fileSize - chunkOffset);
						const nextOffset = chunkOffset + currentChunkSize;

						uploadedBytes += currentChunkSize;
						this.emit('onProgress', { progress: Math.ceil(uploadedBytes / fileSize * 100) });

						chunkOffset = nextOffset;
					}

					if (chunkOffset >= fileSize)
					{
						clearInterval(internal);
						this.emit('onUpload');
					}
				};

				const internal = setInterval(upload, 200);
			}

			abort(): void
			{

			}
		}

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
					uploadControllerClass: UploadController
				},
				events: {
					'File:onUploadProgress': handleProgress,
					'File:onError': (event) => {
						done(event.getData().error);
					},
					'File:onComplete': () => {
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
	});
});
