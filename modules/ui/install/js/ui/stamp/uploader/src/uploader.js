import {EventEmitter, BaseEvent} from 'main.core.events';
import {Type, Cache, Tag, Dom, Reflection, Loc, Event} from 'main.core';
import {Uploader as FileUploader, UploaderFile, FileEvent, UploaderEvent} from 'ui.uploader.core';
import 'ui.dialogs.messagebox';
import {Layout} from 'ui.sidepanel.layout';
import {Button} from 'ui.buttons';
import Header from './header/header';
import UploadLayout from './upload-layout/upload-layout';
import Dropzone from './dropzone/dropzone';
import ActionPanel from './action-panel/action-panel';
import Status from './status/status';
import Preview from './preview/preview';
import Message from './message/message';
import FileSelect from './file-select/file-select';

import './css/style.css';

export type ContactItem = {
	id: number | string,
	label: string,
	href?: string,
};

export type UploaderOptions = {
	controller: {
		upload: string,
	},
	mode: $Values<Uploader.Mode>,
	contact: ContactItem,
	contactsList: Array<ContactItem>,
	events: {
		[key: string]: (event: BaseEvent) => void,
	},
};

/**
 * @namespace BX.UI.Stamp
 */
export class Uploader extends EventEmitter
{
	static Mode = {
		SLIDER: 'slider',
		INLINE: 'inline',
	};

	cache = new Cache.MemoryCache();

	constructor(options: UploaderOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		this.cache.remember('fileUploader', () => {
			const dropzoneLayout = this.getDropzone().getLayout();
			const previewLayout = this.getPreview().getLayout();
			const fileSelectButtonLayout = this.getFileSelect().getLayout();

			Event.bind(previewLayout, 'click', (event: MouseEvent) => {
				if (this.getPreview().isCropEnabled())
				{
					event.stopImmediatePropagation();
				}
			});

			return new FileUploader({
				controller: this.getOptions().controller.upload,
				assignAsFile: true,
				browseElement: [
					dropzoneLayout,
					previewLayout,
					fileSelectButtonLayout,
					this.getHiddenInput(),
				],
				dropElement: [
					dropzoneLayout,
					previewLayout,
				],
				imagePreviewHeight: 556,
				imagePreviewWidth: 1000,
				autoUpload: false,
				acceptedFileTypes: ['image/png', 'image/jpeg'],
				events: {
					[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
						const {file, error} = event.getData();

						if (Type.isNil(error))
						{
							this.getPreview().show(file.getClientPreview());
							this.setUploaderFile(file);

							if (this.getMode() === Uploader.Mode.SLIDER)
							{
								this.getSliderButtons().saveButton.setDisabled(false);
								this.getActionPanel().enable();
							}

							if (this.getMode() === Uploader.Mode.INLINE)
							{
								this.getInlineSaveButton().setDisabled(false);
								this.getActionPanel().enable();
							}

							this.setIsChanged(true);
						}
					},
					[UploaderEvent.FILE_UPLOAD_PROGRESS]: (event: BaseEvent) => {
						const {progress, file} = event.getData();

						this.getStatus().updateUploadStatus({
							percent: progress,
							size: (file.getSize() / 100) * progress,
						});
					},
					[UploaderEvent.FILE_ERROR]: function(event: BaseEvent) {
						const {error} = event.getData();
						Uploader.showAlert(error.getMessage());
					},
				},
			});
		});
	}

	static showAlert(...args)
	{
		const TopMessageBox = Reflection.getClass('top.BX.UI.Dialogs.MessageBox');
		if (!Type.isNil(TopMessageBox))
		{
			TopMessageBox.alert(...args);
		}
	}

	static showConfirm(options: {[key: string]: any})
	{
		const TopMessageBox = Reflection.getClass('top.BX.UI.Dialogs.MessageBox');
		const TopMessageBoxButtons = Reflection.getClass('top.BX.UI.Dialogs.MessageBoxButtons');
		if (!Type.isNil(TopMessageBox))
		{
			TopMessageBox.show({
				modal: true,
				buttons: TopMessageBoxButtons.OK_CANCEL,
				...options,
			});
		}
	}

	setIsChanged(value: boolean)
	{
		this.cache.set('isChanged', value);
	}

	isChanged(): boolean
	{
		return this.cache.get('isChanged', false);
	}

