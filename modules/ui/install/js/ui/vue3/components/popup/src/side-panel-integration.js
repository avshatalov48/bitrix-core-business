import { Popup as MainPopup } from 'main.popup';
import type { BaseEvent } from 'main.core.events';
import { Popup } from './popup';

export class SidePanelIntegration
{
	popup: Popup = null;
	sliders: Set = new Set();

	constructor(popup: Popup)
	{
		this.popup = popup;

		this.getPopup().subscribe('onShow', this.onPopupShow.bind(this));
		this.getPopup().subscribe('onClose', this.onPopupClose.bind(this));
		this.getPopup().subscribe('onDestroy', this.onPopupClose.bind(this));

		this.handleSliderOpen = this.handleSliderOpen.bind(this);
		this.handleSliderClose = this.handleSliderClose.bind(this);
		this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
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

	onPopupShow(): void
	{
		this.bindEvents();
	}

	onPopupClose(): void
	{
		this.unbindEvents();
		this.sliders.clear();
		this.popup.unfreeze();
	}

	handleSliderOpen(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		if (!this.isPopupInSlider(slider))
		{
			this.sliders.add(slider);
			this.popup.freeze();
		}
	}

	handleSliderClose(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		this.sliders.delete(slider);
		if (this.sliders.size === 0)
		{
			this.popup.unfreeze();
		}
	}

	handleSliderDestroy(event: BaseEvent): void
	{
		const [sliderEvent] = event.getData();
		const slider = sliderEvent.getSlider();

		if (this.isPopupInSlider(slider))
		{
			this.unbindEvents();
			this.getPopup().destroy();
		}
		else
		{
			this.sliders.delete(slider);
			if (this.sliders.size === 0)
			{
				this.popup.unfreeze();
			}
		}
	}

	isPopupInSlider(slider): boolean
	{
		if (slider.getFrameWindow())
		{
			return slider.getFrameWindow().document.contains(this.getPopup().getPopupContainer());
		}

		return slider.getContainer().contains(this.getPopup().getPopupContainer());
	}

	getPopup(): MainPopup
	{
		return this.popup.getPopupInstance();
	}
}
