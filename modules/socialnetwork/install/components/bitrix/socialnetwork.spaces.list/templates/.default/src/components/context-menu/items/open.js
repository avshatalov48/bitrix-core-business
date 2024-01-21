import { ContextItem } from './context-item';

export class Open extends ContextItem
{
	path: string;

	create(): JSON
	{
		return {
			text: this.message,
			href: this.path,
		}
	}

	setPath(path: string): Open
	{
		this.path = path;
		return this;
	}
}