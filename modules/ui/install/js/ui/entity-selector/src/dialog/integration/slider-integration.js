import { EventEmitter } from 'main.core.events';
import type { BaseEvent } from 'main.core.events';
import type Dialog from '../dialog';

export default class SliderIntegration
{
	dialog: Dialog = null;
	sliders: Set = new Set();

	constructor(dialog: Dialog)
	{
		this.dialog = dialog;

		this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
		this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
		this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));

		this.handleSliderOpen = this.handleSliderOpen.bind(this);
		this.handleSliderClose = this.handleSliderClose.bind(this);
	}

	getDialog(): Dialog
	{
		return this.dialog;
	}

	bindEvents()
	{
		this.unbindEvents();

		EventEmitter.subscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
		EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
		EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.handleSliderClose);
	}

	unbindEvents()
	{
		EventEmitter.unsubscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
		EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
		EventEmitter.unsubscribe('SidePanel.Slider:onDestroy', this.handleSliderClose);
	}

	handleDialogShow()
	{
		this.bindEvents();
	}

	handleDialogHide()
	{
		this.sliders.clear();
		this.unbindEvents();
		this.getDialog().unfreeze();
	}

	handleDialogDestroy()
	{
		this.sliders.clear();
		this.unbindEvents();
		this.getDialog().unfreeze();
	}

	handleSliderOpen(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		this.sliders.add(slider);

		this.getDialog().freeze();
	}

	handleSliderClose(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		this.sliders.delete(slider);

		if (this.sliders.size === 0)
		{
			this.getDialog().unfreeze();
		}
	}
}