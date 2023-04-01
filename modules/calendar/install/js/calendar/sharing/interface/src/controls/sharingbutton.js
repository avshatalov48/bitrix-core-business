import {Loc, Tag, Dom, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Util} from 'calendar.util';
import Dialog from './dialog';
import 'ui.switcher';
import 'spotlight';

export default class SharingButton
{
	HELP_DESK_CODE = 17198666;

	constructor(options = {})
	{
		this.wrap = options.wrap;
		this.userId = options.userId;

		this.subscribeToEvents();
	}

	subscribeToEvents()
	{
		EventEmitter.subscribe(
			'Calendar.Sharing.copyLinkButtonContainer:onMouseEnter',
			() => this.handleCopyLinkButtonContainerMouseEnter(),
		);
		EventEmitter.subscribe(
			'Calendar.Sharing.Dialog:onClose',
			() => this.handleSharingDialogClose(),
		);
	}

	show()
	{
		this.button = new BX.UI.Button({
			text: Loc.getMessage('SHARING_BUTTON_TITLE'),
			round: true,
			size: BX.UI.Button.Size.EXTRA_SMALL,
			color: BX.UI.Button.Color.LIGHT_BORDER,
			className: 'ui-btn-themes calendar-sharing__btn',
			onclick: (button, event) => {
				if (!this.switcher.getNode().contains(event.target) )
				{
					this.handleSharingButtonClick();
				}
			},
			events: {
				'mouseenter': () => this.handleSharingButtonMouseEnter(),
				'mouseleave': () => this.handleSharingButtonMouseLeave(),
			},
		});

		this.button.renderTo(this.wrap);
		this.renderSwitcher();
	}

	handleCopyLinkButtonContainerMouseEnter()
	{
		if (!this.switcher?.disabled && !this.switcherSpotlight)
		{
			this.showSwitcherSpotlight();
		}
	}

	handleSharingDialogClose()
	{
		this.hideSwitcherSpotlight();
	}

	showSwitcherSpotlight()
	{
		this.switcherSpotlight = new BX.SpotLight({
			targetElement: this.switcherWrap,
			targetVertex: 'middle-center',
			left: -17,
			top: -5,
			lightMode: true,
			events: {
				'onTargetEnter': () => {
					this.hideSwitcherSpotlight()
				},
			},
		});
		this.switcherSpotlight.show();
	}

	hideSwitcherSpotlight()
	{
		if (this.switcherSpotlight)
		{
			this.switcherSpotlight.close();
			this.switcherSpotlight = null;
		}
	}

	handleSharingButtonClick()
	{
		this.clearInfoPopupShowTimeOut();
		this.getSharingDialog().toggle();
	}

	handleSharingButtonMouseEnter()
	{
		if (!this.sharingDialog?.isShown() && !this.switcher.isChecked())
		{
			this.infoPopupShowTimeout = setTimeout(() => this.showInfoPopup(), 1000);
		}
	}

	handleSharingButtonMouseLeave()
	{
		this.clearInfoPopupShowTimeOut();
	}

	clearInfoPopupShowTimeOut()
	{
		if (this.infoPopupShowTimeout)
		{
			clearTimeout(this.infoPopupShowTimeout);
			this.infoPopupShowTimeout = null;
		}
	}

	showInfoPopup()
	{
		if (this.sharingDialog?.isShown())
		{
			return;
		}

		if (this.infoPopup)
		{
			this.infoPopup.destroy();
		}

		const infoPopupWidth = 320;
		this.infoPopup = new BX.Main.Popup({
			bindElement: this.button.getContainer(),
			width: infoPopupWidth,
			padding: 15,
			autoHide: true,
			closeByEsc: true,
			closeIcon: true,
			content: this.getInfoPopupContent(),
			angle: { offset: infoPopupWidth / 2 },
			offsetLeft: (this.button.getContainer().offsetWidth / 2) - infoPopupWidth / 2.5,
		});

		this.infoPopup.show();
	}

	getInfoPopupContent()
	{
		const content = Tag.render`<div></div>`;
		const mainContent1 = Tag.render`
			<div class="calendar-sharing__info-popup_main-content">
				${Loc.getMessage('SHARING_INFO_POPUP_CONTENT_1')}
			</div>
		`;
		Dom.append(mainContent1, content);
		const mainContent2 = Tag.render`
			<div class="calendar-sharing__info-popup_main-content">
				${Loc.getMessage('SHARING_INFO_POPUP_CONTENT_2')}
			</div>
		`;
		Dom.append(mainContent2, content);
		const detailLink = Tag.render`
			<a class="calendar-sharing__info-popup_detail-link">
				${Loc.getMessage('SHARING_DIALOG_MORE_DETAILED')}
			</a>
		`;
		Event.bind(detailLink, 'click', () => this.handleDetailLinkClick())
		Dom.append(detailLink, content);

		return content;
	}

