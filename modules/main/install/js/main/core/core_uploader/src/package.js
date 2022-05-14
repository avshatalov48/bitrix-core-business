import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import Options from './options';
import PackageFile from './package-file';
import {appendToForm, getFormDataSize, copyFormToForm, convertFormDataToObject} from './utils';

export default class Package extends EventEmitter {
	length: number = 0;
	filesVirgin: Set = new Set();
	filesInprogress: Set = new Set();
	files: Map = new Map();
	formData: FormData;
	#formDataFilesCount: number = 0;
	#formDataSize: number = 0;
	uploadInputName: string;
	makeAPackTimeout: number = 0;

	uploadStatus = Options.uploadStatus.ready;
	errors = [];
	response = {status: 'start'};

	constructor({id, formData, files, uploadFileUrl, uploadInputName})
	{
		super();
		this.setEventNamespace(Options.getEventNamespace());

		this.id = id;
		this.formData = formData;
		this.uploadFileUrl = uploadFileUrl;
		this.uploadInputName = uploadInputName;

		this.initFiles(files);

		console.log('2. Package is created with ', this.filesVirgin.size, ' files.');

		this.doneStreaming = this.doneStreaming.bind(this)
		this.progressStreaming = this.progressStreaming.bind(this)
	}

	getId()
	{
		return this.id;
	}

	initFiles(files)
	{
		files.forEach((fileItem: BX.UploaderFile) => {
			const uploadFile = new PackageFile(fileItem);
			this.filesVirgin.add(uploadFile.getId());
			this.files.set(uploadFile.getId(), uploadFile);
		});
	}

	prepare()
	{
		let [formSize, filesCount] = getFormDataSize(this.formData);
		console.log('2.1 Prepare form with files: ', filesCount, ' and formSize: ', parseInt(formSize), 'B');

		if (Options.getUploadLimits('phpMaxFileUploads') <= filesCount)
		{
			this.error('Too many files in your form. ');
			return false;
		}

		if ((Options.getUploadLimits('phpPostMaxSize') - formSize) < Options.getUploadLimits('phpPostMinSize'))
		{
			this.error('Too much data in your form. ');
			return false;
		}

		let packSize = 0;
		this.files.forEach((file) => {
			packSize += file.size;
		});

		if (Options.getMaxSize() !== null && Options.getMaxSize() < packSize)
		{
			this.error('There is not enough space on your server.');
			return false;
		}
		Options.decrementMaxSize(packSize);
		this.#formDataSize = formSize;
		this.#formDataFilesCount = filesCount;
		return true;
	}

	run(stream)
	{
		if (this.uploadStatus !== Options.uploadStatus.ready)
		{
			return;
		}

		console.log('4. Package is running with a stream: ', stream);
		this.uploadStatus = Options.uploadStatus.preparing;

		return this.startStreaming(stream);
	}

	bindStream(stream)
	{
		if (stream === this.stream)
		{
			return;
		}
		this.stream = stream;
		stream.subscribe('done', this.doneStreaming);
		stream.subscribe('progress', this.progressStreaming);
	}

	unbindStream(stream)
	{
		if (stream || this.stream)
		{
			(stream || this.stream).unsubscribe('done', this.doneStreaming);
			(stream || this.stream).unsubscribe('progress', this.progressStreaming);
			if (stream === this.stream)
			{
				delete this.stream;
			}
		}
	}

	makeAPack(formSize, filesCount, formData: FormData)
	{
		while (
			(formSize - Options.getUploadLimits('phpUploadMaxFilesize')) > 0
			&& filesCount > 0)
		{
			if (this.filesVirgin.size <= 0)
			{
				break;
			}

			const entry = this.filesVirgin.entries().next();

			if (entry.done === true)
			{
				break;
			}

			/*@var uploadItem: PackageFile */
			const [uploadItemId] = entry.value;
			const uploadItem = this.files.get(uploadItemId);
			if (!uploadItem.isReady())
			{
				return uploadItem.subscribeOnce('onReady', () => {
					this.makeAPack(formSize, filesCount, formData);
				});
			}

			const result = uploadItem.packFile();
			if (result.data)
			{
				const name = `${this.uploadInputName}[${uploadItem.getId()}]`;
				const tmpFormData = new FormData();
				appendToForm(tmpFormData, result.data, name);
				const [tmpFormSize, tmpFilesCount] = getFormDataSize(tmpFormData);
				copyFormToForm(tmpFormData, formData);
				formSize -= tmpFormSize;
				filesCount -= tmpFilesCount;
				this.filesInprogress.add(uploadItemId);
			}
			if (result.done === true)
			{
				this.filesVirgin.delete(uploadItemId);
			}
		}
		return this.emit('onPackIsReady', formData);
	}

	startStreaming(stream)
	{
		this.bindStream(stream);
		this.doStreaming(stream);
	}

