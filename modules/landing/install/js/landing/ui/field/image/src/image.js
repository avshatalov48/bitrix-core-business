import { Dom, Type, Runtime, Event } from 'main.core';
import {Loc} from 'landing.loc';
import {Main} from 'landing.main';
import {StylePanel} from 'landing.ui.panel.stylepanel';
import {TextField} from 'landing.ui.field.textfield';
import {ImageUploader} from 'landing.imageuploader';
import {BaseButton} from 'landing.ui.button.basebutton';
import {AiImageButton} from 'landing.ui.button.aiimagebutton';
import {Env} from 'landing.env';
import type { Copilot as CopilotType } from 'ai.copilot';

import 'ui.fonts.opensans';
// todo: remove most likely
import 'ui.forms';
import './css/style.css';

export class Image extends TextField
{
	imageCopilot: CopilotType;
	copilotBindElement: ?HTMLElement = null;
	aiButton = null;
	copilotContext = null;
	copilotCategory = null;
	useCopilotInIframe = false;
	copilotFinishInitPromise = new Promise(() => {});

	static CONTEXT_TYPE_CONTENT = 'content';
	static CONTEXT_TYPE_STYLE = 'style';

	constructor(data)
	{
		super(data);

		this.dimensions = typeof data.dimensions === "object" ? data.dimensions : null;
		this.create2xByDefault = data.create2xByDefault !== false;
		this.uploadParams = typeof data.uploadParams === "object" ? data.uploadParams : {};
		this.onValueChangeHandler = data.onValueChange ? data.onValueChange : (() => {});
		this.type = this.content.type || "image";
		this.contextType = data.contextType || Image.CONTEXT_TYPE_CONTENT;
		this.allowClear = data.allowClear;
		this.isAiImageAvailable = Type.isBoolean(data.isAiImageAvailable) ? data.isAiImageAvailable : false;
		this.isAiImageActive = Type.isBoolean(data.isAiImageActive) ? data.isAiImageActive : false;
		this.aiUnactiveInfoCode = Type.isString(data.aiUnactiveInfoCode) ? data.aiUnactiveInfoCode : null;

		this.input.innerText = this.content.src;
		this.input.hidden = true;
		this.input2x = this.createInput();
		this.input2x.innerText = this.content.src2x || '';
		this.input2x.hidden = true;

		this.layout.classList.add("landing-ui-field-image");
		this.compactMode = data.compactMode === true;
		if (this.compactMode)
		{
			this.layout.classList.add("landing-ui-field-image--compact");
		}

		this.disableAltField = typeof data.disableAltField === "boolean" ? data.disableAltField : false;

		this.fileInput = Image.createFileInput(this.selector);
		this.fileInput.addEventListener("change", this.onFileInputChange.bind(this));
		// Do not append input to layout! Ticket 172032

		this.linkInput = Image.createLinkInput();
		this.linkInput.onInputHandler = Runtime.debounce(this.onLinkInput.bind(this), 777);

		this.dropzone = Image.createDropzone(this.selector);
		this.dropzone.hidden = true;

		this.onDragOver = this.onDragOver.bind(this);
		this.onDragLeave = this.onDragLeave.bind(this);
		this.onDrop = this.onDrop.bind(this);

		this.dropzone.addEventListener("dragover", this.onDragOver);
		this.dropzone.addEventListener("dragleave", this.onDragLeave);
		this.dropzone.addEventListener("drop", this.onDrop);

		this.clearButton = Image.createClearButton();
		this.clearButton.on("click", this.onClearClick.bind(this));

		this.preview = Image.createImagePreview();
		this.preview.appendChild(this.clearButton.layout);
		this.preview.style.backgroundImage = "url(" + this.input.innerText.trim() + ")";

		this.onImageDragEnter = this.onImageDragEnter.bind(this);
		this.preview.addEventListener("dragenter", this.onImageDragEnter);

		this.loader = new BX.Loader({target: this.preview});

		this.icon = Image.createIcon();

		this.image = Image.createImageLayout();
		this.image.appendChild(this.preview);
		this.image.appendChild(this.icon);
		this.image.dataset.fileid = this.content.id;
		this.image.dataset.fileid2x = this.content.id2x;

		this.hiddenImage = Dom.create("img", {
			props: {className: "landing-ui-field-image-hidden"},
		});

		if (Type.isPlainObject(this.content) && "src" in this.content)
		{
			this.hiddenImage.src = this.content.src;
		}

		this.altField = Image.createAltField();
		this.altField.setValue(this.content.alt);

		this.left = Image.createLeftLayout();
		this.left.appendChild(this.dropzone);
		this.left.appendChild(this.image);
		this.left.appendChild(this.hiddenImage);

		if (this.description)
		{
			this.left.appendChild(this.description);
		}

		this.left.appendChild(this.altField.layout);
		this.left.appendChild(this.linkInput.layout);

		this.uploadButton = Image.createUploadButton(this.compactMode);
		this.uploadButton.on("click", this.onUploadClick.bind(this));

		this.editButton = Image.createEditButton();
		this.editButton.on("click", this.onEditClick.bind(this));

		this.right = Image.createRightLayout();

		// ai images
		if (
			this.isAiImageAvailable
			&& (this.type === "background" || this.type === "image")
		)
		{
			this.useCopilotInIframe = this.uploadParams.action === 'Landing::uploadFile';
			this.defineCopilotCategory();
			const copilotOptions = {
				moduleId: 'landing',
				contextId: this.getAiContext(),
				category: this.copilotCategory,
				useText: false,
				useImage: true,
				autoHide: true,
			};

			this.copilotContext = this.useCopilotInIframe ? BX : top.BX;
			this.stylePanel = StylePanel.getInstance().layout;
			this.stylePanelContent = StylePanel.getInstance().content;
			this.copilotContext.Runtime.loadExtension('ai.copilot').then(({ Copilot, CopilotEvents}) => {
				this.imageCopilot = new Copilot(copilotOptions);

				this.imageCopilot.subscribe(CopilotEvents.FINISH_INIT, this.imageCopilotFinishInitHandler.bind(this));
				this.imageCopilot.subscribe(CopilotEvents.IMAGE_COMPLETION_RESULT, this.imageCopilotImageResultHandler.bind(this));
				this.imageCopilot.subscribe(CopilotEvents.IMAGE_SAVE, this.imageCopilotSaveImageHandler.bind(this));
				this.imageCopilot.subscribe(CopilotEvents.IMAGE_CANCEL, this.imageCopilotCancelImageHandler.bind(this));

				Event.bind(this.stylePanelContent, 'scroll', this.onScrollContentPanel.bind(this));
				Event.bind(this.stylePanel, 'click', this.onClickStylePanel.bind(this));
				Event.EventEmitter.subscribe('BX.Landing.UI.Panel.ContentEdit:onClick', this.onClickContentPanel.bind(this));
				Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onHide', this.closeCopilot.bind(this));
				Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onClick', this.onClickContentPanel.bind(this));
				Event.EventEmitter.subscribe('BX.Landing.UI.Panel.BasePanel:onScroll', this.onScrollContentPanel.bind(this));
				this.imageCopilot.init();
			});

			this.aiButton = Image.createAiButton(this.compactMode);
			BX.bind(this.aiButton.layout, 'click', () => {
				if (this.isAiImageActive)
				{
					this.onAiClick();
				}
				else if (this.aiUnactiveInfoCode && this.aiUnactiveInfoCode.length > 0)
				{
					BX.UI.InfoHelper.show(this.aiUnactiveInfoCode);
				}
			});
			this.right.appendChild(this.aiButton.layout);
		}

		this.right.appendChild(this.uploadButton.layout);
		this.right.appendChild(this.editButton.layout);
		this.form = Image.createForm();
		this.form.appendChild(this.left);
		this.form.appendChild(this.right);

		this.layout.appendChild(this.form);

		this.enableTextOnly();

		if (!this.input.innerText.trim() || this.input.innerText.trim() === window.location.toString())
		{
			this.showDropzone();
		}

		if (this.disableAltField)
		{
			this.altField.layout.hidden = true;
			this.altField.layout.style.display = "none";
			this.altField.layout.classList.add("landing-ui-hide");
		}

		if (this.content.type === "icon")
		{
			this.type = "icon";
			this.classList = this.content.classList;
			this.showPreview();
			this.altField.layout.hidden = true;
			Dom.addClass(this.layout, 'landing-ui-field-image-icon');
		}

		this.makeAsLinkWrapper = Dom.create("div", {
			props: {className: "landing-ui-field-image-make-as-link-wrapper"},
			children: [
				Dom.create('div', {
					props: {className: "landing-ui-field-image-make-as-link-button"},
					children: [],
				}),
			],
		});

		this.url = new BX.Landing.UI.Field.Link({
			content: this.content.url || {
				text: '',
				href: '',
			},
			options: {
				siteId: Main.getInstance().options.site_id,
				landingId: Main.getInstance().id,
			},
			contentRoot: this.contentRoot,
		});

		this.isDisabledUrl = this.content.url && this.content.url.enabled === false;
		if (this.isDisabledUrl)
		{
			this.content.url.href = '';
		}

		this.url.left.hidden = true;

		this.makeAsLinkWrapper.appendChild(this.url.layout);

		if (!data.disableLink)
		{
			this.layout.appendChild(this.makeAsLinkWrapper);
		}

		this.content = this.getValue();
		BX.DOM.write(function ()
		{
			this.adjustPreviewBackgroundSize();
		}.bind(this));

		if (this.getValue().type === "background" || this.allowClear)
		{
			this.clearButton.layout.classList.add("landing-ui-show");
		}

		this.uploader = new ImageUploader({
			uploadParams: this.uploadParams,
			additionalParams: {context: 'imageeditor'},
			dimensions: this.dimensions,
			sizes: ['1x', '2x'],
			allowSvg: Main.getInstance().options.allow_svg === true,
		});

		this.adjustEditButtonState();
	}

