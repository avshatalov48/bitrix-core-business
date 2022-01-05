import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import Toolbar from './toolbar';
import ActionManager from './actionmanager';

export default class Manager
{
	static repo = new Map();

	static getById(id)
	{
		return Manager.repo.get(id);
	}

	constructor(
		params: {
			id: string,
			gridId: string,
			filterId: string,
			gridContainerId: string,
			componentName: string,
			signedParameters: string,
			toolbar: {
				id: string,
				menuButtonId: string,
				menuItems: object,
			},
		}
	)
	{
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.gridId = params.gridId;
		this.filterId = (Type.isStringFilled(params.filterId) ? params.filterId : null);
		this.gridContainer = (Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null);

		params.toolbar.componentName = this.componentName;
		this.toolbarInstance = new Toolbar(params.toolbar);
		this.actionManagerInstance = new ActionManager({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			gridId: this.gridId,
		});

		EventEmitter.subscribe('SidePanel.Slider:onMessage', (event: BaseEvent) => {

			const [ sliderEvent ] = event.getCompatData();

			if (
				sliderEvent.getEventId() === 'sonetGroupEvent'
				&& !Type.isUndefined(sliderEvent.data)
				&& Type.isStringFilled(sliderEvent.data.code)
				&& sliderEvent.data.code === 'afterInvite'
			)
			{
				BX.Main.gridManager.reload(this.gridId);
			}
		});

		Manager.repo.set(this.id, this);
	}

	getActionManager()
	{
		return this.actionManagerInstance;
	}

}
