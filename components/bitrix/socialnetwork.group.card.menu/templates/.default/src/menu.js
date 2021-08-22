import {Type, Event} from 'main.core';

class WorkgroupSliderMenu
{
	constructor()
	{
		this.menuNode = null;
	}

	init(params)
	{
		this.menuNode = (Type.isStringFilled(params.menuNodeId) ? document.getElementById(params.menuNodeId) : null);

		if (Type.isDomNode(this.menuNode))
		{
			this.menuItems = Array.prototype.slice.call(this.menuNode.querySelectorAll('a'));

			(this.menuItems || []).forEach((item) => {
				Event.bind(item, 'click', (event) => {
					this.processClick(item, event);
					return event.preventDefault();
				});
			});
		}
	}

	processClick(item)
	{
		const url = item.getAttribute('data-url');
		const action = item.getAttribute('data-action');

		if (Type.isStringFilled(url))
		{
			window.location.href = url;
		}
		else if (Type.isStringFilled(action))
		{
			switch (action)
			{
				case 'theme':
					BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
					break;
				case 'join':

					break;
				default:
			}
		}
	}
}

export {
	WorkgroupSliderMenu,
}