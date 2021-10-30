import BgColor from './bg_color';

import ColorValue from '../color_value';

export default class BgColorAfter extends BgColor
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorAfter');
		this.property = ['background-image', 'background-color'];
		this.variableName = '--bg--after';
		this.className = 'g-bg--after';
		this.pseudoClass = ':after';

		const opacityValue = this.getValue() || new ColorValue();
		this.opacity.setValue(opacityValue.setOpacity(0.5));
		this.tabs.showTab('Opacity');
	}
}
