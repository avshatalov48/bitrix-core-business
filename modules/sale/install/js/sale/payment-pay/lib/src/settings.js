export class Settings
{
	constructor(settings)
	{
		this.settings = settings;
	}

	get(name, defaultValue)
	{
		let parts = name.split('.');

		let currentOption = this.settings;
		let found = false;

		parts.map((part) => {
			if (currentOption && currentOption.hasOwnProperty(part))
			{
				currentOption = currentOption[part];
				found = true;
			}
			else
			{
				currentOption = null;
				found = false;
			}
		});

		return found ? currentOption : defaultValue;
	}
}