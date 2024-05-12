import { Dom, Event, Loc, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Button, ButtonSize, ButtonColor, ButtonIcon } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Util } from 'calendar.util';
import DialogNew from './dialog-new';
import 'ui.switcher';
import 'spotlight';
import { Guide } from 'ui.tour';
import { Counter } from 'ui.cnt';

export default class SharingButton
{
	PAY_ATTENTION_TO_NEW_FEATURE_DELAY = 1000;

	PAY_ATTENTION_TO_NEW_FEATURE_FIRST = 'first-feature';
	PAY_ATTENTION_TO_NEW_FEATURE_NEW = 'new-feature';
	PAY_ATTENTION_TO_NEW_FEATURE_REMIND = 'remind-feature';
	PAY_ATTENTION_TO_NEW_FEATURE_JOINT = 'joint-sharing'

	PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS = [this.PAY_ATTENTION_TO_NEW_FEATURE_FIRST];
	PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS = [
		this.PAY_ATTENTION_TO_NEW_FEATURE_NEW,
		this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND,

	];

	AVAILABLE_PAY_ATTENTION_TO_NEW_FEATURE_MODS = [
		...this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS,
		...this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS,
	];

	constructor(options = {})
	{
		this.wrap = options.wrap;
		this.userInfo = options.userInfo || null;
		this.sharingConfig = Util.getSharingConfig();
		this.sharingUrl = this.sharingConfig?.url || null;
		this.linkHash = this.sharingConfig?.hash || null;
		this.sharingRule = this.sharingConfig?.rule || null;
		this.payAttentionToNewFeatureMode = options.payAttentionToNewFeature;
		this.sharingFeatureLimit = options.sharingFeatureLimit;
		this.sharingSettingsCollapsed = options.sharingSettingsCollapsed;
		this.sortJointLinksByFrequentUse = options.sortJointLinksByFrequentUse;
	}

	show()
	{
		Dom.addClass(this.wrap, 'calendar-sharing__btn-wrap');
		this.button = new Button({
			text: Loc.getMessage('SHARING_BUTTON_TITLE'),
			round: true,
			size: ButtonSize.EXTRA_SMALL,
			color: ButtonColor.LIGHT_BORDER,
			icon: this.sharingFeatureLimit ? ButtonIcon.LOCK : null,
			className: 'ui-btn-themes calendar-sharing__btn',
			onclick: (button, event) => {
				if (!this.switcher.getNode().contains(event.target))
				{
					this.handleSharingButtonClick();
				}
			},
		});

		this.button.renderTo(this.wrap);
		this.renderSwitcher();

		if (this.payAttentionToNewFeatureMode === this.PAY_ATTENTION_TO_NEW_FEATURE_JOINT)
		{
			setTimeout(() => {
				if (BX.SidePanel.Instance.getTopSlider() === null)
				{
					this.payAttentionToNewFeatureWithText();
					BX.ajax.runAction('calendar.api.sharingajax.disableOptionPayAttentionToNewSharingFeature');
				}
			}, this.PAY_ATTENTION_TO_NEW_FEATURE_DELAY);
		}
	}

	handleSharingButtonClick()
	{
		if (this.sharingFeatureLimit)
		{
			top.BX.UI.InfoHelper.show('limit_office_calendar_free_slots');

			return;
		}

		if (this.isSharingEnabled())
		{
			this.openDialog();
		}
		else
		{
			this.switcher.toggle();
		}
	}

	getSwitcherContainer()
	{
		return Tag.render`
			<div class="calendar-sharing__switcher"></div>
		`;
	}

	getSwitcherDivider()
	{
		return Tag.render`
			<div class="calendar-sharing__switcher_divider"></div>
		`;
	}

