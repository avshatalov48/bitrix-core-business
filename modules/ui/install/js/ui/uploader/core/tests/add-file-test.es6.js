import Uploader from '../src/uploader';
import { BaseEvent } from 'main.core.events';
import createFileByType from './utils/create-file-by-type.es6';
import { UploaderEvent } from '../src/enums/uploader-event';

describe('Add File Method', () => {

	it('should accept File', (done) => {

		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();

						assert.ifError(error);
						assert.equal(file.getName(), 'index.html');
						assert.equal(file.getType(), 'text/html');
						assert.equal(file.getSize(), 38);
						assert.equal(file.isImage(), false);

						done();
					}
					catch(exception)
					{
						done(exception);
					}
				},
			}
		});

		const file = new File(['<html><body><b>Hello</b></body></html>'], 'index.html', { type: 'text/html' });
		uploader.addFile(file);

	});

	it('should accept Blob', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();

						assert.ifError(error);
						assert.equal(file.getName(), 'hello.txt');
						assert.equal(file.getType(), 'text/plain');
						assert.equal(file.getSize(), 11);
						assert.equal(file.isImage(), false);

						done();
					}
					catch(exception)
					{
						done(exception);
					}
				},
			}
		});

		const blob = new Blob(['Hello World'], { type: 'text/plain' });
		uploader.addFile(blob, { name: 'hello.txt' });
	});

	it('should accept Blob without mimetype', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();

						assert.ifError(error);
						assert.equal(file.getName(), 'without-mimetype.png');
						assert.equal(file.getType(), '');
						assert.equal(file.getSize(), 11);
						assert.equal(file.isImage(), false);

						done();
					}
					catch(exception)
					{
						done(exception);
					}
				},
			}
		});

		const blob = new Blob(['Hello World']);
		uploader.addFile(blob, { name: 'without-mimetype.png' });
	});

	it('should accept Base64', (done) => {
		const uploader = new Uploader({
			autoUpload: false,
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();

						assert.ifError(error);
						assert.equal(file.getName(), 'my-image.png');
						assert.equal(file.getType(), 'image/png');
						assert.equal(file.getSize(), 109);

						done();
					}
					catch(exception)
					{
						done(exception);
					}
				},
			}
		});

		const base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAAAAADhOQgPAAAANElEQVQY0wXBQREAQQgDwfhXcI/44L2FBSrgZq5bFSClc4hPnJ8PQX8NYtweNF7Wo1pg6wd0TCizQnhtzAAAAABJRU5ErkJggg==';
		uploader.addFile(base64, { name: 'my-image.png' });

	});

	it('should return total files size', () => {
		const uploader = new Uploader({
			autoUpload: false,
			multiple: true,
		});

		assert.equal(uploader.getTotalSize(), 0);
		uploader.addFile(new Blob(['text'], { type: 'text/plain' }));
		assert.equal(uploader.getTotalSize(), 4);
		uploader.addFile(new Blob(['123'], { type: 'text/plain' }));
		assert.equal(uploader.getTotalSize(), 7);
	});

	it('should find file by id', function() {
		const uploader = new Uploader({
			autoUpload: false,
			multiple: true,
		});

		const id = 'my-file-id'
		uploader.addFile(
			new Blob(['text'], { type: 'text/plain' }),
			{ id: id, type: 'text/plain', name: 'say-my-name.txt' }
		);

		uploader.addFile(createFileByType('gif'));
		uploader.addFile(createFileByType('png'), { id: 'png' });

		const file = uploader.getFile(id);

		assert.equal(file, uploader.getFiles()[0]);
		assert.equal(file.getSize(), 4);
		assert.equal(file.getType(), 'text/plain');
		assert.equal(file.getName(), 'say-my-name.txt');

		const png = uploader.getFile('png');

		assert.equal(png, uploader.getFiles()[2]);
		assert.equal(png.getSize(), 1041);
		assert.equal(png.getType(), 'image/png');
		assert.equal(png.getName(), 'image.png');
	});

	/*it('should accept a pseudo file object', (done) => {
		const uploader = new Uploader({
			events: {
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					try
					{
						const { file, error } = event.getData();

						assert.ifError(error);
						assert.equal(file.getName(), 'my-image.png');
						assert.equal(file.getType(), 'image/png');
						assert.equal(file.getSize(), 109);

						done();
					}
					catch(exception)
					{
						done(exception);
					}
				},
			}
		});

		uploader.addFile(10, { name: 'my-file.txt', type: 'plain/text', size: 100 });

	});*/
});
