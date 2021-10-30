import Color from "./color";
import ColorValue from '../color_value';

export default class ButtonColor extends Color
{
	static COLOR_CONTRAST_VAR: string = '--button-color-contrast';
	static COLOR_HOVER_VAR: string = '--button-color-hover';
	static COLOR_LIGHT_VAR: string = '--button-color-light';
	static COLOR_VAR: string = '--button-color';

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.ButtonColor');
		this.property = 'background-color';
		// order is important! Base variable must be last. Hack :-/
		this.variableName = [
			ButtonColor.COLOR_CONTRAST_VAR,
			ButtonColor.COLOR_HOVER_VAR,
			ButtonColor.COLOR_LIGHT_VAR,
			ButtonColor.COLOR_VAR,
		];
		this.className = 'g-button-color';  //todo: ?
	}

	getStyle(): {string: ?string}
	{
		if (this.getValue() === null)
		{
			return {
				[ButtonColor.COLOR_CONTRAST_VAR]: null,
				[ButtonColor.COLOR_HOVER_VAR]: null,
				[ButtonColor.COLOR_LIGHT_VAR]: null,
				[ButtonColor.COLOR_VAR]: null,
			};
		}

		const value = this.getValue();
		const valueContrast = value.getContrast().lighten(10);
		const valueHover = new ColorValue(value).lighten(10);
		const valueLight = value.getLighten();

		return {
			[ButtonColor.COLOR_CONTRAST_VAR]: valueContrast.getStyleString(),
			[ButtonColor.COLOR_HOVER_VAR]: valueHover.getStyleString(),
			[ButtonColor.COLOR_LIGHT_VAR]: valueLight.getStyleString(),
			[ButtonColor.COLOR_VAR]: value.getStyleString(),
		};
	}
}