	handleDetailLinkClick()
	{
		this.openHelpDesk();
	}

	openHelpDesk()
	{
		top.BX.Helper.show('redirect=detail&code=' + this.HELP_DESK_CODE);
	}

	getSharingDialog()
	{
		if (!this.sharingDialog)
		{
			this.sharingDialog = new Dialog({
				bindElement: this.button.getContainer(),
				userId: this.userId,
				isSwitchCheckedOnStart: this.switcher.isChecked(),
				switcherNode: this.switcher.getNode(),
			});
		}

		return this.sharingDialog;
	}

	getSwitcherContainer()
	{
		const switcherContainer = Tag.render`
			<div class="calendar-sharing__switcher">
				
			</div>
		`;

		return switcherContainer;
	}

	getSwitcherDivider()
	{
		const switcherDivider = Tag.render`
			<div class="calendar-sharing__switcher_divider"></div>
		`;

		return switcherDivider;
	}

	renderSwitcher()
	{
		Dom.append(this.getSwitcherDivider(), this.button.button);
		this.switcherWrap = Tag.render`<div class="calendar-sharing__switcher-wrap"></div>`;
		Dom.append(this.switcherWrap, this.button.button);
		Event.bind(this.switcherWrap, 'click', this.handleSwitcherWrapClick.bind(this), {capture: true});

		this.switcher = new BX.UI.Switcher({
			node: this.getSwitcherContainer(),
			checked: Util.getSharingConfig()?.isEnabled === 'true',
			color: 'green',
			size: 'small',
			handlers: {
				toggled: () => this.handleSwitcherToggled(),
			},
		});

		this.switcher.renderTo(this.switcherWrap);
	}

	handleSwitcherWrapClick(event)
	{
		if (this.switcher.isChecked())
		{
			this.showWarningPopup();
			event.stopPropagation();
		}
	}

	showWarningPopup()
	{
		if (!this.warningPopup)
		{
			this.warningPopup = new BX.UI.Dialogs.MessageBox({
				title: Loc.getMessage('SHARING_WARNING_POPUP_TITLE'),
				message: Loc.getMessage('SHARING_WARNING_POPUP_CONTENT'),
				buttons: this.getWarningPopupButtons(),
				popupOptions: {
					autoHide: true,
					closeByEsc: true,
					draggable: true,
					closeIcon: true,
					minWidth: 365,
					maxWidth: 365,
				},
			});
		}

		this.warningPopup.show();
	}

	getWarningPopupButtons()
	{
		return [this.getSubmitButton(), this.getCancelButton()]
	}

	getSubmitButton()
	{
		return new BX.UI.Button({
			size: BX.UI.Button.Size.MEDIUM,
			color: BX.UI.Button.Color.DANGER,
			text: Loc.getMessage('SHARING_WARNING_POPUP_SUBMIT_BUTTON'),
			events: {
				click: () => this.handleSubmitButtonClick(),
			}
		});
	}

	getCancelButton()
	{
		return new BX.UI.Button({
			size: BX.UI.Button.Size.MEDIUM,
			color: BX.UI.Button.Color.LIGHT_BORDER,
			text: Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
			events: {
				click: () => this.handleCancelButtonClick(),
			}
		});
	}

	handleSubmitButtonClick()
	{
		this.switcher.toggle();
		this.warningPopup.close();
	}

	handleCancelButtonClick()
	{
		this.warningPopup.close();
	}

	handleSwitcherToggled()
	{
		if (this.switcher.isChecked())
		{
			const sharingDialog = this.getSharingDialog();
			if(!sharingDialog.isShown())
			{
				sharingDialog.toggle();
			}
			sharingDialog.enableLinks();

			EventEmitter.emit('Calendar.Sharing.copyLinkButton:onSwitchToggled', this.switcher.isChecked());
		}
		else
		{
			BX.ajax.runAction('calendar.api.sharingajax.deleteUserLinks');
			this.getSharingDialog().destroy();
			this.sharingDialog = null;
			this.warningPopup.close();
		}
		BX.userOptions.save('calendar', 'sharing', 'isEnabled', this.switcher.isChecked());
	}
}