import { Tag, Text, Loc, Dom, Event, Runtime } from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Util} from 'calendar.util';
import Checkbox from './checkbox';

export default class Dialog
{
	POPUP_WIDTH = 420;
	zIndex = 3100;
	QRCODE_SIZE = 114;
	QRCODE_COLOR_LIGHT = '#fff';
	QRCODE_COLOR_DARK = '#000';

	constructor(options = {})
	{
		this.bindElement = options.bindElement;
		this.userId = options.userId;
		this.isSharingOn = options.isSwitchCheckedOnStart;
		this.switcherNode = options.switcherNode;
		this.create();
	}

	create()
	{
		this.popup = new BX.Main.Popup({
			bindElement: this.bindElement,
			minHeight: 230,
			width: this.POPUP_WIDTH,
			autoHide: true,
			autoHideHandler: (event) => this.dialogPopupAutoHideHandler(event),
			closeByEsc: true,
			angle: { offset: this.POPUP_WIDTH / 2 },
			offsetLeft: (this.bindElement.offsetWidth / 2) - this.POPUP_WIDTH / 2.25,
			events: {
				onFirstShow: this.onFirstShow.bind(this),
				onClose: this.onClose.bind(this),
			},
		});

		this.createLoader().show();
	}

	dialogPopupAutoHideHandler(event)
	{
		if (this.switcherNode.contains(event.target) || this.popup.getPopupContainer().contains(event.target))
		{
			return false;
		}

		return true;
	}

	getPopup()
	{
		return this.popup;
	}

	getLoader()
	{
		return this.loader;
	}

	isShown()
	{
		return this.popup?.isShown();
	}

	destroy()
	{
		this.popup?.destroy();
	}

	createLoader()
	{
		this.loader = new BX.Loader({
			target: this.popup.getContentContainer(),
			size: 110,
		});

		return this.loader;
	}

	async onFirstShow()
	{
		await this.loadDialogData();
		await this.initQrCode();
		this.getPopup().setContent(this.createDialogContent());
		this.onAfterDialogContentCreated();
		this.getLoader().hide();
	}

	onClose()
	{
		EventEmitter.emit('Calendar.Sharing.Dialog:onClose');
	}

	async loadDialogData()
	{
		const response = await BX.ajax.runAction('calendar.api.sharingajax.getDialogData', {
			data: {
				isSharingOn: this.isSharingOn,
			}
		});

		this.links = response.data.links;
	}

	async initQrCode()
	{
		await Runtime.loadExtension(['main.qrcode']);
	}

	onAfterDialogContentCreated()
	{
		this.subscribeToEvents();
		Dom.style(
			this.copyLinkButtonContainer.firstChild,
			'min-width',
			this.copyLinkButtonContainer.offsetWidth + 1 + 'px'
		);
	}

	subscribeToEvents()
	{
		EventEmitter.subscribe(
			'Calendar.Sharing.copyLinkButton:onSwitchToggled',
			(event) => {
				this.copyLinkButton?.setDisabled(!event.data);
				if (this.previewBlockQr)
				{
					Dom.removeClass(
						this.previewBlockQr,
						'calendar-sharing-dialog-preview-block-qr-container-blurred'
					);
				}
				if (this.previewBlockAnnotationLink)
				{
					Dom.removeClass(
						this.previewBlockAnnotationLink,
						'calendar-sharing-dialog-preview-block-annotation-link-disabled'
					);
				}

				this.links.forEach((link) => {
					if (link.linkInputNode)
					{
						Dom.removeClass(
							link.linkContainerNode,
							'calendar-sharing-dialog-sharing-block-link-container-disabled'
						);
						Dom.removeClass(
							link.linkInputNode,
							'calendar-sharing-dialog-controls-link-text-disabled'
						);
						Dom.attr(link.linkInputNode, 'value', link.url);
						EventEmitter.emit('Calendar.Sharing.LinkTextContainer:onChange');
					}
				});

				BX.ajax.runAction('calendar.api.sharingajax.toggleLink', {
					data: {
						userLinkId: this.links[0].id,
						isActive: this.links[0].active,
					},
				});
			}
		);
	}

	getDialogContent()
	{
		return this.dialogContent;
	}

	createDialogContent()
	{
		this.dialogContent = this.createContentWrap();
		Dom.append(this.createSharingBlock(), this.dialogContent);
		Dom.append(this.createPreviewBlock(), this.dialogContent);

		return this.dialogContent;
	}

	createContentWrap()
	{
		this.contentWrap = Tag.render`
			<div class="calendar-sharing-dialog-wrap"></div>
		`;

		return this.contentWrap;
	}