	/**
	 * Creates file input
	 * @return {Element}
	 */
	static createFileInput(id)
	{
		return Dom.create("input", {
			props: {className: "landing-ui-field-image-dropzone-input"},
			attrs: {accept: "image/*", type: "file", id: "file_" + id, name: "picture"},
		});
	}

	/**
	 * Creates link input field
	 * @return {TextField}
	 */
	static createLinkInput(): TextField
	{
		var field = new TextField({
			id: "path_to_image",
			placeholder: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK_LABEL"),
		});
		field.enableTextOnly();
		field.layout.hidden = true;
		return field;
	}

	/**
	 * Creates dropzone
	 * @param {string} id
	 * @return {Element}
	 */
	static createDropzone(id)
	{
		return Dom.create("label", {
			props: {className: "landing-ui-field-image-dropzone"},
			children: [
				Dom.create("div", {
					props: {className: "landing-ui-field-image-dropzone-text"},
					html: (
						"<div class=\"landing-ui-field-image-dropzone-title\">" + Loc.getMessage(
							"LANDING_IMAGE_DROPZONE_TITLE") + "</div>" +
						"<div class=\"landing-ui-field-image-dropzone-subtitle\">" + Loc.getMessage(
							"LANDING_IMAGE_DROPZONE_SUBTITLE") + "</div>"
					),
				}),
			],
			attrs: {"for": "file_" + id},
		});
	}

