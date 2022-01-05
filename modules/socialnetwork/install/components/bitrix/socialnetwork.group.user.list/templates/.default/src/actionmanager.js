import {ajax, Type} from 'main.core';

export default class ActionManager
{
	constructor(
		params: {
			componentName: string,
			signedParameters: string,
			gridId: string,
		}
	)
	{
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.gridId = params.gridId;
	}

	viewProfile(params)
	{
		const userId = parseInt(!Type.isUndefined(params.userId) ? params.userId : 0);
		const pathToUser = (Type.isStringFilled(params.pathToUser) ? params.pathToUser : '');

		if (
			userId <= 0
			|| !Type.isStringFilled(pathToUser)
		)
		{
			return;
		}

		BX.SidePanel.Instance.open(
			pathToUser.replace('#ID#', userId)
				.replace('#USER_ID#', userId)
				.replace('#user_id#', userId),
			{
				cacheable: false,
				allowChangeHistory: true,
				contentClassName: 'bitrix24-profile-slider-content',
				loader: 'intranet:profile',
				width: 1100,
			}
		);
	}

	act(action, userId)
	{
		ajax.runComponentAction(this.componentName, 'act', {
			mode: 'class',
			signedParameters: this.signedParameters,
			data: {
				action: action,
				fields: {
					userId: userId,
				},
			},
		}).then((response) => {
			if (response.data.success)
			{
				BX.Main.gridManager.reload(this.gridId);
			}
		});
	}

	disconnectDepartment(params)
	{
		const id = parseInt(!Type.isUndefined(params.id) ? params.id : 0);

		if (id <= 0)
		{
			return;
		}

		ajax.runComponentAction(this.componentName, 'disconnectDepartment', {
			mode: 'class',
			signedParameters: this.signedParameters,
			data: {
				fields: {
					id: id,
				},
			},
		}).then((response) => {
			if (response.data.success)
			{
				BX.Main.gridManager.reload(this.gridId);
			}
		});
	}
}
