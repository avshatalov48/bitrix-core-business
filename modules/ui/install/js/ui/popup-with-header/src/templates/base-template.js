import type { ResultContent } from '../types/template';

export class BaseTemplate
{
	getContent(): Array<ResultContent>
	{
		throw new Error('Must be implemented in a child class');
	}

	setOptions(options): void
	{
		this.options = options;
	}
}