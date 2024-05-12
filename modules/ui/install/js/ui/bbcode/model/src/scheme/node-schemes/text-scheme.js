import { BBCodeNodeScheme } from './node-scheme';

export class BBCodeTextScheme extends BBCodeNodeScheme
{
	constructor(options)
	{
		super({ ...options, name: ['#text'] });
	}
}
