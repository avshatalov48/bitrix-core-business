import {Type} from 'main.core';

export class Widget
{
	constructor()
	{
		this.widget = null;
	}

	show(targetNode: HTMLElement)
	{
		if (this.widget)
		{
			if (this.widget.isShown())
			{
				this.widget.close();
				return;
			}
		}

		const data = this.getData({
			targetNode: targetNode,
		});

		if (Type.isNull(data))
		{
			return;
		}

		this.widget = this.getWidget({
			targetNode: targetNode,
			data: data,
		});

		if (this.widget)
		{
			this.widget.show();
		}
	}

	hide()
	{
		if (
			this.widget
			&& this.widget.isShown()
		)
		{
			this.widget.close();
		}
	}

	getData(params)
	{
		return {};
	}

	getWidget(params)
	{
		return null;
	}
}
