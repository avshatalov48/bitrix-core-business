import { ajax as Ajax, Runtime } from 'main.core';

import UploaderError from '../uploader-error';

import type UploaderFile from '../uploader-file';
import type Server from './server';
import type ServerLoadController from './server-load-controller';

type QueueTask = {
	controller: ServerLoadController,
	file: UploaderFile,
}

type Queue = {
	tasks: QueueTask[],
	load: Function,
	xhr: XMLHttpRequest,
	aborted: boolean,
};

const pendingQueues: WeakMap<Server, Queue> = new WeakMap();
const loadingFiles: WeakMap<UploaderFile, Queue> = new WeakMap();

export function loadMultiple(controller: ServerLoadController, file: UploaderFile): void
{
	const server: Server = controller.getServer();
	const timeout = controller.getOption('timeout', 100);

	let queue = pendingQueues.get(server);
	if (!queue)
	{
		queue = {
			tasks: [],
			load: Runtime.debounce(loadInternal, timeout, server),
			xhr: null,
			aborted: false,
		};

		pendingQueues.set(server, queue);
	}

	queue.tasks.push({ controller, file });
	queue.load();
}

export function abort(controller: ServerLoadController, file: UploaderFile): void
{
	const server: Server = controller.getServer();
	const queue: Queue = pendingQueues.get(server);
	if (queue)
	{
		queue.tasks = queue.tasks.filter((task: QueueTask): boolean => {
			return task.file !== file;
		});

		if (queue.tasks.length === 0)
		{
			pendingQueues.delete(server);
		}
	}
	else
	{
		const queue: Queue = loadingFiles.get(file);
		if (queue)
		{
			queue.tasks = queue.tasks.filter((task: QueueTask): boolean => {
				return task.file !== file;
			});

			loadingFiles.delete(file);

			if (queue.tasks.length === 0)
			{
				queue.aborted = true;
				queue.xhr.abort();
			}
		}
	}
}

function loadInternal(): void
{
	// eslint-disable-next-line no-invalid-this,unicorn/no-this-assignment
	const server: Server = this;
	const queue: Queue = pendingQueues.get(server);
	if (!queue)
	{
		return;
	}

	pendingQueues.delete(server);

	if (queue.tasks.length === 0)
	{
		return;
	}

	const fileIds = [];
	queue.tasks.forEach((task: QueueTask): void => {
		const file: UploaderFile = task.file;
		fileIds.push(file.getServerFileId());
		loadingFiles.set(file, queue);
	});

	const controllerOptions = server.getControllerOptions();
	Ajax.runAction('ui.fileuploader.load', {
		data: {
			fileIds,
		},
		getParameters: {
			controller: server.getController(),
			controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
		},
		onrequeststart: (xhr): void => {
			queue.xhr = xhr;
		},
		onprogress: (event: ProgressEvent): void => {
			if (event.lengthComputable)
			{
				const progress: number = event.total > 0 ? Math.floor(event.loaded / event.total * 100) : 100;

				queue.tasks.forEach((task: QueueTask): void => {
					const { controller } = task;
					controller.emit('onProgress', { progress });
				});
			}
		},
	})
		.then((response) => {
			if (response.data?.files)
			{
				const fileResults = {};
				response.data.files.forEach((fileResult): void => {
					fileResults[fileResult.id] = fileResult;
				});

				queue.tasks.forEach((task: QueueTask): void => {
					const { controller, file } = task;
					const fileResult = fileResults[file.getServerFileId()] || null;

					loadingFiles.delete(file);

					if (fileResult && fileResult.success)
					{
						controller.emit('onProgress', { progress: 100 });
						controller.emit('onLoad', { fileInfo: fileResult.data.file });
					}
					else
					{
						const error: UploaderError = UploaderError.createFromAjaxErrors(fileResult?.errors);
						controller.emit('onError', { error });
					}
				});
			}
			else
			{
				const error: UploaderError = new UploaderError('SERVER_ERROR');
				queue.tasks.forEach((task: QueueTask): void => {
					const { controller, file } = task;

					loadingFiles.delete(file);
					controller.emit('onError', { error: error.clone() });
				});
			}
		})
		.catch((response) => {
			const error: ?UploaderError = queue.aborted ? null : UploaderError.createFromAjaxErrors(response.errors);
			queue.tasks.forEach((task: QueueTask): void => {
				const { controller, file } = task;

				loadingFiles.delete(file);

				if (!queue.aborted)
				{
					controller.emit('onError', { error: error.clone() });
				}
			});
		})
	;
}
