import {Tag, Event} from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

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
	let goodFilesCountFromEvent = 0;
	filesSource.forEach((file) => {
		if (file.name.indexOf('good') === 0) {
			goodFilesCounter++;
		}
	});
	let testFiles = null;
	const events = {
		onUploaderIsInited: ({compatData: [uploaderId, uploader]}) => {
			if (uploaderId === 'testUploader' && uploader instanceof BX.Uploader) {
				delete events['onUploaderIsInited'];
			}
		},
		onAttachFiles: ({compatData: [files, nodes]}) => {
			testFiles = files;
			if (testFiles === filesSource) {
				console.log('Ura!')
			}
			delete events['onAttachFiles'];
		},
		onItemIsAdded: ({compatData: [file, node]}) => {
			if (file.name.indexOf('good') === 0)
			{
				goodFilesCountFromEvent++;
			}
			if (goodFilesCountFromEvent === goodFilesCounter)
			{
				delete events['onItemIsAdded'];
			}
		},
		onPackageIsInitialized: ({
			compatData: [somePostData, filesQueue],
			data: {formData: formData, data: data, files: files}}) => {
			it('Must be fired "onPackageIsInitialized" with special compatibility data', () => {
				assert.equal(!!somePostData.post, true);
				assert.equal(!!somePostData.post.data, true);
				assert.equal(somePostData.post.filesCount, goodFilesCounter);
				assert.equal(!!filesQueue, true);
			});
			it('Must be fired "onPackageIsInitialized" with new events', () => {
				assert.equal(!!formData, true);
				assert.equal(!!data, true);
				assert.equal(!!files, true);
			});
			delete events['onPackageIsInitialized'];
		},
		onStart: ({
					compatData: [packageId, somePostData, packItem],
					data: {package: packItemForNewEvents}}) => {
			it('Must be fired "onStart" with special compatibility data', () => {
				assert.equal(packageId, packItem.getId());
				assert.equal(!!somePostData.post, true);
				assert.equal(!!somePostData.post.data, true);
				assert.equal(somePostData.post.filesCount, goodFilesCounter);
			});
			it('Must be fired "onStart" with new events', () => {
				assert.equal(packageId, packItemForNewEvents.getId());
			});
			delete events['onStart'];
		},
		onFinish: ({
			compatData: [usedToBeStreams, packageId, packItem, response],
			data: {package: packItemForNewEvents, response: responseForNewEvents}
		}) => {
			it('Must be fired "onFinish" with special compatibility data', () => {
				assert.equal(packageId, packItem.getId());
			});
			it('Must be fired "onFinish" with new events', () => {
				assert.equal(packageId, packItemForNewEvents.getId());
			});
			delete events['onFinish'];
			//Todo make a differed test
		}
	}
	EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, 'onUploaderIsInited', events.onUploaderIsInited);

	const input = Tag.render`<input type="file" name="" accept="image/png">`;
	const form = Tag.render`<form action="">${input}</form>`;
	const agent = new BX.Uploader({
		id: uploaderId,
		input: input,
		uploadFileUrl: 'uploader.php',
		dropZone: null,
		placeHolder: null,
		events: events,

		uploadMaxFilesize: 1024,
		uploadFileWidth: 10,
		uploadFileHeight: 10,
		allowUpload: 'I',
		allowUploadExt: '.jpg png txt'
	});

	it('Should apply limits', () => {
		assert.equal(
			agent.limits['uploadFile'],
			'image/png, image/*, .jpg, .png, .txt');
	});
	it('Must be fired "onUploaderIsInited"', () => {
		assert.equal(events['onUploaderIsInited'], undefined);
	});

	agent.onAttach(filesSource);

	it('Must be fired "onAttachFiles"', () => {
		assert.equal(events['onAttachFiles'], undefined);
	});

	const lengthFiles = agent.length;
	it(`Must be fired "onItemIsAdded" for ${goodFilesCounter} times`, () => {
		assert.equal(lengthFiles, goodFilesCounter);
	});

	agent.submit();
});