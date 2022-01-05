import {Type, Event, Runtime, ajax} from 'main.core';

class WorkgroupSliderMenu
{
	constructor()
	{
		this.menuNode = null;
		this.pageBodyStyles = {};
		this.signedParameters = '';
	}

	init(params)
	{
		this.menuNode = (Type.isStringFilled(params.menuNodeId) ? document.getElementById(params.menuNodeId) : null);
		this.pageBodyStyles = (Type.isPlainObject(params.pageBodyStyles) ? params.pageBodyStyles : {});
		this.signedParameters = (Type.isStringFilled(params.signedParameters) ? params.signedParameters : '');

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
				case 'card':
				case 'edit':
				case 'copy':
				case 'delete':
				case 'leave':
					this.changePage(action);
					break;
				case 'theme':
					BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
					break;
				case 'join':

					break;
				default:
			}
		}
	}

	changePage(action)
	{
		let componentName = '';
		const componentParams = {
			componentTemplate: '',
		};

		switch (action)
		{
			case 'card':
				componentName = 'bitrix:socialnetwork.group';
				componentParams.componentTemplate = 'card';
				break;
			case 'edit':
				componentName = 'bitrix:socialnetwork.group_create.ex';
				componentParams.TAB = 'edit';
				break;
			case 'copy':
				componentName = 'bitrix:socialnetwork.group_copy';
				break;
			case 'delete':
				componentName = 'bitrix:socialnetwork.group_delete';
				break;
			case 'leave':
				componentName = 'bitrix:socialnetwork.user_leave_group';
				break;
			default:
		}

		if (!Type.isStringFilled(componentName))
		{
			return;
		}

		ajax.runComponentAction(componentName, 'getComponent', {
			mode: 'ajax',
			signedParameters: this.signedParameters,
			data: {
				params: componentParams,
			}
		}).then((response) => {

			if (
				!Type.isPlainObject(response.data)
				|| !Type.isStringFilled(response.data.html)
			)
			{
				return;
			}

			// change location address

			if (document.getElementById('workarea-content'))
			{
				Runtime.html(document.getElementById('workarea-content'), response.data.html).then(() => {
					Object.entries(this.pageBodyStyles).forEach(([key, style]) => {
						document.body.classList.remove(style);
					});

					if (Type.isStringFilled(this.pageBodyStyles[action]))
					{
						document.body.classList.add(this.pageBodyStyles[action]);
					}
				});
			}

			if (
				Type.isPlainObject(response.data.componentResult)
				&& Type.isStringFilled(response.data.componentResult.PageTitle)
				&& document.getElementById('pagetitle')
			)
			{
				const titleContainer = document.getElementById('pagetitle').querySelector('.ui-side-panel-wrap-title-name');
				if (titleContainer)
				{
					Runtime.html(titleContainer, response.data.componentResult.PageTitle);
				}
			}

			// change Body class


		}).catch((response) => {
console.log('failed');
console.dir(response);

			// process error
		});
	}
}

export {
	WorkgroupSliderMenu,
}