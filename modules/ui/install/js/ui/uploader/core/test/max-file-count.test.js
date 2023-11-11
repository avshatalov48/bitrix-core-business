import Uploader from '../src/uploader';
import { BaseEvent } from 'main.core.events';
import { BaseError } from 'main.core';
import { UploaderEvent } from '../src/enums/uploader-event';
import createFileByType from './utils/create-file-by-type.es6';

describe('Max Total Count Validation', () => {
	const png = createFileByType('png');
	const gif = createFileByType('gif');
	const jpg = createFileByType('jpg');
	const text = createFileByType('text');
	const csv = createFileByType('csv');
	const css = createFileByType('css');
	const json = createFileByType('json');

	it('should skip group of files', (done) => {
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
					catch (exception)
					{
						done(exception);
					}
				},
			},
		});

		uploader.addFiles([json, gif, png, jpg]);
	});

	it('should skip a single file', (done) => {
		const uploader = new Uploader({
			maxFileCount: 3,
			multiple: true,
		});

		const onAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			const { file } = event.getData();
			try
			{
				if (!['gif', 'csv', 'png'].includes(file.getExtension()))
				{
					assert.fail('Wrong files were added');
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		const onMaxFileCountExceeded = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				if (onMaxFileCountExceeded.callCount === 4 && onAddStart.callCount === 3)
				{
					done();
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD_START, onAddStart);
		uploader.subscribe(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, onMaxFileCountExceeded);

		uploader.addFiles([gif, csv, png]);
		uploader.addFiles([json]);
		uploader.addFiles([text]);
		uploader.addFile(css);
		uploader.addFile(jpg);
	});

	it('should allow and deny files dynamically', (done) => {
		const uploader = new Uploader({
			maxFileCount: 3,
			multiple: true,
		});

		const onAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			const { file } = event.getData();
			try
			{
				if (!['gif', 'csv', 'png', 'css'].includes(file.getExtension()))
				{
					assert.fail('Wrong files were added');
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		const onMaxFileCountExceeded = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				if (onMaxFileCountExceeded.callCount === 2 && onAddStart.callCount === 4)
				{
					done();
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD_START, onAddStart);
		uploader.subscribe(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, onMaxFileCountExceeded);

		uploader.addFiles([
			[gif, { id: 'gif' }],
			[csv, { id: 'csv' }],
			[png, { id: 'png' }],
		]);

		uploader.addFile(json);
		uploader.removeFile('gif');

		uploader.addFile(css, { id: 'css' });
		uploader.addFile(jpg);
	});

	it('should skip files in the single mode', (done) => {
		const uploader = new Uploader({
			multiple: false,
		});

		const onAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			const { file } = event.getData();
			try
			{
				if (file.getExtension() === 'json')
				{
					done();
				}
				else
				{
					assert.fail('Wrong files were added');
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD_START, onAddStart);
		uploader.addFiles([gif, csv, png]);
		uploader.addFile(json);
	});

	it('should replace file in the single mode', (done) => {
		const uploader = new Uploader({
			multiple: false,
			allowReplaceSingle: true,
		});

		const onAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				assert.equal(uploader.getFiles().length, 1);
				if (onAddStart.callCount === 3)
				{
					done();
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		const onMaxFileCountExceeded = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				assert.fail('Uploader emitted onMaxFileCountExceeded event.');
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD_START, onAddStart);
		uploader.subscribe(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, onMaxFileCountExceeded);

		uploader.addFile(gif);
		uploader.addFile(json);
		uploader.addFile(jpg);
	});

	it('should deny replace file in the single mode', (done) => {
		const uploader = new Uploader({
			multiple: false,
			allowReplaceSingle: false,
		});

		const onAddStart = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				assert.equal(uploader.getFiles().length, 1);
				assert.equal(onAddStart.callCount, 1);
			}
			catch (exception)
			{
				done(exception);
			}
		});

		const onMaxFileCountExceeded = sinon.stub().callsFake((event: BaseEvent) => {
			try
			{
				if (onMaxFileCountExceeded.callCount === 2)
				{
					done();
				}
			}
			catch (exception)
			{
				done(exception);
			}
		});

		uploader.subscribe(UploaderEvent.FILE_ADD_START, onAddStart);
		uploader.subscribe(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, onMaxFileCountExceeded);

		uploader.addFile(gif);
		uploader.addFile(json);
		uploader.addFile(jpg);
	});
});
