import 'ui.notification';

export type BaseActionType = {
	grid: ?BX.Main.grid,
	showPopups: ?boolean,
};

type ActionType = 'component' | 'controller';
type ModeType = 'sync' | 'ajax' | 'class';

export type ActionConfig = {
	type: ActionType,
	name: string,
	component?: string,
	options?: {
		mode?: ModeType,
		[key: string]: any,
	},
};

/**
 * @abstract
 */
export class BaseAction
{
	grid: ?BX.Main.grid;

	/**
	 * @abstract
	 */
	static getActionId(): string
	{
		throw new Error('not implemented');
	}

	/**
	 * @abstract
	 * @returns {ActionConfig}
	 */
	getActionConfig(): ActionConfig
	{
		throw new Error('not implemented');
	}

	constructor(params: BaseActionType)
	{
		this.grid = params.grid;
	}

	setActionParams(params: Object): void
	{
	}

	getActionData(): Object
	{
		return {};
	}

	async execute(): void
	{
		this.onBeforeActionRequest();
		await this.sendActionRequest();
		this.onAfterActionRequest();
	}

	onBeforeActionRequest(): void
	{
	}

	async sendActionRequest(): void
	{
		try
		{
			const result = await new Promise((resolve, reject) => {
				const actionConfig = this.getActionConfig();
				const actionData = this.getActionData();
				const ajaxOptions = {
					...actionConfig.options,
					data: actionData,
				};

				let ajaxPromise = null;

				switch (actionConfig.type)
				{
					case 'controller':
						ajaxPromise = BX.ajax.runAction(actionConfig.name, ajaxOptions);

						break;

					case 'component':
						ajaxPromise = BX.ajax.runComponentAction(actionConfig.component, actionConfig.name, ajaxOptions);

						break;

					default:
					{
						const errorMessage = `Unknown action type: ${actionConfig.type}`;
						const error = new Error(errorMessage);
						error.errors = [{ message: errorMessage }];
						reject(error);

						return;
					}
				}

				ajaxPromise.then(resolve, reject);
			});

			this.handleSuccess(result);
		}
		catch (result)
		{
			this.handleError(result);
		}
	}

	onAfterActionRequest(): void
	{
	}

	handleSuccess(result: Result): void
	{
	}

	handleError(result: Result): void
	{
	}
}