	doStreaming(stream)
	{
		this.subscribeOnce('onPackIsReady', ({data}) => {
			console.log('onPackIsReady: ', data);
			console.groupEnd('Make a pack.');
			clearTimeout(this.makeAPackTimeout);
			this.makeAPackTimeout = 0;

			if (data instanceof FormData)
			{
				const firstValue = data.entries().next();
				if (firstValue.done === true && !firstValue.value)
				{
					return this.checkAndDone(stream);
				}
				copyFormToForm(this.formData, data);
				console.log('4.1. Start streaming');
				return stream.send(this.uploadFileUrl, data);
			}
			this.error('Package: error in packing');
		});

		const formSize = Math.min(
			Options.getUploadLimits('currentPostSize'),
			Options.getUploadLimits('phpPostMaxSize') - this.#formDataSize,
		);

		const filesCount = Options.getUploadLimits('phpMaxFileUploads') - this.#formDataFilesCount;
		const fromData = new FormData();
		console.group('Make a pack.');
		this.makeAPack(formSize, filesCount, fromData);
		this.makeAPackTimeout = setTimeout(() => {
			this.emit('onPackIsReady', null);
		}, Options.getUploadLimits('estimatedTimeForUploadFile') * 1000);
	}

	doneStreaming({target: stream, data: {status, data, errors}})
	{
		console.log('4.2. Done streaming');

		if (status === 'success')
		{
			this.parseResponse(data);
			if (this.errors.length <= 0)
			{
				this.doStreaming(stream);
			}
		}
		else
		{
			this.error(errors.join('. '));
		}
	}

	progressStreaming({data: percent})
	{
		this.filesInprogress.forEach((itemId) => {
			const item = this.files.get(itemId);
			const currentPercent = percent * (item.packPercent || 0);
			if (!item['previousPackPercent'])
			{
				item['previousPackPercent'] = currentPercent;
			}
			this.emit('fileIsInProgress',
				{
					itemId: itemId,
					item: item.item,
					percent: Math.ceil(Math.max(item['previousPackPercent'], currentPercent) / 100)
				});
			item['previousPackPercent'] = currentPercent;
		});
	}

	parseResponse(data)
	{
		const merge = function(ar1, ar2)
		{
			for (let jj in ar2)
			{
				if (ar2.hasOwnProperty(jj))
				{
					ar1[jj] = Type.isPlainObject(ar2[jj]) && Type.isPlainObject(ar1[jj])
						? merge(ar1[jj], ar2[jj]) : ar2[jj];
				}
			}
			return ar1;
		};
		this.response = merge(this.response, data);

		if (data.status === 'error')
		{
			this.error('Error in a uploading');
		}
		else if (!data['files'])
		{
			this.error('Unexpected server response.');
		}
		else
		{
			this.filesInprogress.forEach((itemId) => {
				const fileResponse = data['files'][itemId] || {status: 'error', errors: ['File data is not found']};
				if (fileResponse.status === 'error' || fileResponse.status === 'uploaded')
				{
					this.filesVirgin.delete(itemId);
					this.emit((fileResponse.status === 'error' ? 'fileIsErrored' : 'fileIsUploaded'),
						{
							itemId: itemId,
							item: this.files.get(itemId).item,
							response: fileResponse
						});
				}
				this.files.get(itemId).parseResponse(fileResponse);
			});
			this.filesInprogress.clear();
		}
	}

	checkAndDone(stream)
	{
		console.log('5. Form has been sent.');
		if (this.response['status'] === 'done')
		{
			this.done(stream);
		}
		else if (this.response['status'] === 'start')
		{
			this.error('Error with starting package.');
		}
		else if (this.response['status'] !== 'continue')
		{
			this.error('Unknown response');
		}
	}

	done(stream)
	{
		console.log('5.1 Release the stream');
		this.unbindStream(stream);
		this.emit('done', {
			status: this.errors.length <= 0 ? 'success' : 'failed'}
		);
	}

	error(errorText)
	{
		const handler = (itemId) => {
			this.emit('fileIsErrored',
				{
					itemId: itemId,
					item: this.files.get(itemId).item,
					response: {error: errorText, status: 'failed'},
					serverResponse: Object.assign({}, this.response)
				}
			);
		};
		this.filesVirgin.forEach(handler);
		this.filesVirgin.clear();
		this.filesInprogress.forEach(handler);
		this.filesInprogress.clear();

		this.errors.push(errorText);
		console.log('5. Form has been sent with errors: ', this.errors);
		this.done(this.stream);
	}

	get filesCount()
	{
		return (this.filesVirgin.size + this.filesInprogress.size);
	}

	get data()
	{
		return convertFormDataToObject(this.formData);
	}

	getServerResponse()
	{
		return this.response;
	}
}