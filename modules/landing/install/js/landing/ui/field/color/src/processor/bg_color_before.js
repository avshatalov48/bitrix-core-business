import BgColor from './bg_color';
import ColorValue from '../color_value';

export default class BgColorBefore extends BgColor
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorBefore');
		this.property = ['background-image', 'background-color'];
		this.variableName = '--bg--before';
		this.className = 'g-bg--before';
		this.pseudoClass = ':before';

		const opacityValue = this.getValue() || new ColorValue();
		this.opacity.setValue(opacityValue.setOpacity(0.5));
		this.tabs.showTab('Opacity');
	}
}
