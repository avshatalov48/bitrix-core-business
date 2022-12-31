import {Tag, Loc, Browser, Runtime, Type} from 'main.core';

export default class CanvasLoader
{
	static instance: ?CanvasLoader = null;
	#justACounter: Number = 0;
	#queue: Map;
	#image: Element;
	#canvas: HTMLCanvasElement;
	#context: Element;
	#reader: ?FileReader;
	#isReady: boolean = true;
	#id: string;

	constructor()
	{
		this.#reader = new FileReader();
		this.#queue = new Map();
		this.#image = new Image();
		this.#canvas = Tag.render`<canvas id="loadercanvas"></canvas>`;
		// document.querySelector('#workarea-content').appendChild(this.#canvas);
		this.#context = this.#canvas.getContext('2d');
		this.#id = String((new Date()).getTime());
	}

	#load(itemId)
	{
		if (!this.#queue.has(itemId) || this.#isReady !== true)
		{
			return;
		}

		this.#isReady = false;
		const [file, successCallback, failCallback] = this.#queue.get(itemId);

		this.#image.onload = function() {};
		this.#image.onerror = function() {};

		/* Almost all browsers cache images from local resource except of FF on 06.03.2017. It appears that
		FF collect src and does not abort image uploading when src is changed. And we had a bug when in
		onload event we got e.target.src of one element but source of image was from '/bitrix/images/1.gif'. */
		// TODO check if chrome and other browsers cache local files for now. If it does not then delete next 2 strings
		try {
			window["URL"]["revokeObjectURL"](this.#image.src);
		}
		catch (e) {

		}
		if (!Browser.isFirefox())
		{
			this.#image.src = '/bitrix/images/1.gif';
		}

		const onFinish = () => {
			this.#queue.delete(itemId);
			this.#isReady = true;
			setTimeout(() => {
				this.#exec();
			}, 0);
		};

		const onLoad = (e) => {
			const image = e && e.target ? e.target : this.#image;
			if (image.src.indexOf('/bitrix/images/1.gif') >= 0)
			{
				return;
			}

			if (!!successCallback)
			{
				onFinish();
				successCallback(image);
			}
		};
		const onError = () => {
			if (!!failCallback)
			{
				try
				{
					failCallback();
				}
				catch (e)
				{
					Runtime.debug(e);
				}
			}
			onFinish();
		}

		this.#image.name = file.name;
		this.#image.onload = onLoad;
		this.#image.onerror = onError;

		if (Type.isPlainObject(file) && (file['src'] || file['tmp_url']))
		{
			const src = file['src'] || file['tmp_url'];
			this.#image.src = encodeURI(src) + (src.indexOf("?") > 0 ? '&' : '?')
				+ 'imageUploader' + this.#id + (this.#justACounter++);
		}
		else
		{
			const res = Object.prototype.toString.call(file);
			if (res !== '[object File]' && res !== '[object Blob]')
			{
				onError();
			}
			else if (window["URL"])
			{
				this.#image.src = window["URL"]["createObjectURL"](file);
			}
			else
			{
				this.#reader.onloadend = (e) => {
					this.#reader.onloadend = null;
					this.#reader.onerror = null;
					this.#image.src = e.target.result;
				};
				this.#reader.onerror = () => {
					this.#reader.onloadend = null;
					this.#reader.onerror = null;
					onError();
				};
				this.#reader.readAsDataURL(file);
			}
		}
	}

	push(file, successCallback, failCallback)
	{
		const id = [this.#id, this.#justACounter++].join('_');
		this.#queue.set(id, [file, successCallback, failCallback]);
		this.#exec();
	}

	#exec()
	{
		if (this.#isReady === true)
		{
			const itemId = Array.from(this.#queue.keys()).shift();
			if (itemId)
			{
				this.#load(itemId);
			}
		}
	}

	getCanvas(): Element
	{
		return this.#canvas;
	}

	getContext(): Element
	{
		return this.#context;
	}

	static #dataURLToBlob(dataURL)
	{
		let marker = ';base64,', parts, contentType, raw, rawLength;
		if (dataURL.indexOf(marker) < 0)
		{
			parts = dataURL.split(',');
			contentType = parts[0].split(':')[1];
			raw = parts[1];
			return new Blob([raw], {type: contentType});
		}

		parts = dataURL.split(marker);
		contentType = parts[0].split(':')[1];
		raw = window.atob(parts[1]);
		rawLength = raw.length;

		const uInt8Array = new Uint8Array(rawLength);

		for(let i = 0; i < rawLength; ++i)
		{
			uInt8Array[i] = raw.charCodeAt(i);
		}

		return new Blob([uInt8Array], {type: contentType});
	}

	pack(fileType)
	{
		return new Promise((resolve, reject) => {
			try
			{
				if (this.#canvas['toBlob'])
				{
					this.#canvas.toBlob(resolve, fileType);
				}
				else
				{
					resolve(this.constructor.#dataURLToBlob(this.#canvas.toDataURL(fileType)));
				}
			}
			catch (e)
			{
				e.message = 'Packing error: ' + e.message;
				reject(e);
			}
		});
	}

	static getInstance(): CanvasLoader
	{
		if (this.instance === null)
		{
			this.instance = new this();
		}
		return this.instance;
	}

	static loadFile(file, successCallback, failCallback)
	{
		if (!window["FileReader"])
		{
			return failCallback(new Error({message: 'FileReader is not supported.'}));
		}


		let newFile = file;
		if (Type.isString(file))
		{
			newFile = {
				src: file,
				name: file.split('/').pop()
			}
		}
		this.getInstance().push(newFile, successCallback, failCallback);
	}

	static loadCanvas()
	{
		this.getInstance().getCanvas()
	}
}
