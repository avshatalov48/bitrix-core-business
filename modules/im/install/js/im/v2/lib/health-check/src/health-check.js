export class HealthCheckManager
{
	static #instance: HealthCheckManager;
	#isShown: boolean = false;

	static getInstance(): HealthCheckManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	setIsShown(status: boolean): void
	{
		this.#isShown = status;
	}

	getIsShown(): boolean
	{
		return this.#isShown;
	}
}