	/**
	 * Creates clear button
	 * @return {BaseButton}
	 */
	static createClearButton()
	{
		return new BaseButton("clear", {
			className: "landing-ui-field-image-action-button-clear",
		});
	}

	/**
	 * Creates image preview
	 * @return {Element}
	 */
	static createImagePreview()
	{
		return Dom.create("div", {
			props: {className: "landing-ui-field-image-preview-inner"},
		});
	}

	/**
	 * Creates icon layout
	 * @return {Element}
	 */
	static createIcon()
	{
		return Dom.create("span", {
			props: {className: "landing-ui-field-image-preview-icon"},
		});
	}

	/**
	 * Creates image layout
	 * @return {Element}
	 */
	static createImageLayout()
	{
		return Dom.create("div", {
			props: {className: "landing-ui-field-image-preview"},
		});
	}

	/**
	 * Creates alt field
	 * @return {TextField}
	 */
	static createAltField()
	{
		var field = new TextField({
			placeholder: Loc.getMessage("LANDING_FIELD_IMAGE_ALT_PLACEHOLDER"),
			className: "landing-ui-field-image-alt",
			textOnly: true,
		});
		return field;
	}

	/**
	 * Creates left layout
	 * @return {Element}
	 */
	static createLeftLayout()
	{
		return Dom.create("div", {
			props: {className: "landing-ui-field-image-left"},
		});
	}

