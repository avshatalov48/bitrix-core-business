import { ajax as Ajax, Runtime } from 'main.core';

import UploaderError from '../uploader-error';

import type UploaderFile from '../uploader-file';
import type Server from './server';
import type ServerLoadController from './server-load-controller';

const queues = new WeakMap();

export function loadMultiple(controller: ServerLoadController, file: UploaderFile)
{
	const server = controller.getServer();
	let queue = queues.get(server);
	if (!queue)
	{
		queue = {
			tasks: [],
			load: Runtime.debounce(loadInternal, 100, server),
			xhr: null,
		};

		queues.set(server, queue);
	}

	queue.tasks.push({ controller, file });
	queue.load();
}

export function abort(controller: ServerLoadController)
{
	const server = controller.getServer();
	const queue = queues.get(server);
	if (queue)
	{
		queue.xhr.abort();
		queue.xhr = null;
		queues.delete(server);

		tasks.forEach(task => {
			const { controller, file } = task;
			controller.emit('onAbort');
		});
	}
}

function loadInternal()
{
	const server: Server = this;
	const queue = queues.get(server);
	if (!queue)
	{
		return;
	}

	const { tasks } = queue;
	queues.delete(server);

	const fileIds = [];
	tasks.forEach(task => {
		const { controller, file } = task;
		fileIds.push(file.getServerId());
	});

	const controllerOptions = server.getControllerOptions();
	Ajax.runAction('ui.fileuploader.load', {
			data: {
				fileIds: fileIds,
			},
			getParameters: {
				controller: server.getController(),
				controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
			},
			onrequeststart: (xhr) => {
				queue.xhr = xhr;
			},
			onprogress: (event: ProgressEvent) => {
				if (event.lengthComputable)
				{
					const progress = event.total > 0 ? Math.floor(event.loaded / event.total * 100) : 100;

					tasks.forEach(task => {
						const { controller, file } = task;
						controller.emit('onProgress', { file, progress });
					});
				}
			},
		})
		.then(response => {
			if (response.data?.files)
			{
				const fileResults = {};
				response.data.files.forEach((fileResult) => {
					fileResults[fileResult.id] = fileResult;
				});

				tasks.forEach(task => {
					const { controller, file } = task;
					const fileResult = fileResults[file.getServerId()] || null;

					if (fileResult && fileResult.success)
					{
						controller.emit('onProgress', { file, progress: 100 });
						controller.emit('onLoad', { fileInfo: fileResult.data.file });
					}
					else
					{
						const error = UploaderError.createFromAjaxErrors(fileResult?.errors);
						controller.emit('onError', { error });
					}
				});
			}
			else
			{
				const error = new UploaderError('SERVER_ERROR');
				tasks.forEach(task => {
					const { controller } = task;
					controller.emit('onError', { error: error.clone() });
				});
			}
		})
		.catch(response => {
			const error = UploaderError.createFromAjaxErrors(response.errors);
			tasks.forEach(task => {
				const { controller } = task;
				controller.emit('onError', { error: error.clone() });
			});
		})
	;
}