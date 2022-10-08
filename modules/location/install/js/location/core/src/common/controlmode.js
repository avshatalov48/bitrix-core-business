export default class ControlMode
{
	static get edit()
	{
		return 'edit';
	}

	static get view()
	{
		return 'view';
	}

	static isValid(mode: string)
	{
		return mode === ControlMode.edit || mode === ControlMode.view;
	}
}