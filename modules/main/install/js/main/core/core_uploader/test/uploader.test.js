import BX from '../../../core/test/old/core/internal/bootstrap';
import {Tag, Event} from 'main.core';
import {Uploader} from '../src/index';
import { BaseEvent, EventEmitter } from 'main.core.events';
import {UploaderQueue, UploaderUtils} from '../src/bootstrap';

const dataURLToBlob = function(dataURL) {
	var marker = ';base64,', parts, contentType, raw, rawLength;
	if(dataURL.indexOf(marker) === -1) {
		parts = dataURL.split(',');
		contentType = parts[0].split(':')[1];
		raw = parts[1];
		return new Blob([raw], {type: contentType});
	}

	parts = dataURL.split(marker);
	contentType = parts[0].split(':')[1];
	raw = window.atob(parts[1]);
	rawLength = raw.length;

	var uInt8Array = new Uint8Array(rawLength);

	for(var i = 0; i < rawLength; ++i) {
		uInt8Array[i] = raw.charCodeAt(i);
	}

	return new Blob([uInt8Array], {type: contentType});
};

describe('BX.Uploader', () => {
	const uploaderId = 'testUploader';
	const filesSource = [
		new File(
			[new Blob(["<html>bad because of type</html>"], {type: 'text/html'})],
			'bad.html'
		),
		new File(
			[new Blob(["hello, world"], {type: 'text/plain'})],
			'good.txt'
		),
		new File(
			[dataURLToBlob('data:image/png;base64,R0lGODlhDAAMAKIFAF5LAP/zxAAAANyuAP/gaP///wAAAAAAACH5BAEAAAUALAAAAAAMAAwAAAMlWLPcGjDKFYi9lxKBOaGcF35DhWHamZUW0K4mAbiwWtuf0uxFAgA7')],
			'good.png'
		),
		new File(
			[dataURLToBlob('data:image/gif;base64,R0lGODlhFAAUAKUgAEyV9Zq98S+I7Hl7gWen9TZKiigxb4+PknuHlHd4fUSs9mad83Sr9XV5qkNZmU+N1S49dKKduuHh4cng9HBwbd7j9Dt5s3yZ1fz4/H+h1So9gH59fVtjk3+Bg1p+s7W2xv///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAABkALAAAAAAUABQAAAarwIxwSCwaj8ikEklpOp+RjTRSREyu2Gukg+l2qMPDZLFgLAgMRgLT2XA7RDGZzCAQDGzJOz4B+AEXAQoQGBAbFGx8An4KDhwPGhh4hHBhEwICCpAGBR4WBxgHX0QdEwELFw4aDgUGrhwGlUMbEx8YDZwFGrqgomBCpREfuKuwkoWJQwkTFbquEK2FTclCtFlYBhUUE3vKCd/g34hS3UsHG11sv0tRU0vv8EZBADs=')],
			'bad.gif'
		),
	];
	let goodFilesCounter = 0;
	filesSource.forEach((file) => {
		if (file.name.indexOf('good') === 0)
		{
			goodFilesCounter++;
		}
	});
	console.log('goodFilesCounter: ', goodFilesCounter);
	let testFiles = null;
	const events = {
		onUploaderIsInited: ({compatData: [uploaderId, uploader]}) => {
			if (uploaderId === 'testUploader' && uploader instanceof Uploader)
			{
				delete events['onUploaderIsInited'];
			}
		},
		onAttachFiles: ({compatData: [files, nodes]}) => {
			testFiles = files;
			if (testFiles === filesSource)
			{
				console.log('Ura!')
			}
			delete events['onAttachFiles'];
		},
		onItemIsAdded: ({compatData: [file, node]}) => {
			if (file.name.indexOf('good') === 0)
			{
				goodFilesCounter--;
			}
			else
			{
				console.error('File should not be submited: ', file);
			}
			if (goodFilesCounter <= 0)
			{
				delete events['onItemIsAdded'];
			}
		}
	}
/*

	BX.onCustomEvent(this, "onItemIsAdded", [file, (nodes[index] || null), this]);
	BX.onCustomEvent(this, 'onStart', [null, {filesCount : 0}, this]);
	BX.onCustomEvent(this, 'onDone', [null, null, {filesCount : 0}]);
	BX.onCustomEvent(this, 'onFinish', [null, null, {filesCount : 0}]);


	BX.onCustomEvent(this, 'onPackageIsInitialized', [buffer1, this.queue.itForUpload]);
	BX.onCustomEvent(this, 'onFileIsUploaded', [itemId, item, response]);
	BX.onCustomEvent(item, 'onUploadDone', [item, response]);

	BX.onCustomEvent(this, 'onFileIsUploadedWithError', [itemId, item, response, packItem, packItem.getId()]);
	BX.onCustomEvent(item, 'onUploadError', [item, response, packItem, packItem.getId()]);

	BX.onCustomEvent(item, 'onUploadProgress', [item, percent, packItem, packItem.getId()]);
	BX.onCustomEvent(item, 'onUploadStart', [item, 0, packItem, packItem.getId()]);

*/	const input = Tag.render`<input type="file" name="" accept="image/png">`;
	const form = Tag.render`<form action="">${input}</form>`;
	const agent = new Uploader({
		id: uploaderId,
		input: input,
		uploadFileUrl: 'someString',
		dropZone: null,
		placeHolder: null,
		events: undefined,

		uploadMaxFilesize: 1024,
		uploadFileWidth: 10,
		uploadFileHeight: 10,
		allowUpload: 'I',
		allowUploadExt: '.jpg png txt',
		allowUploadAccept: '',
	});
	it('Should apply limits', () => {
		assert.equal(
			agent.uploadLimits['allowUploadAccept'],
			'image/png, image/*, .jpg, .png, .txt');
	});
	const files = [
		new File(
			new Blob(["<html>bad because of type</html>"], {type: 'text/html'}),
			'bad.html'
		),
		new File(
			new Blob(["hello, world"], {type: 'text/plain'}),
			'good.txt'
		),
		new File(
			dataURLToBlob('data:image/png;base64,R0lGODlhDAAMAKIFAF5LAP/zxAAAANyuAP/gaP///wAAAAAAACH5BAEAAAUALAAAAAAMAAwAAAMlWLPcGjDKFYi9lxKBOaGcF35DhWHamZUW0K4mAbiwWtuf0uxFAgA7'),
			'good.png'
		),
		new File(
			dataURLToBlob('data:image/gif;base64,R0lGODlhFAAUAKUgAEyV9Zq98S+I7Hl7gWen9TZKiigxb4+PknuHlHd4fUSs9mad83Sr9XV5qkNZmU+N1S49dKKduuHh4cng9HBwbd7j9Dt5s3yZ1fz4/H+h1So9gH59fVtjk3+Bg1p+s7W2xv///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAABkALAAAAAAUABQAAAarwIxwSCwaj8ikEklpOp+RjTRSREyu2Gukg+l2qMPDZLFgLAgMRgLT2XA7RDGZzCAQDGzJOz4B+AEXAQoQGBAbFGx8An4KDhwPGhh4hHBhEwICCpAGBR4WBxgHX0QdEwELFw4aDgUGrhwGlUMbEx8YDZwFGrqgomBCpREfuKuwkoWJQwkTFbquEK2FTclCtFlYBhUUE3vKCd/g34hS3UsHG11sv0tRU0vv8EZBADs='),
			'bad.gif'
		),
	];
	agent.onAttach(files);

	// check 504 Error
	// check differed uploading
});