	renderSwitcher()
	{
		Dom.append(this.getSwitcherDivider(), this.wrap);
		this.switcherWrap = Tag.render`<div class="calendar-sharing__switcher-wrap"></div>`;
		Dom.append(this.switcherWrap, this.wrap);
		Event.bind(this.switcherWrap, 'click', this.handleSwitcherWrapClick.bind(this), { capture: true });

		this.switcher = new BX.UI.Switcher({
			node: this.getSwitcherContainer(),
			checked: this.isSharingEnabled() && !this.sharingFeatureLimit,
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

	handleSwitcherToggled()
	{
		if (this.sharingFeatureLimit && this.switcher.isChecked())
		{
			top.BX.UI.InfoHelper.show('limit_office_calendar_free_slots');
			this.switcher.toggle();

			return;
		}

		if (this.isToggledAfterErrorOccurred())
		{
			return;
		}

		if (this.switcher.isChecked())
		{
			this.enableSharing();
		}
		else
		{
			this.disableSharing();
		}
	}

	isToggledAfterErrorOccurred()
	{
		return this.switcher.isChecked() === this.isSharingEnabled();
	}

	isSharingEnabled()
	{
		return Type.isString(this.sharingUrl);
	}

	enableSharing()
	{
		const action = 'calendar.api.sharingajax.enableUserSharing';
		const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';

		BX.ajax.runAction(action)
			.then((response) => {
				this.sharingUrl = response.data.url;
				this.linkHash = response.data.hash;
				this.sharingRule = response.data.rule;
				this.openDialog();

				EventEmitter.emit(
					event,
					{
						isChecked: this.switcher.isChecked(),
						url: response.data.url,
					},
				);
			})
			.catch(() => {
				this.switcher.toggle();
			})
		;
	}

	openDialog()
	{
		this.pulsar?.close();
		Dom.remove(this.counterNode);

		if (!this.newDialog)
		{
			this.newDialog = new DialogNew({
				bindElement: this.button.getContainer(),
				sharingUrl: this.sharingUrl,
				linkHash: this.linkHash,
				sharingRule: this.sharingRule,
				context: 'calendar',
				calendarSettings: {
					weekHolidays: Util.config.week_holidays,
					weekStart: Util.config.week_start,
					workTimeStart: Util.config.work_time_start,
					workTimeEnd: Util.config.work_time_end,
				},
				userInfo: this.userInfo,
				settingsCollapsed: this.sharingSettingsCollapsed,
				sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
			});
		}

		if (!this.newDialog.isShown())
		{
			this.newDialog.show();
		}
	}

	disableSharing()
	{
		const action = 'calendar.api.sharingajax.disableUserSharing';
		const event = 'Calendar.Sharing.copyLinkButton:onSharingDisabled';
		this.warningPopup.close();

		BX.ajax.runAction(action)
			.then(() => {
				this.sharingUrl = null;
				if (this.newDialog)
				{
					this.newDialog.destroy();
					this.newDialog = null;
				}
				EventEmitter.emit(event, { isChecked: this.switcher.isChecked() });
			})
			.catch(() => {
				this.switcher.toggle();
			})
		;
	}

	showWarningPopup()
	{
		if (!this.warningPopup)
		{
			this.warningPopup = new MessageBox({
				title: Loc.getMessage('SHARING_WARNING_POPUP_TITLE_1'),
				message: Loc.getMessage('SHARING_WARNING_POPUP_CONTENT_2'),
				buttons: this.getWarningPopupButtons(),
				popupOptions: {
					autoHide: true,
					closeByEsc: true,
					draggable: false,
					closeIcon: true,
					minWidth: 365,
					maxWidth: 385,
					minHeight: 180,
				},
			});
		}

		this.warningPopup.show();
	}

	getWarningPopupButtons()
	{
		return [this.getSubmitButton(), this.getCancelButton()];
	}

	getSubmitButton()
	{
		return new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.DANGER,
			text: Loc.getMessage('SHARING_WARNING_POPUP_SUBMIT_BUTTON_NEW_MSGVER_1'),
			events: {
				click: () => this.handleSubmitButtonClick(),
			},
		});
	}

	getCancelButton()
	{
		return new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
			events: {
				click: () => this.handleCancelButtonClick(),
			},
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

	payAttentionToNewFeature(mode)
	{
		if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS.includes(mode))
		{
			this.payAttentionToNewFeatureWithoutText();
		}

		if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS.includes(mode))
		{
			this.payAttentionToNewFeatureWithText();
		}
	}

	payAttentionToNewFeatureWithoutText()
	{
		this.pulsar = this.getPulsar(this.wrap, false);
		this.pulsar.show();
		Event.bind(this.pulsar.container, 'click', () => {
			this.handleSharingButtonClick();
		});

		this.counterNode = (new Counter({
			value: 1,
			color: Counter.Color.DANGER,
			size: Counter.Size.MEDIUM,
			animation: false,
		})).getContainer();
		Dom.addClass(this.counterNode, 'calendar-sharing__new-feature-counter');
		Dom.append(this.counterNode, this.wrap);
	}

	payAttentionToNewFeatureWithText()
	{
		const title = Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_JOINT_TITLE');
		const text = Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_JOINT_TEXT');

		const guide = this.getGuide(title, text);
		const pulsar = this.getPulsar(this.wrap);

		guide.showNextStep();
		guide.getPopup().setAngle({ offset: 210 });

		pulsar.show();
	}

	getGuide(title, text)
	{
		const guide = new Guide({
			simpleMode: true,
			onEvents: true,
			steps: [
				{
					target: this.wrap,
					title,
					text,
					position: 'bottom',
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
				},
			],
		});
		const guidePopup = guide.getPopup();
		Dom.addClass(guidePopup.popupContainer, 'calendar-popup-ui-tour-animate');
		guidePopup.setWidth(400);
		Dom.style(guidePopup.getContentContainer(), 'paddingRight', getComputedStyle(guidePopup.closeIcon).width);

		return guide;
	}

	getPulsar(target, hideOnHover = true)
	{
		const pulsar = new BX.SpotLight({
			targetElement: target,
			targetVertex: 'middle-center',
			lightMode: true,
		});
		if (hideOnHover)
		{
			pulsar.bindEvents({
				onTargetEnter: () => pulsar.close(),
			});
		}

		return pulsar;
	}
}
