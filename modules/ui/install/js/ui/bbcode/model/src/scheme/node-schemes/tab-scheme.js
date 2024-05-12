import { BBCodeNodeScheme } from './node-scheme';

export class BBCodeTabScheme extends BBCodeNodeScheme
{
	constructor(options)
	{
		super({ ...options, name: ['#tab'] });
	}
}
