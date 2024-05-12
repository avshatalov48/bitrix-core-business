import { BBCodeNodeScheme } from './node-scheme';

export class BBCodeNewLineScheme extends BBCodeNodeScheme
{
	constructor(options = {})
	{
		super({ ...options, name: ['#linebreak'] });
	}
}
