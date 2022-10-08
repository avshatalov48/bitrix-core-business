import createUniqueId from './create-unique-id';

const createWorker = (fn: Function) => {
	const workerBlob = new Blob(['(', fn.toString(), ')()'], { type: 'application/javascript' });
	const workerURL = URL.createObjectURL(workerBlob);
	const worker = new Worker(workerURL);

	return {
		post: (message, callback, transfer): void => {
			const id = createUniqueId();
			worker.onmessage = event => {
				if (event.data.id === id)
				{
					callback(event.data.message);
				}
			};

			worker.postMessage({ id, message }, transfer);
		},
		terminate: (): void => {
			worker.terminate();
			URL.revokeObjectURL(workerURL);
		}
	};
};

export default createWorker;