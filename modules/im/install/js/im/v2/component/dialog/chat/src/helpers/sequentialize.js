type Task = {
	args: Array<any>,
	resolve: (result: any) => void,
};

const sharedQueues: Map<string, (...args: any[]) => Promise<any>> = new Map();

/**
 * @description
 * Adds a delay between calls to the fn function.
 * Calls with the same function execute this function in the same sequence
 * @param fn
 * @param delay
 * @param context
 * @returns Function
 */
function sequentializeShared(
	fn: (...args: any[]) => any,
	delay: number,
	context: any = null,
): (...args: any[]) => Promise<any>
{
	if (sharedQueues.has(fn))
	{
		return sharedQueues.get(fn);
	}

	const wrapped = sequentialize(fn, delay, context);
	sharedQueues.set(fn, wrapped);

	return wrapped;
}

function sequentialize(
	fn: (...args: any[]) => any,
	delay: number,
	context: any = null,
): (...args: any[]) => Promise<any>
{
	const queue: Array<Task> = [];
	let isRunning = false;

	async function run(): Promise<void>
	{
		if (isRunning)
		{
			return;
		}

		isRunning = true;

		const task = queue.shift();

		if (!task)
		{
			isRunning = false;

			return;
		}

		const result = fn.apply(context, task.args);
		await wait(delay);
		task.resolve(result);

		isRunning = false;

		run();
	}

	return (...args: any[]): Promise<any> => {
		return new Promise((resolve) => {
			queue.push({ args, resolve });
			run();
		});
	};
}

function wait(ms: number): Promise<void>
{
	return new Promise((resolve) => {
		setTimeout(resolve, ms);
	});
}

export { sequentializeShared as sequentialize };
