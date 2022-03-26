import {Tag, Text, Dom, Event, Type, Cache, Loc} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Popup, PopupManager} from 'main.popup';

import ColorValue from '../../color_value';
import GradientValue from '../../gradient_value';
import Generator from "./generator";
import './css/preset_collection.css';
import {PresetOptions} from "./types/preset-options";
import Preset from "./preset";

export default class PresetCollection extends EventEmitter
{
	static globalActiveId: number | string | null = null;

	popupId: string;
	popupTargetContainer: ?HTMLElement;
	presets: {
		[id: string]: PresetOptions
	} = {};
	activeId: number | string | null = null;

	static ACTIVE_CLASS: string = 'active';

	constructor(options)
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.PresetCollection');
		this.popupId = 'presets-popup_' + Text.getRandom();
		this.popupTargetContainer = options.contentRoot;

		this.onPresetClick = this.onPresetClick.bind(this);
		Event.bind(this.getOpenButton(), 'click', () => {
			this.getPopup().toggle();
		});
		this.onPresetChangeGlobal = this.onPresetChangeGlobal.bind(this);
		EventEmitter.subscribe('BX.Landing.UI.Field.Color.PresetCollection:onChange', this.onPresetChangeGlobal);
	}

	addDefaultPresets()
	{
		this.addPreset(Generator.getPrimaryColorPreset());
		Generator.getDefaultPresets().map((item) => {
			this.addPreset(item);
		});
	}

	addPreset(options: PresetOptions)
	{
		this.cache.delete('popupLayout');
		if (!Object.keys(this.presets).length || !(options.id in this.presets))
		{
			this.presets[options.id] = options;
		}
	}

	getGlobalActiveId(): number | string | null
	{
		return PresetCollection.globalActiveId;
	}

	getActiveId(): number | string
	{
		return this.getGlobalActiveId() || this.getDefaultPreset().getId();
	}

	getActivePreset(): Preset
	{
		return this.getPresetById(this.getActiveId());
	}

	getDefaultPreset(): ?Preset
	{
		return Object.keys(this.presets).length
			? this.getPresetById(Object.keys(this.presets)[0])
			: null;
	}

	getPresetById(id: number | string): ?Preset
	{
		if (id in this.presets)
		{
			return this.cache.remember(id, () => new Preset(this.presets[id]));
		}
		else
		{
			return null;
		}
	}

	getPresetByItemValue(value: ColorValue | GradientValue | null): ?Preset
	{
		if (value === null)
		{
			return null;
		}

		for (let id in this.presets)
		{
			const preset = this.getPresetById(id);
			if (preset && value instanceof ColorValue)
			{
				if (preset.isPresetValue(value))
				{
					return preset;
				}
			}
			else if (preset && value instanceof GradientValue)
			{
				if (preset.getGradientPreset().isPresetValue(value))
				{
					return preset;
				}
			}
		}
		return null;
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('value', () => {
			return Tag.render`
				<div class="landing-ui-field-color-presets">
					<div class="landing-ui-field-color-presets-left">
						<span class="landing-ui-field-color-presets-title">
							${Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_TITLE')}
						</span>
					</div>
					<div class="landing-ui-field-color-presets-right">${this.getOpenButton()}</div>
				</div>
			`;
		});
	}

	getOpenButton(): HTMLDivElement
	{
		return this.cache.remember('openButton', () => {
			return Tag.render`<span class="landing-ui-field-color-presets-open">
				${Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_MORE')}
			</span>`;
		});
	}

	getTitleContainer(): HTMLDivElement
	{
		return this.cache.remember('titleContainer', () => {
			return this.getLayout().querySelector('.landing-ui-field-color-presets-left');
		});
	}

	getPopup(): Popup
	{
		// todo: bind to event target? or need button
		return this.cache.remember('popup', () => {
			return PopupManager.create({
				id: this.popupId,
				className: 'presets-popup',
				autoHide: true,
				bindElement: this.getOpenButton(),
				bindOptions: {
					forceTop: true,
					forceLeft: true,
				},
				width: 280,
				offsetLeft: -200,
				content: this.getPopupLayout(),
				closeByEsc: true,
				targetContainer: this.popupTargetContainer,
			});
		});
	}

	getPopupLayout(): HTMLDivElement
	{
		return this.cache.remember('popupLayout', () => {
			const layouts = Tag.render`<div class="landing-ui-field-color-presets-popup">
				<div class="landing-ui-field-color-presets-popup-title">
					${Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_MORE_COLORS')}
				</div>
				<div class="landing-ui-field-color-presets-popup-inner"></div>
			</div>`;
			const innerLayouts = layouts.querySelector('.landing-ui-field-color-presets-popup-inner');
			for (const presetId in this.presets)
			{
				const layout = this.getPresetLayout(presetId);
				if (presetId === this.getActiveId())
				{
					Dom.addClass(layout, PresetCollection.ACTIVE_CLASS);
					this.activeId = presetId;
				}
				Event.bind(layout, 'click', this.onPresetClick);
				Dom.append(layout, innerLayouts);
			}

			return layouts;
		});
	}

	getPresetLayout(presetId: string | number): HTMLDivElement
	{
		return this.cache.remember(presetId + 'layout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-presets-preset" data-id="${presetId}">
					${
						this.presets[presetId].items.map((item) => {
							return Tag.render`<div
								class="landing-ui-field-color-presets-preset-item"
								style="background: ${Type.isString(item) ? item : item.getStyleString()}"
							></div>`;
						})
					}
				</div>
			`;
		});
	}

	onPresetClick(event: MouseEvent)
	{
		this.getPopup().close();
		this.setActiveItem(event.currentTarget.dataset.id);
		this.emit('onChange', {presetId: this.getActiveId()});
	}

	onPresetChangeGlobal(event: BaseEvent)
	{
		if (event.getData().presetId !== this.activeId)
		{
			this.setActiveItem(event.getData().presetId);
			this.emit('onChange', event);
		}
	}

	setActiveItem(presetId: string)

	{
		if (
			presetId !== null
			&& presetId !== this.activeId
		)
		{
			PresetCollection.globalActiveId = presetId;
			this.activeId = presetId;
			for (const id in this.presets)
			{
				Dom.removeClass(this.getPresetLayout(id), PresetCollection.ACTIVE_CLASS);
				if (id === presetId)
				{
					Dom.addClass(this.getPresetLayout(id), PresetCollection.ACTIVE_CLASS);
				}
			}
		}
	}

	unsetActive()
	{
		for (const presetId in this.presets)
		{
			Dom.removeClass(this.getPresetLayout(presetId), PresetCollection.ACTIVE_CLASS);
		}
	}
}
