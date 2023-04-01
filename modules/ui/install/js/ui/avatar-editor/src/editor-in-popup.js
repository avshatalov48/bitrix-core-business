import {Dom, Loc, Type, Cache, Tag, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Editor} from './editor';
import CameraTab from './tabs/camera-tab';
import UploadTab from './tabs/upload-tab';
import MaskTab from './tabs/mask-tab';
import {PopupManager, Popup} from 'main.popup';
import {CancelButton, SaveButton, ButtonTag} from 'ui.buttons';
import Backend from './backend';
import {ButtonState} from 'ui.buttons';

export default class EditorInPopup extends Editor
{
	#getPopup(): Popup
	{
		this.cache.remember('okButton', () => {
			const okButton = new SaveButton({
				onclick: () => {
					if (okButton.getState() === ButtonState.ACTIVE)
					{
						this.apply();
					}
					this.hide();
				}
			});

			if (this.isEmpty())
			{
				okButton.setState(ButtonState.DISABLED);
				this.subscribeOnce('onSet', () => {
					okButton.setState(ButtonState.ACTIVE);
				});
			}
			return okButton;
		});
		return this.cache.remember('popup', () => {
			return PopupManager.create(
				'popup_' + this.getId(),
				null,
				{
					className : "ui-avatar-editor__popup",
					autoHide : false,
					lightShadow : true,
					closeIcon : true,
					closeByEsc : true,
					titleBar : Loc.getMessage('JS_AVATAR_EDITOR_TITLE_BAR'),
					content: this.getContainer(),
					zIndex : BX.PopupWindowManager.getMaxZIndex() + 1,
					overlay : {},
					contentColor : "white",
					contentNoPaddings : true,
					bindOnResize: false,
					draggable: true,
					isScrollBlock: true,
					buttons: [
						this.cache.remember('okButton'),
						new CancelButton({
							onclick: () => {
								this.hide();
							} }),
					],
					events: {
						onShow: () => { this.emit('onOpen'); },
						onClose: () => { this.emit('onClose'); }
					}
				});
		});
	}

	hide()
	{
		this.#getPopup().close();
	}

	show(tabCode: ?String)
	{
		this.ready(() => {
				this.#getPopup().show();
				if (Type.isStringFilled(tabCode))
				{
					this.setActiveTab(tabCode);
				}
			})
		;
	}

	showFile(url: ?String)
	{
		this.ready(() => {
				this.#getPopup().show();
				if (url)
				{
					return this.loadSrc(url);
				}
			})
		;
	}

	apply()
	{
		this.packBlobAndMask()
			.then(({blob, maskedBlob, maskId, canvas}) =>
			{
				if (blob instanceof Blob)
				{
					if (maskId > 0)
					{
						Backend.useRecently(maskId);
					}
					const ev = new BaseEvent({
						compatData: [blob, canvas],
						data: {blob, maskedBlob}
					});
					EventEmitter.emit(this, 'onApply', ev, {useGlobalNaming: true});
					this.emit('onApply', ev);
				}
			})
			.catch((error) => {
				console.log('error: ', error);
			});
		;
	}

	onApply(callback): this
	{
		this.subscribe('onApply', callback);
		return this;
	}

	subscribeOnFormIsReady(fieldName, callback): Promise
	{
		this.subscribe('onApply', (event: BaseEvent) => {
			const formObj = new FormData();
			const {blob, maskedBlob} = event.getData();

			formObj.append(fieldName, blob, blob['name']);
			const maskedFileId = ['maskedFile', Editor.justANumber++].join(':');
			formObj.append( Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + fieldName, maskedFileId);
			if (maskedBlob)
			{
				formObj.append(Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + '[' + maskedFileId + ']', maskedBlob, blob['name']);
				formObj.append(Loc.getMessage('UI_AVATAR_MASK_REQUEST_FIELD_NAME') + '[' + maskedFileId + '][maskId]', maskedBlob['maskId']);
				callback(new BaseEvent({data: {form: formObj, blob, maskedBlob, maskId: maskedBlob['maskId']}}));
			}
			else
			{
				callback(new BaseEvent({data: {form: formObj, blob}}));
			}
		});
	}

	//region Compatibility
	click()
	{
		this.show();
	}

	get popup()
	{
		return this.#getPopup();
	}

	static isCameraAvailable(): boolean
	{
		return CameraTab.isAvailable();
	}
	//endregion
}
