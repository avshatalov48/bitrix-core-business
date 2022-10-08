import { Extension, Runtime, Type } from 'main.core';
import type { ServerOptions } from '../types/server-options';
import UploadController from './upload-controller';
import AbstractUploadController from './abstract-upload-controller';
import ServerLoadController from './server-load-controller';
import AbstractLoadController from './abstract-load-controller';
import ClientLoadController from './client-load-controller';

export default class Server
{
	controller: ?string = null;
	controllerOptions: ?{ [key: string]: any } = null;
	uploadControllerClass: Class<AbstractUploadController> = null;
	loadControllerClass: Class<AbstractLoadController> = null;
	chunkSize: number = null;
	defaultChunkSize: number = null;
	chunkMinSize: number = null;
	chunkMaxSize: number = null;
	chunkRetryDelays: number[] = [1000, 3000, 6000];

	constructor(serverOptions: ServerOptions)
	{
		const options = Type.isPlainObject(serverOptions) ? serverOptions : {};

		this.controller = Type.isStringFilled(options.controller) ? options.controller : null;
		this.controllerOptions = Type.isPlainObject(options.controllerOptions) ? options.controllerOptions : null;

		const chunkSize =
			Type.isNumber(options.chunkSize) && options.chunkSize > 0
				? options.chunkSize
				: this.getDefaultChunkSize()
		;

		this.chunkSize = options.forceChunkSize === true ? chunkSize : this.#calcChunkSize(chunkSize);

		if (options.chunkRetryDelays === false || options.chunkRetryDelays === null)
		{
			this.chunkRetryDelays = [];
		}
		else if (Type.isArray(options.chunkRetryDelays))
		{
			this.chunkRetryDelays = options.chunkRetryDelays;
		}

		['uploadControllerClass', 'loadControllerClass'].forEach((controllerClass: string) => {
			if (Type.isStringFilled(options[controllerClass]))
			{
				this[controllerClass] = Runtime.getClass(options[controllerClass]);
				if (!Type.isFunction(options[controllerClass]))
				{
					throw new Error(`Uploader.Server: "${controllerClass}" must be a function.`);
				}
			}
			else if (Type.isFunction(options[controllerClass]))
			{
				this[controllerClass] = options[controllerClass];
			}
		});
	}

	createUploadController(): ?UploadController
	{
		if (this.uploadControllerClass)
		{
			const controller = new this.uploadControllerClass(this);
			if (!(controller instanceof AbstractUploadController))
			{
				throw new Error(
					'Uploader.Server: "uploadControllerClass" must be an instance of AbstractUploadController.',
				);
			}

			return controller;
		}
		else if (Type.isStringFilled(this.controller))
		{
			return new UploadController(this);
		}

		return null;
	}

	createLoadController(): ServerLoadController
	{
		if (this.loadControllerClass)
		{
			const controller = new this.loadControllerClass(this);
			if (!(controller instanceof AbstractLoadController))
			{
				throw new Error(
					'Uploader.Server: "loadControllerClass" must be an instance of AbstractLoadController.',
				);
			}

			return controller;
		}

		return new ServerLoadController(this);
	}

	createClientLoadController(): ClientLoadController
	{
		return new ClientLoadController(this);
	}

	getController(): ?string
	{
		return this.controller;
	}

	getControllerOptions(): ?{ [key: string]: any }
	{
		return this.controllerOptions;
	}

	getChunkSize(): number
	{
		return this.chunkSize;
	}

	getDefaultChunkSize(): number
	{
		if (this.defaultChunkSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.defaultChunkSize = settings.get('defaultChunkSize', 5 * 1024 * 1024);
		}

		return this.defaultChunkSize;
	}

	getChunkMinSize(): number
	{
		if (this.chunkMinSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.chunkMinSize = settings.get('chunkMinSize', 1024 * 1024);
		}

		return this.chunkMinSize;
	}

	getChunkMaxSize(): number
	{
		if (this.chunkMaxSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.chunkMaxSize = settings.get('chunkMaxSize', 5 * 1024 * 1024);
		}

		return this.chunkMaxSize;
	}

	getChunkRetryDelays(): number[]
	{
		return this.chunkRetryDelays;
	}

	#calcChunkSize(chunkSize: number): number
	{
		return Math.min(Math.max(this.getChunkMinSize(), chunkSize), this.getChunkMaxSize());
	}
}
