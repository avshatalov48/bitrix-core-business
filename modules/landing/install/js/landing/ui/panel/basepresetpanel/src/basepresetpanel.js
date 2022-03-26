import {Content} from 'landing.ui.panel.content';
import {BaseEvent} from 'main.core.events';
import {BaseButton} from 'landing.ui.button.basebutton';
import {Loc} from 'landing.loc';
import {Cache, Dom, Tag, Type} from 'main.core';
import {PresetField} from 'landing.ui.field.presetfield';
import {PageObject} from 'landing.pageobject';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import PresetCategory from './preset-category/preset-category';
import Preset from './preset/preset';
import {Loader} from 'main.loader';
import ContentWrapper from './content-wrapper/content-wrapper';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class BasePresetPanel extends Content
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.BasePresetPanel');
		Dom.addClass(this.layout, 'landing-ui-panel-base-preset');
		Dom.addClass(this.overlay, 'landing-ui-panel-base-preset-overlay');

		this.cache = new Cache.MemoryCache();

		this.onSidebarButtonClick = this.onSidebarButtonClick.bind(this);
		this.onSaveClick = this.onSaveClick.bind(this);
		this.onCancelClick = this.onCancelClick.bind(this);
		this.onPresetFieldClick = this.onPresetFieldClick.bind(this);
		this.onPresetClick = this.onPresetClick.bind(this);
		this.onChange = this.onChange.bind(this);

		this.appendFooterButton(this.getSaveButton());
		this.appendFooterButton(this.getCancelButton());

		Dom.append(this.getHeaderControlsContainer(), this.header);
	}

	enableToggleMode()
	{
		this.cache.set('toggleMode', true);
		this.renderTo(this.getViewContainer());
	}

	isToggleModeEnabled(): boolean
	{
		return this.cache.get('toggleMode') === true;
	}

	disableOverlay()
	{
		Dom.addClass(this.overlay, 'landing-ui-panel-base-preset-disable-overlay');
	}

	getViewContainer(): HTMLDivElement
	{
		return this.cache.remember('viewContainer', () => {
			const rootWindow = PageObject.getRootWindow();
			return rootWindow.document.querySelector('.landing-ui-view-container');
		});
	}

	getViewWrapper(): HTMLDivElement
	{
		return this.cache.remember('viewWrapper', () => {
			return this.getViewContainer().querySelector('.landing-ui-view-wrapper');
		});
	}

	getSaveButton(): BaseButton
	{
		return this.cache.remember('saveButton', () => {
			const button = new BaseButton('save_settings', {
				text: Loc.getMessage('BLOCK_SAVE'),
				onClick: this.onSaveClick,
				className: 'ui-btn ui-btn-success',
				attrs: {title: Loc.getMessage('LANDING_TITLE_OF_SLIDER_SAVE')},
			});

			Dom.removeClass(button.layout, 'landing-ui-button');

			return button;
		});
	}

	// eslint-disable-next-line
	onSaveClick()
	{

	}

	getCancelButton(): BaseButton
	{
		return this.cache.remember('cancelButton', () => {
			return new BaseButton('cancel_settings', {
				text: Loc.getMessage('BLOCK_CANCEL'),
				onClick: this.onCancelClick,
				className: 'landing-ui-button-content-cancel',
				attrs: {title: Loc.getMessage('LANDING_TITLE_OF_SLIDER_CANCEL')},
			});
		});
	}

	// eslint-disable-next-line
	onCancelClick()
	{

	}

	appendSidebarButton(button)
	{
		super.appendSidebarButton(button);
	}

	// eslint-disable-next-line
	onSidebarButtonClick(event: BaseEvent)
	{
		const activeButton = this.sidebarButtons.getActive();
		if (activeButton)
		{
			activeButton.deactivate();
		}

		event.getTarget().activate();

		Dom.addClass(this.content, 'landing-ui-panel-base-preset-fade');
		this.showContentLoader();

		void this.getContent(event.getTarget().id)
			.then((content) => {
				if (content)
				{
					setTimeout(() => {
						Dom.removeClass(this.content, 'landing-ui-panel-base-preset-fade');
						this.clearContent();
						this.hideContentLoader();
						content.subscribe('onChange', this.onChange);
						Dom.append(content.getLayout(), this.content);
					}, 300);
				}
				else
				{
					Dom.removeClass(this.content, 'landing-ui-panel-base-preset-fade');
					this.clearContent();
					this.hideContentLoader();
				}
			});
	}

	onChange(event: BaseEvent)
	{

	}

	// eslint-disable-next-line
	getContent(id: string): Promise<ContentWrapper>
	{
		throw new Error('Must be implemented in child class');
	}

	getHeaderControlsContainer(): HTMLDivElement
	{
		return this.cache.remember('headerControlsContainer', () => {
			return Tag.render`
				<div class="landing-ui-panel-base-preset-header-controls">
					${this.getLeftHeaderControls()}
					${this.getRightHeaderControls()}
				</div>
			`;
		});
	}

	getRightHeaderControls(): HTMLDivElement
	{
		return this.cache.remember('rightHeaderControls', () => {
			return Tag.render`<div class="landing-ui-panel-base-preset-header-controls-right"></div>`;
		});
	}

	getLeftHeaderControls(): HTMLDivElement
	{
		return this.cache.remember('leftHeaderControls', () => {
			return Tag.render`
				<div class="landing-ui-panel-base-preset-header-controls-left">
					${this.getPresetField().getNode()}
				</div>
			`;
		});
	}

	getPresetField(): PresetField
	{
		return this.cache.remember('presetField', () => {
			return new PresetField({
				events: {
					onClick: this.onPresetFieldClick,
				},
			});
		});
	}

	show(options: any): Promise<any>
	{
		if (this.isToggleModeEnabled())
		{
			const contentEditPanel = BX.Landing.UI.Panel.ContentEdit;
			if (contentEditPanel.showedPanel)
			{
				contentEditPanel.showedPanel.hide();
			}

			const viewWrapper = this.getViewWrapper();
			Dom.style(viewWrapper, 'transition', '400ms margin ease');

			setTimeout(() => {
				Dom.style(viewWrapper, 'margin-left', '880px');
			});
		}

		return super.show(options);
	}

	hide(): Promise<any>
	{
		const viewWrapper = this.getViewWrapper();
		if (this.isToggleModeEnabled())
		{
			Dom.style(viewWrapper, 'margin-left', null);
		}

		return super.hide()
			.then(() => {
				if (this.isToggleModeEnabled())
				{
					Dom.style(viewWrapper, 'transition', null);
				}
			});
	}

	enableTransparentMode()
	{
		Dom.addClass(this.layout, 'landing-ui-panel-mode-transparent');
	}

	disableTransparentMode()
	{
		Dom.removeClass(this.layout, 'landing-ui-panel-mode-transparent');
	}

	setCategories(categories: Array<PresetCategory>)
	{
		this.cache.set('categories', categories);
		this.cache.delete('renderedPresets');
	}

	getCategories(): Array<PresetCategory>
	{
		return this.cache.get('categories');
	}

	setPresets(presets: Array<Preset>)
	{
		presets.forEach((preset) => {
			preset.unsubscribe('onClick', this.onPresetClick);
			preset.subscribe('onClick', this.onPresetClick);
		});

		this.cache.set('presets', presets);
		this.cache.delete('renderedPresets');
	}

	getPresets(): Array<Preset>
	{
		return this.cache.get('presets');
	}

	setSidebarButtons(buttons: Array<SidebarButton>)
	{
		buttons.forEach((button) => {
			button.subscribe('onClick', this.onSidebarButtonClick);
		});
		this.cache.set('sidebarButtons', buttons);
	}

	getSidebarButtons(): Array<SidebarButton>
	{
		return this.cache.get('sidebarButtons');
	}

	onPresetFieldClick()
	{
		this.clear();
		this.enableTransparentMode();

		this.getCategories().forEach((category) => {
			const presets = this.getPresets().filter((preset) => {
				return preset.options.category === category.options.id;
			});

			category.setPresets(presets);

			Dom.append(category.getLayout(), this.content);

			this.getPresets().forEach((preset) => {
				preset.getTextCrop().init();
			});
		});
	}

	onPresetClick(event: BaseEvent)
	{
		this.disableTransparentMode();
		this.applyPreset(event.getTarget());
	}

	activatePreset(presetId: string)
	{
		const preset = this.getPresets().find((currentPreset) => {
			return currentPreset.options.id === presetId;
		});

		const presetField = this.getPresetField();
		presetField.setLinkText(preset.options.title);
		presetField.setIcon(preset.options.icon);

		preset.activate();
	}

	// eslint-disable-next-line no-unused-vars
	applyPreset(preset: Preset, skipOptions = false)
	{
		this.clear();

		const presetField = this.getPresetField();
		presetField.setLinkText(preset.options.title);
		presetField.setIcon(preset.options.icon);

		const buttons = this.getSidebarButtons().filter((button) => {
			return preset.options.items.includes(button.id);
		});

		buttons.forEach((button) => {
			button.deactivate();
			this.appendSidebarButton(button);
		});

		if (Type.isStringFilled(preset.options.defaultSection))
		{
			const defaultSectionButton = buttons.find((button) => {
				return button.id === preset.options.defaultSection;
			});

			if (defaultSectionButton)
			{
				defaultSectionButton.activate();
				defaultSectionButton.layout.click();
			}
		}
		else
		{
			const [firstButton] = buttons;
			firstButton.activate();
			firstButton.layout.click();
		}
	}

	getContentLoader(): Loader
	{
		return this.cache.remember('contentLoader', () => {
			return new Loader({
				target: this.body,
				offset: {
					left: '130px',
				},
			});
		});
	}

	showContentLoader()
	{
		void this.getContentLoader().show();
	}

	hideContentLoader()
	{
		void this.getContentLoader().hide();
	}
}

export {
	PresetCategory,
	Preset,
	ContentWrapper,
};