import { CircularBuffer } from '../../util/src/util';

export class BufferedLogger
{
	buffer: CircularBuffer;

	constructor(bufferSize: number)
	{
		this.buffer = new CircularBuffer(bufferSize);
	}

	getAll()
	{
		return this.buffer.getAll();
	}

	log(...params)
	{
		this.buffer.push({
			time: new Date(),
			level: 'info',
			data: params,
		});

		console.log(...params);
	}

	warn(...params)
	{
		this.buffer.push({
			time: new Date(),
			level: 'warn',
			data: params,
		});

		console.warn(...params);
	}

	error(...params)
	{
		this.buffer.push({
			time: new Date(),
			level: 'error',
			data: params,
		});

		console.error(...params);
	}
}