	createSharingBlock()
	{
		this.sharingBlock = this.createBlock();
		Dom.append(this.createSharingBlockTitle(), this.sharingBlock);
		Dom.append(this.createSharingBlockLinks(), this.sharingBlock);

		return this.sharingBlock;
	}

	createBlock()
	{
		return Tag.render`
			<div class="calendar-sharing-dialog-block"></div>
		`;
	}

	createSharingBlockTitle()
	{
		this.sharingBlockTitle = Tag.render`
			<div class="calendar-sharing-dialog-sharing-block-title">
				<div class="calendar-sharing-dialog-sharing-block-title-text">
					${Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_TITLE')}
				</div>
			</div>
		`;

		Dom.append(this.createSharingHint(), this.sharingBlockTitle);

		return this.sharingBlockTitle;
	}

	createSharingHint()
	{
		return BX.UI.Hint.createNode(Loc.getMessage('SHARING_DIALOG_SHARING_HINT'));
	}

	createSharingBlockLinks()
	{
		const references = Tag.render`
			<div></div>
		`;
		this.links.forEach((link) =>{
			Dom.append(this.createSharingBlockLink(link), references);
		});

		return references;
	}

	createSharingBlockLink(link)
	{
		const referenceBlock = Tag.render`
			<div class="calendar-sharing-dialog-sharing-block-link-container"></div>
		`;
		const linkContainer = Tag.render`
			<div class="calendar-sharing-dialog-controls-container"></div>
		`;

		const linkInput = Tag.render`
			<input
				type="text"
				class="calendar-sharing-dialog-controls-link-text"
				value="${Text.encode(link.url)}"
				readonly
			>
		`;

		if (!this.isSharingOn)
		{
			Dom.attr(linkInput, 'value', link.serverPath + '/...');
			Dom.addClass(referenceBlock,'calendar-sharing-dialog-sharing-block-link-container-disabled');
			Dom.addClass(linkInput,'calendar-sharing-dialog-controls-link-text-disabled');
			Dom.style(linkInput,'width', linkInput.value.length - 3 + 'ch');
		}
		else
		{
			Dom.style(linkInput,'width', linkInput.value.length + 'ch');
		}

		EventEmitter.subscribe(
			'Calendar.Sharing.LinkTextContainer:onChange',
			() => {
				Dom.style(linkInput,'width', linkInput.value.length + 'ch');
			},
		);

		link.linkContainerNode = referenceBlock
		link.linkInputNode = linkInput;

		Dom.append(linkInput, linkContainer);
		Dom.append(linkContainer, referenceBlock);

		this.copyLinkButtonContainer = this.createCopyLinkButtonContainer();
		Dom.append(this.copyLinkButtonContainer, referenceBlock);
		this.copyLinkButton = this.createCopyLinkButton(link.url);
		this.copyLinkButton.renderTo(this.copyLinkButtonContainer);

		return referenceBlock;
	}

	createCopyLinkButtonContainer()
	{
		const copyLinkButtonContainer = Tag.render`<div></div>`;
		Event.bind(copyLinkButtonContainer, 'mouseenter', () => this.handleCopyLinkButtonContainerMouseEnter());
		Event.bind(copyLinkButtonContainer, 'mouseleave', () => this.handleCopyLinkButtonContainerMouseLeave());

		return copyLinkButtonContainer;
	}

	handleCopyLinkButtonContainerMouseEnter()
	{
		if (this.copyLinkButton?.disabled)
		{
			EventEmitter.emit('Calendar.Sharing.copyLinkButtonContainer:onMouseEnter');
			this.showDisabledCopyLinkButtonInfoPopup();
		}
	}

	showDisabledCopyLinkButtonInfoPopup()
	{
		if (!this.disabledCopyLinkButtonPopup)
		{
			this.disabledCopyLinkButtonPopup = this.createDisabledCopyLinkButtonInfoPopup();
		}
		if (!this.disabledCopyLinkButtonPopup?.isShown())
		{
			this.disabledCopyLinkButtonPopup.show();
		}
	}

	handleCopyLinkButtonContainerMouseLeave()
	{
		if (this.copyLinkButton?.disabled)
		{
			this.hideDisabledCopyLinkButtonInfoPopup();
		}
	}

	hideDisabledCopyLinkButtonInfoPopup()
	{
		if (this.disabledCopyLinkButtonPopup?.isShown())
		{
			this.disabledCopyLinkButtonPopup.close();
		}
	}

	createDisabledCopyLinkButtonInfoPopup()
	{
		const disabledCopyLinkButtonInfoPopupWidth = 200;

		return new BX.Main.Popup(
			{
				bindElement: this.copyLinkButtonContainer,
				className: 'calendar-clipboard-copy',
				content: Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_DISABLED_COPY_LINK_BUTTON_POPUP'),

				offsetLeft: (this.copyLinkButtonContainer.offsetWidth / 2 - disabledCopyLinkButtonInfoPopupWidth / 2) + 40,
				width: disabledCopyLinkButtonInfoPopupWidth,
				darkMode: true,
				zIndex: 1000,
				angle: {
					position: 'top',
					offset: 90,
				},
				cacheable: true,
			}
		);
	}

