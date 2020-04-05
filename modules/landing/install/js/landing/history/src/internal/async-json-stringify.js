const worker = new Worker(
	'/bitrix/js/landing/history/src/worker/json-stringify-worker.js',
);

/**
 * Serializes object
 * @param {Object|array} obj
 * @return {Promise<?String>}
 */
export default function asyncJsonStringify(obj: {[key: string]: any} | Array<any>): Promise<?string>
{
	return new Promise(((resolve) => {
		worker.postMessage(obj);
		worker.addEventListener('message', (event) => {
			resolve(event.data);
		});
	}));
}