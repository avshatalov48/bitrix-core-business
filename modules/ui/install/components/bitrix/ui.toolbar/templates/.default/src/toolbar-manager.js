import { Toolbar, type ToolbarOptions } from './toolbar';

class Manager
{
	toolbars: {[id: string]: Toolbar };

	constructor()
	{
		this.toolbars = {};
	}

	create(options: ToolbarOptions): Toolbar
	{
		const toolbar = new Toolbar(options);

		if (this.get(toolbar.getId()))
		{
			throw new Error("The toolbar instance with the same 'id' already exists.");
		}

		this.toolbars[toolbar.getId()] = toolbar;

		return toolbar;
	}

	getDefaultToolbar(): Toolbar | null
	{
		return this.get('default-toolbar');
	}

	get(id: string): Toolbar | null
	{
		return id in this.toolbars ? this.toolbars[id] : null;
	}
}

export const ToolbarManager = new Manager();