	/**
	 * Creates upload button
	 * @return {BaseButton}
	 */
	static createAiButton(compactMode: boolean = false)
	{
		return new AiImageButton("ai", {
			text: 'CoPilot',
			className: "landing-ui-field-image-ai-button" + (compactMode ? ' --compact' : ''),
		});
	}

	/**
	 * Creates ia create button
	 * @return {BaseButton}
	 */
	static createUploadButton()
	{
		return new BaseButton("upload", {
			text: Loc.getMessage("LANDING_FIELD_IMAGE_UPLOAD_BUTTON"),
			className: "landing-ui-field-image-action-button",
		});
	}

	/**
	 * Creates edit button
	 * @return {BaseButton}
	 */
	static createEditButton()
	{
		var field = new BaseButton("edit", {
			text: Loc.getMessage("LANDING_FIELD_IMAGE_EDIT_BUTTON"),
			className: "landing-ui-field-image-action-button",
		});

		return field;
	}

	/**
	 * Creates right layout
	 * @return {Element}
	 */
	static createRightLayout()
	{
		return Dom.create("div", {
			props: {className: "landing-ui-field-image-right"},
		});
	}

	/**
	 * Creates form
	 * @return {Element}
	 */
	static createForm()
	{
		return Dom.create("form", {
			props: {className: "landing-ui-field-image-container"},
			attrs: {method: "post", enctype: "multipart/form-data"},
			events: {
				submit: function (event)
				{
					event.preventDefault();
				},
			},
		});
	}

	onInputInput()
	{
		this.preview.src = this.input.innerText.trim();
	}

	onImageDragEnter(event)
	{
		event.preventDefault();
		event.stopPropagation();

		if (!this.imageHidden)
		{
			this.showDropzone();
			this.imageHidden = true;
		}
	}

	onDragOver(event)
	{
		event.preventDefault();
		event.stopPropagation();
		this.dropzone.classList.add("landing-ui-active");
	}

	onDragLeave(event)
	{
		event.preventDefault();
		event.stopPropagation();
		this.dropzone.classList.remove("landing-ui-active");

		if (this.imageHidden)
		{
			this.imageHidden = false;
			this.showPreview();
		}
	}

	onDrop(event)
	{
		event.preventDefault();
		event.stopPropagation();
		this.dropzone.classList.remove("landing-ui-active");
		this.onFileChange(event.dataTransfer.files[0]);
		this.imageHidden = false;
	}

	onFileChange(file)
	{
		this.showLoader();

		this.upload(file)
			.then(this.setValue.bind(this))
			.then(this.hideLoader.bind(this))
			.catch(function (err)
			{
				console.error(err);
				this.hideLoader();
			}.bind(this));
	}

	onFileInputChange(event)
	{
		this.onFileChange(event.currentTarget.files[0]);
	}

	async onAiClick()
	{
		await this.copilotFinishInitPromise;
		this.showCopilot();
	}

