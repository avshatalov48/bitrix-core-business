export class ErrorTimeout extends Error
{
	constructor(message)
	{
		super(message);
		this.name = 'ErrorTimeout';
	}
}
