import type { BaseEvent } from 'main.core.events';
import type AbstractUploadController from '../backend/abstract-upload-controller';
import type AbstractLoadController from '../backend/abstract-load-controller';

export type ServerOptions = {
	controller?: string,
	controllerOptions?: { [key: string]: any },
	chunkSize?: number,
	forceChunkSize?: boolean,
	chunkRetryDelays?: number[],
	uploadControllerClass?: Class<AbstractUploadController> | string,
	loadControllerClass?: Class<AbstractLoadController> | string,
	events?: { [eventName: string]: (event: BaseEvent) => void },
};