	/**
	 * Return AI image button, if exists (if allow)
	 */
	getAiButton(): ?BaseButton
	{
		return this.aiButton;
	}

	showCopilot()
	{
		this.copilotBindElement = this.aiButton.layout;
		const offsetY = 3;
		const copilotBindElementPosition = this.copilotBindElement.getBoundingClientRect();

		this.imageCopilot.show({
			width: 500,
			bindElement: {
				top: copilotBindElementPosition.bottom + offsetY,
				left: copilotBindElementPosition.left,
			},
		});
		this.imageCopilot.adjustPosition({});
	}

	imageCopilotFinishInitHandler()
	{
		this.copilotFinishInitPromise = Promise.resolve();
	}

	imageCopilotImageResultHandler(e)
	{
		const data = e.getData();
		this.imageCopilotUrl = encodeURI(data.imageUrl);
		if (this.copilotBindElement === this.dropzone)
		{
			this.showPreview();
		}
		Dom.addClass(this.preview, '--shown');
		Dom.style(this.preview, 'background-image', `url("${this.imageCopilotUrl}")`);
		Dom.style(this.preview, 'background-size', 'contain');
	}

	imageCopilotSaveImageHandler()
	{
		const url = this.imageCopilotUrl;
		const proxyUrl = BX.util.add_url_param("/bitrix/tools/landing/proxy.php", {
			"sessid": BX.bitrix_sessid(),
			"url": url,
		});
		BX.Landing.Utils.urlToBlob(proxyUrl)
			.then(blob => {
				blob.lastModifiedDate = new Date();
				blob.name = url.slice(url.lastIndexOf('/') + 1);

				return blob;
			})
			.then(this.upload.bind(this))
			.then(this.setValue.bind(this))
			.then(this.hideLoader.bind(this))
			.then(() => {
				Dom.removeClass(this.preview, '--shown');
			});

		this.closeCopilot();
	}

	imageCopilotCancelImageHandler()
	{
		if (this.copilotBindElement === this.dropzone)
		{
			this.showDropzone();
		}
		else
		{
			Dom.removeClass(this.preview, '--shown');
			Dom.style(this.preview, 'background-image', `url("${this.input.innerText.trim()}")`);
		}
	}

	closeCopilot()
	{
		if (this.imageCopilot.isShown())
		{
			this.imageCopilot.hide();
			Event.EventEmitter.unsubscribe('BX.Landing.UI.Panel.BasePanel:onHide', this.closeCopilot.bind(this));
			Event.unbind(this.stylePanel, 'click', this.onClickStylePanel.bind(this));
		}
	}

	onClickStylePanel(event)
	{
		if (!this.aiButton.layout.contains(event.target))
		{
			this.closeCopilot();
		}
	}

	onClickContentPanel(event)
	{
		const target = event.getData().event.target;
		if (!this.aiButton.layout.contains(target))
		{
			this.closeCopilot();
		}
	}

	onScrollContentPanel()
	{
		if (Boolean(this.imageCopilot?.isShown()) === false)
		{
			return;
		}

		if (this.imageCopilot.getPosition().inputField.top < 133)
		{
			this.imageCopilot.adjustPosition({ hide: true });
		}
		else
		{
			this.imageCopilot.adjustPosition({ hide: false });
		}
	}

	defineCopilotCategory()
	{
		this.copilotCategory = this.contextType === 'style' ? 'landing_designer'
			: (this.useCopilotInIframe ? 'landing_setting'
				: 'landing_editor');
	}

	getAiContext(): string
	{
		return 'image_site_' + Env.getInstance().getSiteId();
	}

