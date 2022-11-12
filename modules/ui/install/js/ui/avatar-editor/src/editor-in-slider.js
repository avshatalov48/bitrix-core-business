import {Dom, Loc, Type, Cache, Tag, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import EditorInPopup from './editor-in-popup';
import CameraTab from './tabs/camera-tab';
import UploadTab from './tabs/upload-tab';
import MaskTab from './tabs/mask-tab';
import {PopupManager, Popup} from 'main.popup';
import {CancelButton, SaveButton, ButtonTag} from 'ui.buttons';
import Backend from './backend';
import {ButtonState} from 'ui.buttons';
import MaskEditor from './mask-tool/mask-editor';
import {Layout} from "ui.sidepanel.layout";

export default class EditorInSlider extends EditorInPopup
{
	#id: String = 'editor-in-slider';

	init()
	{
		super.init();
		MaskEditor.subscribe('onOpen', (baseEvent: BaseEvent) => {
			this.hide();
		});
	}

	#showSlider(): Promise
	{
		return new Promise((resolve, reject) => {
				BX.SidePanel.Instance.open(
					this.#id, {
					// width: 800,
					cacheable: true,
					allowChangeHistory: false,
					events: {
						onCloseByEsc: (event) => {
							event.denyAction();
						},
						onOpen: () => {
							setTimeout(() => {
								this.emit('onOpen', {});
							}, 0);
							resolve();
						},
						onClose: () => {
							this.emit('onClose');
						}
					},
					contentCallback: (slider) => {
						return Layout.createContent({
							extensions: [],
							title: Loc.getMessage('JS_AVATAR_EDITOR_TITLE_BAR'),
							content: () => {
								return Tag.render`<div class="ui-avatar-editor__popup">${this.getContainer()}</div>`
							},
							buttons: ({CancelButton, SaveButton}) => {
								const SB = new SaveButton({
									onclick: (button) => {
										if (SB.getState() === ButtonState.DISABLED)
										{
											return;
										}

										this.apply();
										slider.close();
									}
								});
								if (this.isEmpty())
								{
									SB.setState(ButtonState.DISABLED);
									this.subscribeOnce('onSet', () => {
										SB.setState(ButtonState.ACTIVE);
									});
								}

								return [
									SB,
									new CancelButton({
										onclick: () => {
											this.hide();
											slider.close();
										}
									})
								];
							}
						});
					},
					label: {
						text: Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_LABEL'),
					}
				});
			})
	}

	show(tabCode: ?String)
	{
		this
			.#showSlider()
			.then(() => {
				if (Type.isStringFilled(tabCode))
				{
					this.setActiveTab(tabCode);
				}
			});
	}

	showFile(url: ?String)
	{
		this
			.#showSlider()
			.then(() => {
				this.loadSrc(url);
			})
		;
	}

	//region Compatibility
	click()
	{
		this.show();
	}

	get popup()
	{
		return {};
	}
	//endregion
}
