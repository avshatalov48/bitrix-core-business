import {Type, Loc} from 'main.core';

export class PullControllerSocialnetwork
{
	static get events()
	{
		return {
			add: 'add',
			update: 'update',
			delete: 'delete',
			userAdd: 'userAdd',
			userUpdate: 'userUpdate',
			userDelete: 'userDelete',
			favoritesChanged: 'favoritesChanged',
			pinChanged: 'pinChanged',
		};
	}

	static getInstance()
	{

	}

	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;

		this.gridController = options.gridController;
		this.pullController = options.pullController;

		this.gridPinController = this.gridController.getInstance().getPinController();
		this.grid = this.gridController.getGrid();
	}

	getModuleId()
	{
		return 'socialnetwork';
	}

	getMap()
	{
		return {
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_ADD') ]: this.onWorkgroupAdd.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_UPDATE') ]: this.onWorkgroupUpdate.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_DELETE') ]: this.onWorkgroupDelete.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_ADD') ]: this.onWorkgroupUserAdd.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_UPDATE') ]: this.onWorkgroupUserUpdate.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_USER_DELETE') ]: this.onWorkgroupUserDelete.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_FAVORITES_CHANGED') ]: this.onWorkgroupFavoritesChanged.bind(this),
			[ Loc.getMessage('PUSH_EVENT_WORKGROUP_PIN_CHANGED') ]: this.onWorkgroupPinChanged.bind(this),
		};
	}

	onWorkgroupAdd(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.add,
			moveParams: {
				rowBefore: this.gridPinController.getLastPinnedRowId(),
				rowAfter: this.gridController.getInstance().getFirstRowId(),
			},
		};

		this.pullController.checkExistence(data.params.GROUP_ID).then(
			response => this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params),
			response => console.error(response)
		);
	}

	onWorkgroupUpdate(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.update,
		};

		this.pullController.checkExistence(data.params.GROUP_ID).then(
			response => this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params),
			response => console.error(response)
		);
	}

	onWorkgroupDelete(data)
	{
		this.pullController.removeRow(data.params.GROUP_ID);
	}

	onWorkgroupUserAdd(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.userAdd,
		};

		this.pullController.checkExistence(data.params.GROUP_ID).then(
			response => this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params),
			response => console.error(response)
		);
	}

	onWorkgroupUserUpdate(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.userUpdate,
		};

		this.pullController.checkExistence(data.params.GROUP_ID).then(
			response => this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params),
			response => console.error(response)
		);
	}

	onWorkgroupUserDelete(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.userDelete,
		};

		this.pullController.checkExistence(data.params.GROUP_ID).then(
			response => this.pullController.onCheckExistenceSuccess(response, data.params.GROUP_ID, params),
			response => console.error(response)
		);
	}

	onWorkgroupFavoritesChanged(data)
	{
		const params = {
			event: PullControllerSocialnetwork.events.favoritesChanged,
		};

		this.pullController.moveToDirectPlace(data.GROUP_ID, null, params);
	}

	onWorkgroupPinChanged(data)
	{
		if (
			!Type.isStringFilled(data.ACTION)
			|| !['pin', 'unpin'].includes(data.ACTION)
		)
		{
			return;
		}

		const params = {
			event: PullControllerSocialnetwork.events.pinChanged,
		};

		this.pullController.moveToDirectPlace(data.GROUP_ID, null, params);
	}
}