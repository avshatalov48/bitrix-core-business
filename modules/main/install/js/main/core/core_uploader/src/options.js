import {Loc} from 'main.core';


export default class Options {
	static defaultSettings = null;
	static #quota;

	static uploadStatus = {
		ready: 'upload is ready',
		preparing: 'upload is not started, but preparing',
		inProgress: 'upload is in active streaming',
		done: 'upload is in successfully done',
		error: 'upload is in finished with errors',
		stopped: 'PAUSE',
	};

	static fileStatus = {
		ready: 'fileIsReady',
		removed: 'fileIsRemoved',
		restored: 'fileIsRestored',
		errored: 'fileIsBad'
	}

	static getEventName(eventName)
	{
		return [this.getEventNamespace(), ...eventName].join(':');
	}

	static getEventNamespace(): string
	{
		return 'BX:Main:Uploader:'
	}

	static calibratePostSize(deltaTime, size: ?number)
	{
		if (deltaTime <= 0)
		{
			return;
		}

		if (deltaTime < this.defaultSettings['estimatedTimeForUploadFile'])
		{
			const sizes = [
				this.defaultSettings['currentPostSize'] * 2,
				this.defaultSettings["phpPostMaxSize"]];
			if (size > 0)
			{
				sizes.push(Math.ceil(size * this.defaultSettings['estimatedTimeForUploadFile'] * 1000 / deltaTime));
			}
			this.defaultSettings['currentPostSize'] = Math.min(...sizes);
		}
		else
		{
			this.defaultSettings['currentPostSize'] = Math.max(
				Math.ceil(this.defaultSettings['currentPostSize'] / 2),
				this.defaultSettings['phpPostMinSize']);
		}
		this.defaultSettings['currentPostSize'] = Math.max(
			this.defaultSettings['currentPostSize'],
			this.defaultSettings['phpPostMinSize']
		);
	}

	static getUploadLimits(key: ?string)
	{
		if (!this.defaultSettings)
		{
			this.defaultSettings = {
				currentPostSize: 5.5 * 1024 * 1024,
				phpPostMinSize: 5.5 * 1024 * 1024, // Bytes
				phpUploadMaxFilesize: Math.min(/^d+$/.test(Loc.getMessage('phpUploadMaxFilesize')) ? Loc.getMessage('phpUploadMaxFilesize') : 5 * 1024 * 1024, 5 * 1024 * 1024), // Bytes 5MB because of Cloud
				phpMaxFileUploads: Math.max((/^d+$/.test(Loc.getMessage('phpMaxFileUploads')) ? Loc.getMessage('phpMaxFileUploads') : 20), 20),
				phpPostMaxSize: (/^d+$/.test(Loc.getMessage('phpPostMaxSize')) ? Loc.getMessage('phpPostMaxSize') : 11 * 1024 * 1024), // Bytes
				estimatedTimeForUploadFile: 10 * 60, // in sec
				maxSize: this.getMaxSize(),
			};
		}
		if (key)
		{
			return this.defaultSettings[key];
		}
		return this.defaultSettings;

	}

	static getFileTypes(): Array
	{
		return [
			'A', //'A'll files
			'I', //'I'mages
			'F' //'F'iles with selected extensions
		];
	}

	static getImageExtensions()
	{
		return ["jpg", "bmp", "jpeg", "jpe", "gif", "png", "webp"];
	}

	static getMaxSize()
	{
		if (this.#quota !== null && !this.#quota)
		{
			if (/^\d+$/.test(Loc.getMessage("bxQuota")))
			{
				this.#quota = parseInt(Loc.getMessage("bxQuota"));
			}
			else
			{
				this.#quota = null
			}
		}
		return this.#quota;
	}

	static decrementMaxSize(size: number)
	{
		if (this.getMaxSize() !== null)
		{
			this.#quota -= size;
		}
		return this.#quota;
	}

	static getMaxTimeToUploading()
	{
		return 900;
	}

	static getVersion()
	{
		return '1';
	}
}
