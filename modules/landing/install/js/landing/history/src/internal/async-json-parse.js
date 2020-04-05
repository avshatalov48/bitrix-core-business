const worker = new Worker(
	'/bitrix/js/landing/history/src/worker/json-parse-worker.js',
);

/**
 * Parses json string
 * @param {string} str
 * @return {Promise<?Object|array>}
 */
export default function asyncJsonParse(str): Promise<{[key: string]: any} | Array<any>>
{
	return new Promise(((resolve) => {
		worker.postMessage(str);
		worker.addEventListener('message', (event) => {
			resolve(event.data);
		});
	}));
}
