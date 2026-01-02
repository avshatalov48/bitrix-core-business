import { Dom, Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { SplitButton, ButtonSize, ButtonColor } from 'ui.buttons';
import { SwitcherColor } from 'ui.switcher';

type Params = {
	isShortView: 'Y' | 'N'
}

export class ShortView extends EventEmitter
{
	constructor(params: Params)
	{
		super(params);

		this.setEventNamespace('BX.UI.ShortView');

		this.setShortView(params.isShortView);

		this.node = null;
	}

	renderTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('UI ShortView: HTMLElement not found');
		}

		Dom.append(this.render(), container);
	}

	render(): HTMLElement
	{
		const checked = (this.getShortView() === 'Y');

		this.node = new SplitButton({
			text: Loc.getMessage('UI_SHORT_VIEW_LABEL'),
			round: true,
			size: ButtonSize.SMALL,
			color: ButtonColor.LIGHT_BORDER,
			className: 'ui-btn-themes',
			mainButton: {
				onclick: (button: SplitButton, event: MouseEvent) => {
					event.preventDefault();
					this.node.getSwitcher().toggle();
				},
			},
			switcher: {
				checked,
				color: SwitcherColor.primary,
				handlers: {
					toggled: () => this.onChange(),
				},
			},
		});

		return this.node.render();
	}

	setShortView(value: string)
	{
		this.shortView = (value === 'Y' ? 'Y' : 'N');
	}

	getShortView(): 'Y' | 'N'
	{
		return this.shortView;
	}

	onChange()
	{
		this.setShortView(this.node.getSwitcher().isChecked() ? 'Y' : 'N');

		this.emit('change', this.getShortView());
	}
}
