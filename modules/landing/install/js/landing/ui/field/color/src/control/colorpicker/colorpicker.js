import {Event, Tag, Text, Loc} from 'main.core';
import {Popup, PopupManager} from 'main.popup';
import {BaseEvent} from "main.core.events";

import Hex from '../hex/hex';
import ColorValue from "../../color_value";
import BaseControl from "../base_control/base_control";
import Spectrum from "../spectrum/spectrum";
import Recent from '../../layout/recent/recent';

import './css/colorpicker.css';

export default class Colorpicker extends BaseControl
{
	popupId: string;
	popupTargetContainer: ?HTMLElement;

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Colorpicker');
		this.popupId = 'colorpicker_popup_' + Text.getRandom();
		this.popupTargetContainer = options.contentRoot;

		this.hexPreview = new Hex();
		this.hexPreview.setPreviewMode(true);
		Event.bind(this.hexPreview.getLayout(), 'click', this.onPopupOpenClick.bind(this));

		// popup
		this.hex = new Hex();
		this.hex.subscribe('onChange', this.onHexChange.bind(this));
		this.hex.subscribe('onButtonClick', this.onSelectClick.bind(this));

		this.spectrum = new Spectrum(options);
		this.spectrum.subscribe('onChange', this.onSpectrumChange.bind(this));

		this.recent = new Recent();
		this.recent.subscribe('onChange', this.onRecentChange.bind(this));

		Event.bind(this.getCancelButton(), 'click', this.onCancelClick.bind(this));
		Event.bind(this.getSelectButton(), 'click', this.onSelectClick.bind(this));
		// end popup

		this.previously = this.getValue();
	}

	onSelectClick(event: ?BaseEvent)
	{
		const value = (event instanceof BaseEvent) ? event.getData().color : this.getValue();
		if (value !== null)
		{
			this.recent.addItem(this.getValue().getHex());
		}
		this.getPopup().close();
	}

	buildLayout(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-colorpicker">
				${this.hexPreview.getLayout()}
			</div>
		`;
	}

	getPopupContent(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-popup-container">
				<div class="landing-ui-field-color-popup-head">
					${this.recent.getLayout()}
					${this.hex.getLayout()}
				</div>
				${this.spectrum.getLayout()}
				<div class="landing-ui-field-color-popup-footer">
					${this.getSelectButton()}
					${this.getCancelButton()}
				</div>
			</div>
		`;
	}

	getSelectButton(): HTMLButtonElement
	{
		return this.cache.remember('selectButton', () => {
			return Tag.render`
				<button class="ui-btn ui-btn-xs ui-btn-primary">
					${Loc.getMessage('LANDING_FIELD_COLOR-BUTTON_SELECT')}
				</button>
			`;
		});
	}

	getCancelButton(): HTMLButtonElement
	{
		return this.cache.remember('cancelButton', () => {
			return Tag.render`
				<button class="ui-btn ui-btn-xs ui-btn-light-border">
					${Loc.getMessage('LANDING_FIELD_COLOR-BUTTON_CANCEL')}
				</button>
			`;
		});
	}

	getHexPreviewObject(): Hex
	{
		return this.hexPreview;
	}

	getPopup(): Popup
	{
		return this.cache.remember('popup', () => {
			return PopupManager.create({
				id: this.popupId,
				className: 'landing-ui-field-color-spectrum-popup',
				autoHide: true,
				bindElement: this.hexPreview.getLayout(),
				bindOptions: {
					forceTop: true,
					forceLeft: true,
				},
				padding: 0,
				contentPadding: 14,
				width: 260,
				offsetTop: -37,
				offsetLeft: -180,
				content: this.getPopupContent(),
				closeByEsc: true,
				targetContainer: this.popupTargetContainer,
			});
		});
	}

	getValue(): ?ColorValue
	{
		return this.cache.remember('value', () => {
			return this.spectrum.getValue();
		});
	}

	onHexChange(event: BaseEvent)
	{
		this.setValue(event.getData().color);
		this.onChange(event);
	}

	onSpectrumChange(event: BaseEvent)
	{
		this.hex.unFocus();
		this.setValue(event.getData().color);
		this.onChange(event);
	}

	onRecentChange(event: BaseEvent)
	{
		const recentColor = new ColorValue(event.getData().hex);
		this.setValue(recentColor);
		this.onChange(new BaseEvent({data: {color: recentColor}}));
	}

	onCancelClick()
	{
		this.setValue(this.previously);
		this.getPopup().close();
		this.onChange(new BaseEvent({data: {color: this.getValue()}}));
	}

	onPopupOpenClick()
	{
		this.recent.buildItemsLayout();
		this.previously = this.getValue();
		this.getPopup().show();
		if (this.getPopup().isShown())
		{
			this.hex.focus();
		}
	}

	setValue(value: ?ColorValue)
	{
		if (this.isNeedSetValue(value))
		{
			super.setValue(value);

			this.spectrum.setValue(value);
			this.hex.setValue(value);
			this.hexPreview.setValue(value);
		}
		this.setActivity(value);
	}

	setActivity(value: ?ColorValue)
	{
		if (value !== null)
		{
			if (this.spectrum.isActive())
			{
				this.hex.unsetActive();
			}
			else
			{
				this.hex.setActive();
			}
			this.hexPreview.setActive();
		}
	}

	unsetActive(): void
	{
		this.hex.unsetActive();
		this.hexPreview.unsetActive();
	}

	isActive(): boolean
	{
		return this.hex.isActive() || this.hexPreview.isActive();
	}
}
