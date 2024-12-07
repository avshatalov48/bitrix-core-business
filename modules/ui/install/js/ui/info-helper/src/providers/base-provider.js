export class BaseProvider
{
	show(code, params): void
	{
		throw new Error('Must be implemented in a child class');
	}

	close(): void
	{
		throw new Error('Must be implemented in a child class');
	}
}
