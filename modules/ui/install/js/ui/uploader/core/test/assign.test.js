import Uploader from '../src/uploader';
import { fireEvent } from '@testing-library/dom';
import { UploaderEvent } from '../src/enums/uploader-event';

describe('Assigning', () => {
	describe('assignBrowse', () => {
		let uploader: Uploader = null;
		let file = null;

		beforeEach(() => {
			uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				multiple: true,
				acceptedFileTypes: ['image/*', '.zip'],
			});

			file = new File(['<html><body><b>Hello</b></body></html>'], 'index.html', { type: 'text/html' });
		});

		it('should set tag attributes', () => {
			const input = document.createElement('input');
			input.type = 'file';

			assert.equal(input.multiple, false);
			assert.equal(input.getAttribute('accept'), null);

			uploader.assignBrowse(input);

			assert.equal(input.multiple, true);
			assert.equal(input.getAttribute('accept'), 'image/*,.zip');
		});

		it('assign browse to input', () => {
			const input = document.createElement('input');
			input.type = 'file';
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignBrowse(input);
			assert.equal(addFiles.callCount, 0);
			assert.equal(uploader.getFiles().length, 0);

			fireEvent.change(input, { target: { files: [file] } });

			assert.equal(input.files.length, 1);
			assert.equal(uploader.getFiles().length, 1);
			assert.equal(input.files[0], file);
			assert.equal(addFiles.callCount, 1);
		});

		it('should unassign browse', () => {
			const input = document.createElement('input');
			input.type = 'file';
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignBrowse(input);
			assert.equal(addFiles.callCount, 0);
			assert.equal(uploader.getFiles().length, 0);
			uploader.unassignBrowse(input);

			fireEvent.change(input, { target: { files: [file] } });

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);
		});

		it('unassign browse to input', () => {
			const input = document.createElement('input');
			input.type = 'file';

			const input2 = document.createElement('input');
			input2.type = 'file';

			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignBrowse([input, input2]);
			assert.equal(addFiles.callCount, 0);
			assert.equal(uploader.getFiles().length, 0);

			fireEvent.change(input, { target: { files: [file] } });
			fireEvent.change(input2, { target: { files: [file] } });
			assert.equal(uploader.getFiles().length, 2);
			assert.equal(addFiles.callCount, 2);

			fireEvent.change(input2, { target: { files: [file] } });
			assert.equal(uploader.getFiles().length, 3);
			assert.equal(addFiles.callCount, 3);

			fireEvent.change(input, { target: { files: [file] } });
			assert.equal(uploader.getFiles().length, 4);
			assert.equal(addFiles.callCount, 4);

			uploader.unassignBrowseAll();
			fireEvent.change(input, { target: { files: [file] } });
			fireEvent.change(input2, { target: { files: [file] } });
			assert.equal(uploader.getFiles().length, 4);
			assert.equal(addFiles.callCount, 4);
		});

		it('assign browse to div', (done) => {
			const div = document.createElement('div');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignBrowse(div);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			uploader.subscribe(UploaderEvent.BEFORE_BROWSE, (event) => {
				assert.equal(uploader.getFiles().length, 0);
				assert.equal(addFiles.callCount, 0);

				const { input } = event.getData();
				fireEvent.change(input, { target: { files: [file] } });

				assert.equal(uploader.getFiles().length, 1);
				assert.equal(addFiles.callCount, 1);

				done();
			});

			const event = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
			div.dispatchEvent(event);
		});

		it('unassign browse to div', (done) => {
			const div = document.createElement('div');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignBrowse(div);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			const timer = setInterval(() => {
				if (callCount === 2)
				{
					clearInterval(timer);
					done();
				}
			}, 50);

			let callCount = 0;
			uploader.subscribe(UploaderEvent.BEFORE_BROWSE, (event) => {
				callCount++;
				if (callCount === 2)
				{
					uploader.unassignBrowse(div);
				}

				const event2 = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
				div.dispatchEvent(event2);
			});

			const event = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
			div.dispatchEvent(event);
		});
	});

	describe('assignDropzone', () => {
		let uploader: Uploader = null;
		let file = null;

		beforeEach(() => {
			uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				multiple: true,
			});

			file = new File(['<html><body><b>Hello</b></body></html>'], 'index.html', { type: 'text/html' });
		});

		it('should assign dropzone to div', (done) => {
			const div = document.createElement('div');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignDropzone(div);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				assert.equal(uploader.getFiles().length, 1);
				assert.equal(uploader.getFiles()[0].getBinary(), file);
				assert.equal(addFiles.callCount, 1);

				done();
			});

			fireEvent.drop(div, { dataTransfer: { files: [file] } });
		});

		it('should unassign dropzone', (done) => {
			const div = document.createElement('div');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignDropzone(div);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			const timer = setInterval(() => {
				if (callCount === 2)
				{
					clearInterval(timer);
					done();
				}
			}, 150);

			let callCount = 0;
			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				callCount++;
				if (callCount === 2)
				{
					uploader.unassignDropzone(div);
				}
				else
				{
					assert.equal(uploader.getFiles().length, 1);
					assert.equal(uploader.getFiles()[0].getBinary(), file);
					assert.equal(addFiles.callCount, 1);
				}

				fireEvent.drop(div, { dataTransfer: { files: [file] } });
			});

			fireEvent.drop(div, { dataTransfer: { files: [file] } });
		});

		it('should unassign all dropzones', (done) => {
			const div = document.createElement('div');
			const div2 = document.createElement('div');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignDropzone([div, div2]);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			const timer = setInterval(() => {
				if (callCount === 4)
				{
					clearInterval(timer);
					done();
				}
			}, 100);

			let callCount = 0;
			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				callCount++;
				if (callCount === 4)
				{
					uploader.unassignDropzoneAll();
				}
				else if (callCount > 4)
				{
					clearInterval(timer);
					done(new Error('Call count exceeded.'));
				}

				fireEvent.drop(div2, { dataTransfer: { files: [file] } });
			});

			fireEvent.drop(div, { dataTransfer: { files: [file] } });
		});
	});

	function createClipboardEvent(files)
	{
		return {
			clipboardData: {
				files: [...files],
				types: ['Files'],
				items: [],
			},
			preventDefault: () => {},
		};
	}

	describe('assignPaste', () => {
		let uploader: Uploader = null;
		let file = null;
		beforeEach(() => {
			uploader = new Uploader({
				autoUpload: false,
				maxFileSize: 1024,
				multiple: true,
			});

			file = new File(['<html><body><b>Hello</b></body></html>'], 'index.html', { type: 'text/html' });
		});

		it('assign paste to textarea', (done) => {
			const textarea = document.createElement('textarea');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignPaste(textarea);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				assert.equal(uploader.getFiles().length, 1);
				assert.equal(uploader.getFiles()[0].getBinary(), file);
				assert.equal(addFiles.callCount, 1);

				done();
			});

			const clipboardEvent = createClipboardEvent([file]);

			fireEvent.paste(textarea, clipboardEvent);
		});

		it('should unassign paste', (done) => {
			const textarea = document.createElement('textarea');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignPaste(textarea);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			const timer = setInterval(() => {
				if (callCount === 2)
				{
					clearInterval(timer);
					done();
				}
			}, 150);

			let callCount = 0;
			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				callCount++;
				if (callCount === 2)
				{
					uploader.unassignPaste(textarea);
				}
				else
				{
					assert.equal(uploader.getFiles().length, 1);
					assert.equal(uploader.getFiles()[0].getBinary(), file);
					assert.equal(addFiles.callCount, 1);
				}

				fireEvent.paste(textarea, createClipboardEvent([file]));
			});

			fireEvent.paste(textarea, createClipboardEvent([file]));
		});

		it('should unassign paste all', (done) => {
			const textarea = document.createElement('textarea');
			const textarea2 = document.createElement('textarea');
			const addFiles = sinon.spy(uploader, 'addFiles');

			uploader.assignPaste([textarea, textarea2]);

			assert.equal(uploader.getFiles().length, 0);
			assert.equal(addFiles.callCount, 0);

			const timer = setInterval(() => {
				if (callCount === 4)
				{
					clearInterval(timer);
					done();
				}
			}, 100);

			let callCount = 0;
			uploader.subscribe(UploaderEvent.FILE_ADD, (event) => {
				callCount++;
				if (callCount === 4)
				{
					uploader.unassignPasteAll();
				}
				else if (callCount > 4)
				{
					clearInterval(timer);
					done(new Error('Call count exceeded.'));
				}

				fireEvent.paste(textarea2, createClipboardEvent([file]));
			});

			fireEvent.paste(textarea, createClipboardEvent([file]));
		});
	});
});
