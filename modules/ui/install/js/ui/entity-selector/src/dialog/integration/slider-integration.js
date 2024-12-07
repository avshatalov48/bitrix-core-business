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
		this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
	}

	getDialog(): Dialog
	{
		return this.dialog;
	}

	bindEvents(): void
	{
		this.unbindEvents();

		if (top.BX)
		{
			top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
			top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
			top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
		}
	}

	unbindEvents(): void
	{
		if (top.BX)
		{
			top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
			top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
			top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
		}
	}

	isDialogInSlider(slider): boolean
	{
		if (slider.getFrameWindow())
		{
			return slider.getFrameWindow().document.contains(this.getDialog().getContainer());
		}
		else
		{
			return  slider.getContainer().contains(this.getDialog().getContainer());
		}
	}

	handleDialogShow(): void
	{
		this.bindEvents();
	}

	handleDialogHide(): void
	{
		this.sliders.clear();
		this.unbindEvents();
		this.getDialog().unfreeze();
	}

	handleDialogDestroy(): void
	{
		this.sliders.clear();
		this.unbindEvents();
	}

	handleSliderOpen(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		if (!this.isDialogInSlider(slider))
		{
			this.sliders.add(slider);
			this.getDialog().freeze();
		}
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

	handleSliderDestroy(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		if (this.isDialogInSlider(slider))
		{
			this.unbindEvents();
			this.dialog.destroy();
		}
		else
		{
			this.sliders.delete(slider);
			if (this.sliders.size === 0)
			{
				this.getDialog().unfreeze();
			}
		}
	}
}
