import type AbstractUploadController from '../backend/abstract-upload-controller';
import type AbstractLoadController from '../backend/abstract-load-controller';
import type AbstractRemoveController from '../backend/abstract-remove-controller';

export type ServerOptions = {
	controller?: string,
	controllerOptions?: { [key: string]: any },
	chunkSize?: number,
	forceChunkSize?: boolean,
	chunkRetryDelays?: number[],
	uploadControllerClass?: Class<AbstractUploadController> | string,
	uploadControllerOptions?: { [key: string]: any },
	loadControllerClass?: Class<AbstractLoadController> | string,
	loadControllerOptions?: { [key: string]: any },
	removeControllerClass?: Class<AbstractRemoveController> | string,
	removeControllerOptions?: { [key: string]: any },
};