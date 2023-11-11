import { Extension, Runtime, Type } from 'main.core';
import UploadController from './upload-controller';
import AbstractUploadController from './abstract-upload-controller';
import ServerLoadController from './server-load-controller';
import AbstractLoadController from './abstract-load-controller';
import ClientLoadController from './client-load-controller';
import AbstractRemoveController from './abstract-remove-controller';
import RemoveController from './remove-controller';
import ServerlessLoadController from './serverless-load-controller';

import type { ServerOptions } from '../types/server-options';

export default class Server
{
	#controller: ?string = null;
	#controllerOptions: ?{ [key: string]: any } = null;
	#uploadControllerClass: Class<AbstractUploadController> = null;
	#uploadControllerOptions: ?{ [key: string]: any } = {};
	#loadControllerClass: Class<AbstractLoadController> = null;
	#loadControllerOptions: ?{ [key: string]: any } = {};
	#removeControllerClass: Class<AbstractRemoveController> = null;
	#removeControllerOptions: ?{ [key: string]: any } = {};
	#chunkSize: number = null;
	#defaultChunkSize: number = null;
	#chunkMinSize: number = null;
	#chunkMaxSize: number = null;
	#chunkRetryDelays: number[] = [1000, 3000, 6000];

	constructor(serverOptions: ServerOptions)
	{
		const options: ServerOptions = Type.isPlainObject(serverOptions) ? serverOptions : {};

		this.#controller = Type.isStringFilled(options.controller) ? options.controller : null;
		this.#controllerOptions = Type.isPlainObject(options.controllerOptions) ? options.controllerOptions : null;

		const chunkSize: number = (
			Type.isNumber(options.chunkSize) && options.chunkSize > 0
				? options.chunkSize
				: this.getDefaultChunkSize()
		);

		this.#chunkSize = options.forceChunkSize === true ? chunkSize : this.#calcChunkSize(chunkSize);

		if (options.chunkRetryDelays === false || options.chunkRetryDelays === null)
		{
			this.#chunkRetryDelays = [];
		}
		else if (Type.isArray(options.chunkRetryDelays))
		{
			this.#chunkRetryDelays = options.chunkRetryDelays;
		}

		const controllerClasses: string[] = ['uploadControllerClass', 'loadControllerClass', 'removeControllerClass'];
		controllerClasses.forEach((controllerClass: string): void => {
			let fn = null;
			if (Type.isStringFilled(options[controllerClass]))
			{
				fn = Runtime.getClass(options[controllerClass]);
				if (!Type.isFunction(fn))
				{
					throw new TypeError(`Uploader.Server: "${controllerClass}" must be a function.`);
				}
			}
			else if (Type.isFunction(options[controllerClass]))
			{
				fn = options[controllerClass];
			}

			switch (controllerClass)
			{
				case 'uploadControllerClass':
					this.#uploadControllerClass = fn;
					break;
				case 'loadControllerClass':
					this.#loadControllerClass = fn;
					break;
				case 'removeControllerClass':
					this.#removeControllerClass = fn;
					break;
				default:
					// No default
			}
		});

		this.#loadControllerOptions = (
			Type.isPlainObject(options.loadControllerOptions) ? options.loadControllerOptions : {}
		);

		this.#uploadControllerOptions = (
			Type.isPlainObject(options.uploadControllerOptions) ? options.uploadControllerOptions : {}
		);

		this.#removeControllerOptions = (
			Type.isPlainObject(options.removeControllerOptions) ? options.removeControllerOptions : {}
		);
	}

	createUploadController(): ?UploadController
	{
		if (this.#uploadControllerClass)
		{
			const controller: AbstractUploadController = new this.#uploadControllerClass(this, this.#uploadControllerOptions);
			if (!(controller instanceof AbstractUploadController))
			{
				throw new TypeError(
					'Uploader.Server: "uploadControllerClass" must be an instance of AbstractUploadController.',
				);
			}

			return controller;
		}

		if (Type.isStringFilled(this.#controller))
		{
			return new UploadController(this, this.#uploadControllerOptions);
		}

		return null;
	}

	createServerLoadController(): AbstractLoadController
	{
		if (this.#loadControllerClass)
		{
			const controller: AbstractLoadController = new this.#loadControllerClass(this, this.#loadControllerOptions);
			if (!(controller instanceof AbstractLoadController))
			{
				throw new TypeError(
					'Uploader.Server: "loadControllerClass" must be an instance of AbstractLoadController.',
				);
			}

			return controller;
		}

		return this.createDefaultServerLoadController();
	}

	createDefaultServerLoadController(): ServerLoadController
	{
		return new ServerLoadController(this, this.#loadControllerOptions);
	}

	createClientLoadController(): ClientLoadController
	{
		return new ClientLoadController(this, this.#loadControllerOptions);
	}

	createServerlessLoadController(): ServerlessLoadController
	{
		return new ServerlessLoadController(this, this.#loadControllerOptions);
	}

	createRemoveController(): ?AbstractRemoveController
	{
		if (this.#removeControllerClass)
		{
			const controller: AbstractRemoveController = new this.#removeControllerClass(this, this.#removeControllerOptions);
			if (!(controller instanceof AbstractRemoveController))
			{
				throw new TypeError(
					'Uploader.Server: "removeControllerClass" must be an instance of AbstractRemoveController.',
				);
			}

			return controller;
		}

		if (Type.isStringFilled(this.#controller))
		{
			return new RemoveController(this, this.#removeControllerOptions);
		}

		return null;
	}

	getController(): ?string
	{
		return this.#controller;
	}

	getControllerOptions(): ?{ [key: string]: any }
	{
		return this.#controllerOptions;
	}

	getChunkSize(): number
	{
		return this.#chunkSize;
	}

	getDefaultChunkSize(): number
	{
		if (this.#defaultChunkSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.#defaultChunkSize = settings.get('defaultChunkSize', 5 * 1024 * 1024);
		}

		return this.#defaultChunkSize;
	}

	getChunkMinSize(): number
	{
		if (this.#chunkMinSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.#chunkMinSize = settings.get('chunkMinSize', 1024 * 1024);
		}

		return this.#chunkMinSize;
	}

	getChunkMaxSize(): number
	{
		if (this.#chunkMaxSize === null)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			this.#chunkMaxSize = settings.get('chunkMaxSize', 5 * 1024 * 1024);
		}

		return this.#chunkMaxSize;
	}

	getChunkRetryDelays(): number[]
	{
		return this.#chunkRetryDelays;
	}

	#calcChunkSize(chunkSize: number): number
	{
		return Math.min(Math.max(this.getChunkMinSize(), chunkSize), this.getChunkMaxSize());
	}
}
