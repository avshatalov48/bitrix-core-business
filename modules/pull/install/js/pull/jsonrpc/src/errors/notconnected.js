export class ErrorNotConnected extends Error
{
	constructor(message)
	{
		super(message);
		this.name = 'ErrorNotConnected';
	}
}