	onUploadClick(event)
	{
		this.bindElement = event.currentTarget;

		event.preventDefault();

		if (!this.uploadMenu)
		{
			this.uploadMenu = BX.Main.MenuManager.create({
				id: "upload_" + this.selector + (+new Date()),
				bindElement: this.bindElement,
				bindOptions: {
					forceBindPosition: true,
				},
				items: [
					{
						text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UNSPLASH"),
						onclick: this.onUnsplashShow.bind(this),
					},
					{
						text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_GOOGLE"),
						onclick: this.onGoogleShow.bind(this),
					},
					// {
					// 	text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_PARTNER"),
					// 	className: "landing-ui-disabled"
					// },
					{
						text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UPLOAD"),
						onclick: this.onUploadShow.bind(this),
					},
					{
						text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK"),
						onclick: this.onLinkShow.bind(this),
					},
				],
				events: {
					onPopupClose: function ()
					{
						this.bindElement.classList.remove("landing-ui-active");

						if (this.uploadMenu)
						{
							this.uploadMenu.destroy();
							this.uploadMenu = null;
						}
					}.bind(this),
				},
				targetContainer: this.contentRoot,
			});
			if (!this.contentRoot)
			{
				this.bindElement.parentNode.appendChild(this.uploadMenu.popupWindow.popupContainer);
			}
		}

		this.bindElement.classList.add("landing-ui-active");
		this.uploadMenu.toggle();

		if (!this.contentRoot && this.uploadMenu)
		{
			var rect = BX.pos(this.bindElement, this.bindElement.parentNode);
			this.uploadMenu.popupWindow.popupContainer.style.top = rect.bottom + "px";
			this.uploadMenu.popupWindow.popupContainer.style.left = "auto";
			this.uploadMenu.popupWindow.popupContainer.style.right = "5px";
		}
	}

	onUnsplashShow()
	{
		this.uploadMenu.close();

		BX.Landing.UI.Panel.Image.getInstance()
			.show("unsplash", this.dimensions, this.loader, this.uploadParams)
			.then(this.upload.bind(this))
			.then(this.setValue.bind(this))
			.then(this.hideLoader.bind(this))
			.catch(function (err)
			{
				console.error(err);
				this.hideLoader();
			}.bind(this));
	}

	onGoogleShow()
	{
		this.uploadMenu.close();

		BX.Landing.UI.Panel.Image.getInstance()
			.show("google", this.dimensions, this.loader, this.uploadParams)
			.then(this.upload.bind(this))
			.then(this.setValue.bind(this))
			.then(this.hideLoader.bind(this))
			.catch(function (err)
			{
				BX.Landing.ErrorManager.getInstance().add({
					type: 'error',
					action: 'BAD_IMAGE',
					hideSupportLink: true,
				});
				console.error(err);
				this.hideLoader();
			}.bind(this));
	}

	onUploadShow()
	{
		this.uploadMenu.close();
		this.fileInput.click();
	}

	onLinkShow()
	{
		this.uploadMenu.close();
		this.showLinkField();
		this.linkInput.setValue("");
	}

	onEditClick(event)
	{
		event.preventDefault();
		this.edit({src: this.hiddenImage.src});
	}

	onClearClick(event)
	{
		event.preventDefault();
		this.setValue({src: ""});
		this.fileInput.value = "";
		this.showDropzone();
	}

	showDropzone()
	{
		this.dropzone.hidden = false;
		this.image.hidden = true;
		this.altField.layout.hidden = true;
		this.linkInput.layout.hidden = true;
	}

	showPreview()
	{
		this.dropzone.hidden = true;
		this.image.hidden = false;
		this.altField.layout.hidden = false;
		this.linkInput.layout.hidden = true;
	}

	showLinkField()
	{
		this.dropzone.hidden = true;
		this.image.hidden = true;
		this.altField.layout.hidden = true;
		this.linkInput.layout.hidden = false;
	}

	onLinkInput(value)
	{
		const tmpImage = Dom.create("img");
		tmpImage.src = value;
		tmpImage.onload = () => {
			this.showPreview();
			this.setValue({src: value, src2x: value});
		};
	}

	showLoader()
	{
		if (this.dropzone && !this.dropzone.hidden)
		{
			this.loader.show(this.dropzone);
			return;
		}

		this.loader.show(this.preview);
	}

	hideLoader()
	{
		this.loader.hide();
	}