	createCopyLinkButton(link)
	{
		const copyLinkButton = new BX.UI.Button({
			text: Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'),
			round: true,
			icon: BX.UI.Button.Icon.COPY,
			size: BX.UI.Button.Size.EXTRA_SMALL,
			color: BX.UI.Button.Color.SUCCESS,
			onclick: (button) => this.handleCopyLinkButtonClick(button, link),
		});

		copyLinkButton.setDisabled(!this.isSharingOn);

		return copyLinkButton;
	}

	handleCopyLinkButtonClick(button, link)
	{
		const copyResult = this.copyLink(button, link);
		if (copyResult)
		{
			this.onSuccessfulCopyingLink();
		}
	}

	copyLink(button, link = false)
	{
		return !(!link || !BX.clipboard.copy(this.makeLinkText(link)));
	}

	makeLinkText(link)
	{
		return link;
	}

	onSuccessfulCopyingLink()
	{
		Util.showNotification(Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
		this.copyLinkButton?.setText(Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON_COPIED'));
		this.copyLinkButton?.setIcon(BX.UI.Button.Icon.DONE);
		if (this.copyLinkButtonTimeoutId)
		{
			clearTimeout(this.copyLinkButtonTimeoutId);
		}
		this.copyLinkButtonTimeoutId = setTimeout(() => {
			this.copyLinkButton?.setIcon(BX.UI.Button.Icon.COPY);
			this.copyLinkButton?.setText(Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'));
		}, 3000);
	}

	createPreviewBlock()
	{
		this.previewBlock = this.createBlock();
		Dom.addClass(this.previewBlock, 'calendar-sharing-dialog-block-preview-section');
		Dom.append(this.createPreviewBlockQr(), this.previewBlock);
		Dom.append(this.createPreviewBlockAnnotation(), this.previewBlock);

		return this.previewBlock;
	}

	createPreviewBlockQr()
	{
		this.previewBlockQr = Tag.render`
			<div class="calendar-sharing-dialog-preview-block-qr-container"></div>
		`;

		this.QRCode = new QRCode(this.previewBlockQr, {
			text: this.links[0].url,
			width: this.QRCODE_SIZE,
			height: this.QRCODE_SIZE,
			colorDark : this.QRCODE_COLOR_DARK,
			colorLight : this.QRCODE_COLOR_LIGHT,
			correctLevel : QRCode.CorrectLevel.H
		});

		if (!this.isSharingOn)
		{
			Dom.addClass(this.previewBlockQr, 'calendar-sharing-dialog-preview-block-qr-container-blurred');
		}

		return this.previewBlockQr;
	}

	createPreviewBlockAnnotation()
	{
		this.previewBlockAnnotation = Tag.render`
			<div class="calendar-sharing-dialog-preview-block-annotation"></div>
		`;
		Dom.append(
			this.createPreviewBlockAnnotationItem(),
			this.previewBlockAnnotation
		);

		return this.previewBlockAnnotation;
	}

	createPreviewBlockAnnotationItem()
	{

		const annotation = Tag.render`
			<div class="calendar-sharing-dialog-preview-block-annotation-item"></div>
		`;
		const linkPhrase = '<a class="calendar-sharing-dialog-preview-block-annotation-link">'
			+ Loc.getMessage('SHARING_DIALOG_PREVIEW_BLOCK_CONTENT_LINK')
			+ '</a>'
		;
		const blockContent = Tag.render`
			<span>${Loc.getMessage('SHARING_DIALOG_PREVIEW_BLOCK_CONTENT', {'#LINK#': linkPhrase})}</span>
		`;

		Dom.append(blockContent, annotation);
		this.previewBlockAnnotationLink = annotation.querySelector('.calendar-sharing-dialog-preview-block-annotation-link');

		if (this.previewBlockAnnotationLink)
		{
			if (!this.isSharingOn)
			{
				Dom.addClass(this.previewBlockAnnotationLink, 'calendar-sharing-dialog-preview-block-annotation-link-disabled');
			}

			Event.bind(this.previewBlockAnnotationLink, 'click', () => {
				this.openNewTab();
			});
		}

		return annotation
	}

	enableLinks()
	{
		this.links?.forEach((link) => {
			link.active = true;
		});
	}

	openNewTab()
	{
		window.open(Text.encode(this.links[0].url), '_blank');
	}

	toggle()
	{
		this.popup.toggle();
	}
}