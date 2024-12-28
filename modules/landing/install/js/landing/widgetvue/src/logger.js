export class Logger
{
	#enable: boolean = false;

	constructor(enable: boolean = false): void
	{
		this.#enable = enable;
	}

	log(...message: string)
	{
		if (this.#enable)
		{
			console.log(...message);
		}
	}
}