	/**
	 * Handles click event on input field
	 * @param {MouseEvent} event
	 */
	onInputClick(event)
	{
		event.preventDefault();
	}

	/**
	 * @inheritDoc
	 * @return {boolean}
	 */
	isChanged()
	{
		var lastValue = BX.Landing.Utils.clone(this.content);
		var currentValue = BX.Landing.Utils.clone(this.getValue());

		if (lastValue.url && Type.isString(lastValue.url))
		{
			lastValue.url = BX.Landing.Utils.decodeDataValue(lastValue.url);
		}

		if (currentValue.url && Type.isString(currentValue.url))
		{
			currentValue.url = BX.Landing.Utils.decodeDataValue(currentValue.url);
		}

		return JSON.stringify(lastValue) !== JSON.stringify(currentValue);
	}

	/**
	 * Adjusts preview background image size
	 */
	adjustPreviewBackgroundSize()
	{
		var img = Dom.create("img", {attrs: {src: this.getValue().src}});

		img.onload = function ()
		{
			var preview = this.preview.getBoundingClientRect();
			var position = "cover";

			if (img.width > preview.width || img.height > preview.height)
			{
				position = "contain";
			}

			if (img.width < preview.width && img.height < preview.height)
			{
				position = "auto";
			}

			BX.DOM.write(function ()
			{
				this.preview.style.backgroundSize = position;
			}.bind(this));
		}.bind(this);
	}

	/**
	 * @param {object} value
	 * @param {boolean} [preventEvent = false]
	 */
	setValue(value, preventEvent)
	{
		if (value.type !== "icon")
		{
			if (!value || !value.src)
			{
				this.input.innerText = "";
				this.input2x.innerText = "";
				this.preview.removeAttribute("style");
				this.input.dataset.ext = "";
				this.showDropzone();
			}
			else
			{
				this.input.innerText = value.src;
				this.input2x.innerText = value.src2x || '';
				this.preview.style.backgroundImage = "url(\"" + (value.src2x || value.src) + "\")";
				this.preview.id = BX.util.getRandomString();
				this.hiddenImage.src = value.src2x || value.src;
				this.showPreview();
			}

			this.image.dataset.fileid = value && value.id ? value.id : -1;
			this.image.dataset.fileid2x = value && value.id2x ? value.id2x : -1;

			if (value.type === 'image')
			{
				this.altField.layout.hidden = false;
				this.altField.setValue(value.alt);
			}

			this.classList = [];
		}
		else
		{
			this.preview.style.backgroundImage = null;
			this.classList = value.classList;
			this.icon.innerHTML = "<span class=\"" + value.classList.join(" ") + "\"></span>";
			this.showPreview();
			this.type = "icon";
			this.altField.layout.hidden = true;
			this.altField.setValue("");
			this.input.innerText = "";
		}

		if (value.url)
		{
			this.url.setValue(value.url);
		}

		this.adjustPreviewBackgroundSize();
		this.adjustEditButtonState();
		this.hideLoader();

		this.onValueChangeHandler(this);
		BX.fireEvent(this.layout, "input");

		var event = new BX.Event.BaseEvent({
			data: {value: this.getValue()},
			compatData: [this.getValue()],
		});
		if (!preventEvent)
		{
			this.emit('change', event);
		}
	}

	adjustEditButtonState()
	{
		var value = this.getValue();
		if (BX.Type.isStringFilled(value.src))
		{
			this.editButton.enable();
		}
		else
		{
			this.editButton.disable();
		}
	}

	reset()
	{
		this.setValue({
			type: this.getValue().type,
			id: -1,
			src: "",
			alt: "",
		});
	}

