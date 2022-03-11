export class Tooltip
{
	static disabled = false;
	static tooltipsList =  {};

	static disable()
	{
		this.disabled = true;
	}

	static enable()
	{
		this.disabled = false;
	}

	static getDisabledStatus()
	{
		return this.disabled;
	}

	static getLoader()
	{
		return '/bitrix/tools/tooltip.php';
	}

	static getIdPrefix()
	{
		return 'bx-ui-tooltip-';
	}
}
