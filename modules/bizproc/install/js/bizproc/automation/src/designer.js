export class Designer
{
	static #instance;

	robotSettingsDialog;
	component;
	robot;
	triggerSettingsDialog;

	static getInstance()
	{
		if (!Designer.#instance)
		{
			Designer.#instance = new Designer();
		}

		return Designer.#instance;
	}

	setRobotSettingsDialog(dialog)
	{
		this.robotSettingsDialog = dialog;
		this.robot = dialog ? dialog.robot : null;
	}

	getRobotSettingsDialog()
	{
		return this.robotSettingsDialog;
	}

	setTriggerSettingsDialog(dialog)
	{
		this.triggerSettingsDialog = dialog;
	}

	getTriggerSettingsDialog()
	{
		return this.triggerSettingsDialog;
	}
}