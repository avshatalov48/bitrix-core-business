import {Type, Runtime, Loc, Dom, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import type {UploaderType, UploadQueueType} from "./types";
import DropZone from './dropzone';
import Streams from './streams';
import Options from './options';
import Package from './package';
import {appendToForm, convertFormDataToObject} from './utils';
import {UploaderQueue, UploaderUtils} from './bootstrap';
import PackageFile from "./package-file";

export default class Uploader {
	static repo = new Map();
	static justCounter = 0;

	fileInput: ?Element = null;
	form: ?Element = null;

	id: string;
	controlId: string;
	CID: string;

	uploadFileUrl: string;
	limits = {};

	packages: Map = new Map();

	constructor(params: UploaderType)
	{
		let {input, uploadFileUrl, id, CID, controlId, dropZone, placeHolder, events} = params;

		if (Type.isStringFilled(uploadFileUrl))
		{
			this.uploadFileUrl = uploadFileUrl;
		}
		input = Type.isStringFilled(input) ? document.getElementById(input): input;
		if (Type.isDomNode(input))
		{
			this.fileInput = input;
			this.form = input.form;
			this.uploadFileUrl = (this.uploadFileUrl || this.form.getAttribute('action'));
		}
		else if (input !== null)
		{
			Runtime.debug(Loc.getMessage('UPLOADER_INPUT_IS_NOT_DEFINED'));
			return;
		}
		if (!this.uploadFileUrl)
		{
			Runtime.debug(Loc.getMessage('UPLOADER_ACTION_URL_NOT_DEFINED'))
			return;
		}

		this.constructor.justCounter++;
		const uniqueId = UploaderUtils.getId();
		this.id = Type.isStringFilled(id) ? id: ['bitrixUploaderID',  uniqueId].join('');
		this.CID = Type.isStringFilled(CID) ? CID: ('CID' + uniqueId); // this is a security id
		this.controlId = (controlId || 'bitrixUploader'); // this is a control id can be lice control name

		this.onChange = this.onChange.bind(this);

		this.setLimits(params);
		this.initParams(params);
		this.init(this.fileInput);
		this.dropZone = this.initDropZone(dropZone);
		this.bindUserEvents(events);
		this.initFilesQueue(params);
		BX.onCustomEvent(window, 'onUploaderIsInited', [this.id, this]);

		Uploader.repo.set(this.id, this);
	}

	setLimits({uploadMaxFilesize, uploadFileWidth, uploadFileHeight, allowUpload, allowUploadExt})
	{
		this.limits = {
			uploadMaxFilesize: uploadMaxFilesize || 0,
			uploadFileWidth: uploadFileWidth || 0,
			uploadFileHeight: uploadFileHeight || 0,
			uploadFileExt: '',
			uploadFile: this.fileInput ? this.fileInput.getAttribute('accept') : '',
			allowUpload: allowUpload, //compatibility
			allowUploadExt: allowUploadExt //compatibility
		};
		const acceptAttribute = [];
		if (Type.isStringFilled(this.limits['uploadFile']))
		{
			acceptAttribute.push(this.limits['uploadFile']);
		}
		if (allowUpload === 'I')
		{
			acceptAttribute.push('image/*')
		}

		if (Type.isStringFilled(allowUploadExt))
		{
			const separator = allowUploadExt.indexOf(',') >= 0 ? ',' : ' ';
			const extensions = [];
			allowUploadExt.split(separator).forEach((extension) => {
				extensions.push(extension.trim().replace('.', ''));
				acceptAttribute.push('.' + extension.trim().replace('.', ''));
			});
			if (extensions)
			{
				this.limits["uploadFileExt"] = extensions;
			}
		}
		this.limits['uploadFile'] = acceptAttribute.join(', ');
	}

	initParams({uploadMethod, uploadFormData, filesInputMultiple,
		           uploadInputName, uploadInputInfoName,
		           deleteFileOnServer, pasteFileHashInForm})
	{
		// Limits
		this.params = {
			filesInputMultiple: (this.fileInput && this.fileInput["multiple"] || filesInputMultiple ? "multiple" : false),
			uploadFormData: (uploadFormData === "N" ? "N" : "Y"),
			uploadMethod: (uploadMethod === "immediate" ? "immediate" : "deferred"),
			uploadInputName: Type.isStringFilled(uploadInputName) ? uploadInputName : 'bxu_files',
			uploadInputInfoName: Type.isStringFilled(uploadInputInfoName) ? uploadInputInfoName : 'bxu_info',
			deleteFileOnServer: !(deleteFileOnServer === false || deleteFileOnServer === "N"),
			//to insert hash into the form
			filesInputName: (this.fileInput && this.fileInput["name"] ? this.fileInput["name"] : "FILES"),
			pasteFileHashInForm: !(pasteFileHashInForm === false || pasteFileHashInForm === "N")
		};
	}

	init(fileInput)
	{
		if (fileInput === null)
		{
			return true;
		}
		if (Type.isDomNode(fileInput))
		{
			const newFileInput = this.makeFileInput(fileInput);
			if (fileInput === this.fileInput)
			{
				this.fileInput = newFileInput;
			}

			if (newFileInput)
			{
				return true;
			}
		}
		return false;
	}

	initDropZone(dropZoneNode: ?Element): Object
	{
		const dropZone = new DropZone(dropZoneNode);
		EventEmitter.subscribe(dropZone, Options.getEventName('caught'), ({data}) => {
			this.onChange(data);
		});
		EventEmitter.subscribe(this, Options.getEventName('destroy'), () => {
			EventEmitter.unsubscribeAll(dropZone, Options.getEventName('caught'));
			dropZone.destroy();
		});
		return dropZone;
	}

	initFilesQueue({fields, copies, placeHolder, showImage, sortItems, thumb, queueFields})
	{
		const params = {
			fields: queueFields && queueFields['fields'] ? queueFields['fields'] : fields,
			copies: queueFields && queueFields['copies'] ? queueFields['copies'] : copies,
			placeHolder: queueFields && queueFields['placeHolder'] ? queueFields['placeHolder'] : placeHolder,
			showImage: queueFields && queueFields['showImage'] ? queueFields['showImage'] : showImage,
			sortItems: queueFields && queueFields['sortItems'] ? queueFields['sortItems'] : sortItems,
			thumb: queueFields && queueFields['thumb'] ? queueFields['thumb'] : thumb,
		}

		this.queue = new UploaderQueue(params, this.limits, this);
	}

	bindUserEvents(events:?Object)
	{
		if (!Type.isPlainObject(events))
		{
			return;
		}

		for (let eventName in events)
		{
			if (events.hasOwnProperty(eventName))
			{
				EventEmitter.subscribe(this, eventName, events[eventName]);
			}
		}
	}

	makeFileInput(oldFileInput)
	{
		if (!Type.isDomNode(oldFileInput))
		{
			return false;
		}
		Event.unbindAll(oldFileInput, 'change');

		const newFileInput = oldFileInput.cloneNode(true);
		newFileInput.value = '';
		newFileInput.setAttribute('name', (this.params["uploadInputName"] + '[]'));
		newFileInput.setAttribute('multiple', this.params["filesInputMultiple"]);
		newFileInput.setAttribute('accept', this.limits["uploadFile"]);
		oldFileInput.parentNode.replaceChild(newFileInput, oldFileInput);

		BX.onCustomEvent(this, "onFileinputIsReinited", [newFileInput, this]);

		Event.bind(newFileInput, "change", this.onChange);

		return newFileInput;
	}

	onChange(event)
	{
		if (!event)
		{
			return;
		}

		if (event['preventDefault'])
		{
			event.preventDefault();
		}
		if (event['stopPropagation'])
		{
			event.stopPropagation();
		}
		let files = []
		if (Type.isArray(event))
		{
			files = event;
		}
		else if (Type.isObject(event))
		{
			if (event['target'])
			{
				let fileInput = event['target'];
				files = fileInput.files;
				if (!fileInput || fileInput.disabled)
				{
					return false;
				}
				BX.onCustomEvent(this, "onFileinputIsChanged", [fileInput, this]);
				this.init(fileInput);
			}
			else if (event['files'])
			{
				files = event['files'];
			}
		}

		this.onAttach(files);
		return false;
	}

	onAttach(files, nodes, check: boolean)
	{
		if (!files || !files['length'])
		{
			return false;
		}

		check = (check !== false);
		files = [...files];
		nodes = nodes && Type.isArray(nodes) ? [...nodes] : [];

		BX.onCustomEvent(this, "onAttachFiles", [files, nodes, this]);

		let added = false;

		[...files].forEach((file, index) => {

			let ext = '';
			let type = (file['type'] || '').toLowerCase();

			if (Type.isDomNode(file) && file.value)
			{
				ext = (file.value.name || '').split('.').pop();
			}
			else
			{
				ext = (file['name'] || file['tmp_url'] || '').split('.').pop();
				if (ext.indexOf('?') > 0)
				{
					ext = ext.substr(0, ext.indexOf('?'));
				}
			}
			ext = ext.toLowerCase();

			if (check)
			{
				const errors = [];
				if (
					this.limits['uploadFile'].indexOf('image/') >= 0
					&&
					(
						type.indexOf('image/') < 0
						&&
						Options.getImageExtensions().indexOf(ext) < 0
					)
				)
				{
					errors.push('File type is not an image like.');
				}
				if (
					this.limits['uploadFileExt'].length > 0
				)
				{
					if (this.limits['uploadFileExt'].indexOf(ext) < 0)
					{
						errors.push(`File extension ${ext} is in ${this.limits['uploadFileExt']}`);
					}
					else
					{
						errors.pop();
					}
				}
				if (
					this.limits['uploadMaxFilesize'] > 0
					&&
					file.size > this.limits['uploadMaxFilesize']
				)
				{
					errors.push(`File size ${file.size} is bigger than ${this.limits['uploadMaxFilesize']}`);
				}
				if (errors.length > 0)
				{
					return;
				}
			}
			if (String['normalize'])
			{
				file.name = String(file.name).normalize();
			}

			BX.onCustomEvent(this, "onItemIsAdded", [file, (nodes[index] || null), this]);
			added = true;
		});
		if (added && this.params["uploadMethod"] === "immediate")
		{
			this.submit();
		}
		return false;
	}

	getFormData(): FormData
	{
		let formData = new FormData(this.params["uploadFormData"] === "Y" && this.form ? this.form : undefined);
		let entries = formData.entries();
		let entry;
		while((entry = entries.next()) && entry.done === false)
		{
			const [name] = entry.value;

			if (name.indexOf(this.params["filesInputName"]) === 0
				|| name.indexOf(this.params["uploadInputInfoName"]) === 0
				|| name.indexOf(this.params["uploadInputName"]) === 0
			)
			{
				formData.delete(name);
			}
		}

		formData.append('AJAX_POST', 'Y');
		formData.append('USER_ID', Loc.getMessage('USER_ID'));
		formData.append('sessid', BX.bitrix_sessid());
		if (BX.message.SITE_ID)
		{
			formData.append('SITE_ID', BX.message.SITE_ID);
		}
		formData.append(this.params["uploadInputInfoName"] + '[controlId]', this.controlId);
		formData.append(this.params["uploadInputInfoName"] + '[CID]', this.CID);
		formData.append(this.params["uploadInputInfoName"] + '[uploadInputName]', this.params["uploadInputName"]);
		formData.append(this.params["uploadInputInfoName"] + '[version]', Options.getVersion());
		return formData;
	}

	submit()
	{
		//region Compatibility
		if (this.queue.itForUpload.length <= 0)
		{
			BX.onCustomEvent(this, 'onStart', [null, {filesCount : 0}, this]);
			BX.onCustomEvent(this, 'onDone', [null, null, {filesCount : 0}]);
			BX.onCustomEvent(this, 'onFinish', [null, null, {filesCount : 0}]);
			return;
		}
		//endregion

		const files = Object.values(this.queue.itForUpload.items);
		const formData = this.getFormData();

		//region Here we can change formData
		const changedData = {};
		const buffer1 = {
			post: {data: changedData, size: 0, filesCount: files.length}, //compatibility field
			filesCount: files.length,
		};
		const eventOnPackageIsInitialized = new BaseEvent();
		eventOnPackageIsInitialized.setCompatData([buffer1, this.queue.itForUpload]);
		eventOnPackageIsInitialized.setData({
			formData: formData,
			data: changedData,
			files: files
		});

		EventEmitter.emit(this, 'onPackageIsInitialized', eventOnPackageIsInitialized);
		appendToForm(formData, buffer1.post.data);
		if (buffer1.post.data !== changedData)
		{
			appendToForm(formData, changedData);
		}
		//endregion

		const packageId = 'pIndex' + (new Date().valueOf() + Math.round(Math.random() * 1000000));

		formData.append(this.params["uploadInputInfoName"] + '[packageIndex]', packageId);
		formData.append(this.params["uploadInputInfoName"] + '[mode]', 'upload');
		formData.append(this.params["uploadInputInfoName"] + '[filesCount]', files.length);
		if (this.packages.size <= 0)
		{
			console.group('Upload');
		}
		console.log('1. Create a new Package');

		const packItem = new Package({
			id: packageId,
			formData: formData,
			files: files,
			uploadFileUrl : this.uploadFileUrl,
			uploadInputName: this.params["uploadInputName"],
		});

		this.queue.itForUpload = new UploaderUtils.Hash();

		const eventOnStart = new BaseEvent();
		eventOnStart.setCompatData([packageId, Object.assign(
			{post: {data: packItem.data, filesCount: files.length}},
			packItem), this]);
		eventOnStart.setData({package: packItem});
		EventEmitter.emit(this, 'onStart', eventOnStart);

		this.packages.set(packItem.getId(), packItem);
		EventEmitter.emit(this, 'onBusy');
		packItem.subscribeOnce('done', ({target: p, data: {status}}) => {
			const evDone = new BaseEvent();
			evDone.setCompatData([{}, packageId, packItem, packItem.getServerResponse()]);
			evDone.setData({package: packItem, response: packItem.getServerResponse()});
			EventEmitter.emit(this, 'onDone', evDone);
			// region Compatibility
			if (status === 'failed')
			{
				EventEmitter.emit(this, 'onError', new BaseEvent({compatData: [{}, packageId, packItem.getServerResponse()]}));
			}
			// endregion Compatibility
			this.packages.delete(p.getId());
			if (this.packages.size <= 0)
			{
				setTimeout(() => {
					const ev = new BaseEvent();
					ev.setCompatData([{}, packageId, packItem, packItem.getServerResponse()]);
					ev.setData({package: packItem, response: packItem.getServerResponse()});
					EventEmitter.emit(this, 'onFinish', ev);
					console.groupEnd('Upload');
				});
			}
		});
		packItem.subscribe('fileIsUploaded', ({data: {itemId, item, response}}) => {
			this.queue.itUploaded.setItem(itemId, item);
			BX.onCustomEvent(this, 'onFileIsUploaded', [itemId, item, response]);
			BX.onCustomEvent(item, 'onUploadDone', [item, response, this, packItem.getId()]);
		});
		packItem.subscribe('fileIsErrored', ({data: {itemId, item, response}}) => {
			this.queue.itFailed.setItem(itemId, item);
			BX.onCustomEvent(this, 'onFileIsUploadedWithError', [itemId, item, response, this, packItem.getId()]);
			BX.onCustomEvent(item, 'onUploadError', [item, response, this, packItem.getId()]);
		});
		packItem.subscribe('fileIsInProgress', ({data: {item, percent}}) => {
			BX.onCustomEvent(item, 'onUploadProgress', [item, percent, this, packItem.getId()]);
		});

		if (packItem.prepare())
		{
			files.forEach((item: BX.UploaderFile) => {
				BX.onCustomEvent(item, 'onUploadStart', [item, 0, this, packItem.getId()]);
			});

			Streams.addPackage(packItem);
		}
	}

	log(text)
	{

	}

	destruct()
	{
		EventEmitter.emit(this, Options.getEventName('destroy'));
		delete this.dropZone;
	}
/*region Compatbility */
	get controlID()
	{
		return this.controlId;
	}

	get dialogName()
	{
		return "BX.Uploader";
	}

	get length(): number
	{
		return this.queue.itForUpload.length;
	}

	get streams()
	{
		if (!this['#_streams'])
		{
			this['#_streams'] = {
				packages: {
					getItem: (id) => {
						return this.packages.get(id);
					}
				}
			};
		}
		return this['#_streams'];
	}
/*endregion*/

	getItem(id)
	{
		return this.queue.getItem(id);
	}

	getItems()
	{
		return this.queue.items;
	}

	restoreItems()
	{
		//Todo check it
		this.queue.restoreFiles.apply(this.queue, arguments);
	}

	clear()
	{
		var item;
		while((item = this.queue.items.getFirst()) && item)
		{
			item.deleteFile();
		}
	}

	static getById(id) {
		return this.repo.get(id);
	}

	static getInstanceById(id) {
		return this.repo.get(id);
	}

	static getInstance = function(params)
	{
		BX.onCustomEvent(window, "onUploaderIsAlmostInited", ['BX.Uploader', params]);
		return new this(params);
	}

	static getInstanceName()
	{
		return 'BX.Uploader';
	}
}