	/**
	 * Gets field value
	 * @return {{src, [alt]: string, [title]: string, [url]: string, [type]: string}}
	 */
	getValue()
	{
		const value = {type: "", src: "", alt: "", url: ""};

		const fileId = parseInt(this.image.dataset.fileid);
		if (Type.isNumber(fileId) && fileId > 0)
		{
			value.id = fileId;
		}

		const fileId2x = parseInt(this.image.dataset.fileid2x);
		if (Type.isNumber(fileId2x) && fileId2x > 0)
		{
			value.id2x = fileId2x;
		}

		const src2x = this.input2x.innerText.trim();
		if (Type.isString(src2x) && src2x)
		{
			value.src2x = src2x;
		}

		if (this.type === "background" || this.type === "image")
		{
			value.src = this.input.innerText.trim();
		}

		if (this.type === "background")
		{
			value.type = "background";
		}

		if (this.type === "image")
		{
			value.type = "image";
			value.alt = this.altField.getValue();
		}

		if (this.type === "icon")
		{
			value.type = "icon";
			value.classList = this.classList;
		}

		value.url = Object.assign({}, this.url.getValue(), {enabled: true});

		return value;
	}

	edit(data)
	{
		parent.BX.Landing.ImageEditor
			.edit({
				image: data.src,
				dimensions: this.dimensions,
			})
			.then(function (file)
			{
				let ext = file.name.split('.').pop();
				if (!file.name.includes('.') || ext.length > 4)
				{
					ext = `.${file.name.split('_').pop()}`;
					file.name = file.name + ext;
				}

				return this.upload(file, {context: "imageEditor"});
			}.bind(this))
			.then(function (result)
			{
				this.setValue(result);
			}.bind(this));

		// Analytics hack
		const tmpImage = document.createElement('img');
		let imageSrc = "/bitrix/images/landing/close.svg";

		imageSrc = BX.util.add_url_param(imageSrc, {
			action: "openImageEditor",
		});

		tmpImage.src = imageSrc + "?" + (+new Date());
	}

	/**
	 * @param {File|Blob} file
	 * @param {object} [additionalParams]
	 */
	upload(file, additionalParams)
	{
		if (file.type && (file.type.includes('text') || file.type.includes('html')))
		{
			BX.Landing.ErrorManager.getInstance().add({
				type: "error",
				action: "BAD_IMAGE",
			});

			return Promise.reject({
				type: "error",
				action: "BAD_IMAGE",
			});
		}

		this.showLoader();

		const isPng = (
			Type.isStringFilled(file.type)
			&& file.type.includes('png')
		);

		const isSvg = (
			Type.isStringFilled(file.type)
			&& file.type.includes('svg')
		);

		const checkSize = new Promise(function (resolve)
		{
			let sizes = (isPng || isSvg) ? ['2x'] : ['1x', '2x'];

			if (this.create2xByDefault === false)
			{
				const image = document.createElement('img');
				const objectUrl = URL.createObjectURL(file);
				const dimensions = this.dimensions;
				image.onload = function ()
				{
					URL.revokeObjectURL(objectUrl);
					if (
						(
							this.width >= dimensions.width
							|| this.height >= dimensions.height
							|| this.width >= dimensions.maxWidth
							|| this.height >= dimensions.maxHeight
						) === false
					)
					{
						sizes = (isPng || isSvg) ? ['2x'] : ['1x'];
					}

					resolve(sizes);
				};
				image.src = objectUrl;
			}
			else
			{
				resolve(sizes);
			}
		}.bind(this));

		return checkSize
			.then(function (allowedSizes)
			{
				var sizes = (function ()
				{
					if (
						this.create2xByDefault === false
						&& BX.Type.isArrayFilled(allowedSizes)
					)
					{
						return allowedSizes;
					}

					return (isPng || isSvg) ? ['2x'] : ['1x', '2x'];
				}.bind(this))();

				return this.uploader
					.setSizes(sizes)
					.upload(file, additionalParams)
					.then(function (result)
					{
						this.hideLoader();

						if (sizes.length === 1)
						{
							return result[0];
						}

						return Object.assign({}, result[0], {
							src2x: result[1].src,
							id2x: result[1].id,
						});
					}.bind(this));
			}.bind(this));
	}
}
