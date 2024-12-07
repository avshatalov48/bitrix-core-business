import { Loader } from 'main.core';

export class Progress
{
	loader: Loader;
	container: HTMLElement;
	isProgress: Boolean = false;

	constructor(container)
	{
		this.container = container;
	}

	getLoader()
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				size: 150,
			});
		}

		return this.loader;
	}

	start()
	{
		this.isProgress = true;
		if (!this.getLoader().isShown())
		{
			this.getLoader().show(this.container);
		}
	}

	stop()
	{
		this.isProgress = false;
		this.getLoader().hide();
	}
}