	static #delay(callback: () => void, delay: number)
	{
		const timeoutId = setTimeout(() => {
			callback();
			clearTimeout(timeoutId);
		}, delay);
	}

	getFileUploader(): FileUploader
	{
		return this.cache.get('fileUploader');
	}

	setUploaderFile(file: UploaderFile)
	{
		this.cache.set('uploaderFile', file);
	}

	getUploaderFile(): UploaderFile
	{
		return this.cache.get('uploaderFile', null);
	}

	setOptions(options: UploaderOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): UploaderOptions
	{
		return this.cache.get('options', {});
	}

	getMode(): $Values<Uploader.Mode>
	{
		const {mode} = this.getOptions();
		if (Object.values(Uploader.Mode).includes(mode))
		{
			return mode;
		}

		return Uploader.Mode.SLIDER;
	}

	getHeader(): Header
	{
		return this.cache.remember('header', () => {
			return new Header(this.getOptions());
		});
	}

	getPreview(): Preview
	{
		return this.cache.remember('preview', () => {
			return new Preview({});
		});
	}

	getFileSelect(): FileSelect
	{
		return this.cache.remember('fileSelect', () => {
			return new FileSelect({
				events: {
					onTakePhotoClick: () => {
						this.emit('onTakePhotoClick');
					},
					onSelectPhotoClick: () => {

					},
				},
			});
		});
	}

	getUploadLayout(): UploadLayout
	{
		return this.cache.remember('uploadLayout', () => {
			return new UploadLayout({
				children: [
					(() => {
						if (this.getMode() === Uploader.Mode.INLINE)
						{
							return this.getFileSelect();
						}

						return this.getDropzone();
					})(),
					this.getActionPanel(),
					this.getStatus(),
					this.getPreview(),
				],
			});
		});
	}

	getDropzone(): Dropzone
	{
		return this.cache.remember('dropzone', () => {
			return new Dropzone({});
		});
	}

	getActionPanel(): ActionPanel
	{
		return this.cache.remember('actionPanel', () => {
			return new ActionPanel({
				events: {
					onCropClick: this.onCropClick.bind(this),
					onApplyClick: this.onCropApplyClick.bind(this),
					onCancelClick: this.onCropCancelClick.bind(this),
				},
			});
		});
	}

	onCropApplyClick()
	{
		this.getPreview().applyCrop();
		this.getPreview().disableCrop();
		this.getActionPanel().hideCropActions();
		this.getInlineSaveButton().setDisabled(false);
		this.getActionPanel().enable();
	}

	onCropCancelClick()
	{
		this.getPreview().disableCrop();
		this.getActionPanel().hideCropActions();
		this.getInlineSaveButton().setDisabled(false);
		this.getActionPanel().enable();
	}

	onCropClick()
	{
		this.getPreview().enableCrop();
		this.getActionPanel().showCropAction();
		this.getInlineSaveButton().setDisabled(true);
		this.getActionPanel().enable();
	}

	getStatus(): Status
	{
		return this.cache.remember('status', () => {
			return new Status();
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			const mode = this.getMode();
			return Tag.render`
				<div class="ui-stamp-uploader ui-stamp-uploader-mode-${mode}">
					${(() => {
						if (mode === Uploader.Mode.SLIDER)
						{
							return this.getMessage().getLayout();
						}
		
						return '';
					})()}
					${this.getHeader().getLayout()}
					${this.getUploadLayout().getLayout()}
					${(() => {
						if (mode === Uploader.Mode.INLINE)
						{
							return Tag.render`
								<div class="ui-stamp-uploader-footer">
									${this.getInlineSaveButton().render()}
								</div>
							`;
						}
				
						return '';
					})()}
					${this.getHiddenInput()}
				</div>
			`;
		});
	}

	getHiddenInput(): HTMLInputElement
	{
		return this.cache.remember('hiddenInput', () => {
			return Tag.render`
				<input type="file" name="STAMP_UPLOADER_INPUT" hidden>
			`;
		});
	}

	renderTo(target: HTMLElement)
	{
		if (Type.isDomNode(target))
		{
			Dom.append(this.getLayout(), target);
		}
	}

	upload(): Promise<UploaderFile>
	{
		return new Promise((resolve) => {
			this.getPreview().getFile().then((blob) => {
				this.getFileUploader().addFile(blob);
				const [resultFile] = this.getFileUploader().getFiles();

				resultFile.subscribeOnce(FileEvent.LOAD_COMPLETE, () => {
					this.getPreview().hide();
					this.getStatus().showUploadStatus({reset: true});

					resultFile.upload({
						onComplete: () => {
							resolve(resultFile);
						},
						onError: console.error,
					});
				});
			});
		});
	}

	getMessage(): Message
	{
		return this.cache.remember('message', () => {
			return new Message();
		});
	}

	getInlineSaveButton(): Button
	{
		return this.cache.remember('inlineSaveButton', () => {
			const button = new Button({
				text: Loc.getMessage('UI_STAMP_UPLOADER_SAVE_BUTTON_LABEL'),
				color: Button.Color.PRIMARY,
				size: Button.Size.LARGE,
				round: true,
				onclick: () => {
					const saveButton = this.getInlineSaveButton();
					saveButton.setWaiting(true);

					this.upload()
						.then((uploaderFile) => {
							return Promise.all([
								new Promise((resolve) => {
									Uploader.#delay(() => {
										this.getPreview().show(uploaderFile.getClientPreview());
										this.getStatus().showPreparingStatus();
										resolve();
									}, 1000);
								}),
								this.emitAsync('onSaveAsync', {file: uploaderFile.toJSON()}),
							]);
						})
						.then(() => {
							this.getStatus().hide();

							Uploader.#delay(() => {
								saveButton.setWaiting(false);
								saveButton.setDisabled(true);
								this.getActionPanel().disable();
							}, 500);
						});
				},
			});

			button.setDisabled(true);
			this.getActionPanel().disable();

			return button;
		});
	}

	setSliderButtons(buttons: {saveButton: Button, cancelButton: Button})
	{
		this.cache.set('sliderButtons', buttons);
	}

	getSliderButtons(): {saveButton: Button, cancelButton: Button}
	{
		return this.cache.get('sliderButtons', {saveButton: null, cancelButton: null});
	}

	#setPreventConfirmShow(value: boolean)
	{
		this.cache.set('preventConfirmShow', value);
	}

	#isConfirmShowPrevented(): boolean
	{
		return this.cache.get('preventConfirmShow', false);
	}

	show()
	{
		const SidePanelInstance = Reflection.getClass('BX.SidePanel.Instance');
		if (Type.isNil(SidePanelInstance))
		{
			return;
		}

		this.getPreview().hide();
		this.getStatus().hide();
		this.getActionPanel().disable();

		SidePanelInstance.open('stampUploader', {
			width: 640,
			contentCallback: () => {
				return Layout.createContent({
					extensions: [
						'ui.stamp.uploader',
					],
					content: () => {
						return this.getLayout();
					},
					design: {
						section: false,
					},
					buttons: ({cancelButton, SaveButton}) => {
						const saveButton = new SaveButton({
							onclick: () => {
								saveButton.setWaiting(true);
								this.setIsChanged(false);
								this.#setPreventConfirmShow(true);

								this.upload()
									.then((uploaderFile) => {
										Uploader.#delay(() => {
											this.getPreview().show(uploaderFile.getClientPreview());
											this.getStatus().showPreparingStatus();
										}, 1000);

										return this.emitAsync('onSaveAsync', {file: uploaderFile.toJSON()});
									})
									.then(() => {
										this.getStatus().hide();

										Uploader.#delay(() => {
											saveButton.setWaiting(false);
											saveButton.setDisabled(true);
											this.getActionPanel().disable();
											BX.SidePanel.Instance.close();
										}, 500);
									});
							}
						});

						saveButton.setDisabled(true);
						this.getActionPanel().disable();
						this.setSliderButtons({saveButton, cancelButton});

						return [
							saveButton,
							cancelButton,
						];
					},
				});
			},
			events: {
				onClose: (event) => {
					if (this.isChanged())
					{
						event.denyAction();
						if (!this.#isConfirmShowPrevented())
						{
							Uploader.showConfirm({
								message: Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM'),
								onOk: (messageBox) => {
									this.setIsChanged(false);
									event.getSlider().close();
									messageBox.close();
								},
								okCaption: Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM_CLOSE'),
								onCancel: (messageBox) => {
									messageBox.close();
								},
								cancelCaption: Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_CLOSE_CONFIRM_CANCEL'),
							});
						}
						else
						{
							this.setIsChanged(false);
							event.getSlider().close();
						}
					}
				},
			},
		});
	}
}
