import { Dom, Event, Loc, Runtime, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PULL as Pull } from 'pull.client';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';
import { PullRequests } from './pull-requests';
import { EditDescription } from './layout/edit-description';
import { EditPrivacy } from './layout/edit-privacy';
import { EditTitle } from './layout/edit-title';
import { EditAvatar } from './layout/edit-avatar';
import { ActionsButton } from './layout/actions-button';
import { MembersSection } from './layout/members-section';
import { ThemeSliderAdjuster } from './theme-slider-adjuster';
import { Controller } from 'socialnetwork.controller';
import { SpaceSettings } from './space-settings';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';
import type { GroupData } from './type';
import type { LogoData } from 'socialnetwork.logo';

import './group-settings.css';
import 'ui.icon-set.main';
import 'ui.icon-set.actions';

type Params = {
	groupData: GroupData,
	logo: LogoData,
}

export class GroupSettings extends EventEmitter
{
	#groupData: GroupData;
	#logo: LogoData;

	#circle: any;
	#layout: {
		wrap: HTMLElement,
		actionsButton: ActionsButton,
		editAvatar: EditAvatar,
		editTitle: EditTitle,
		editPrivacy: EditPrivacy,
		editDescription: EditDescription,
		membersSection: MembersSection,
		efficiencyHelper: HTMLElement,
	};

	SLIDER_WIDTH = 491;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Page');

		this.#groupData = params.groupData;
		this.#logo = params.logo;

		if (this.#isAvatar(this.#groupData.avatar))
		{
			this.#logo.id = encodeURI(this.#groupData.avatar);
		}

		this.#layout = {};

		this.#subscribeToPull();

		new ThemeSliderAdjuster({
			sliderWidth: this.SLIDER_WIDTH,
			checkSlider: (slider) => slider.options.data?.spaceSettingsSliderId === this.#groupData.id,
		}).bindSliderEvents();
	}

	openInSlider(): void
	{
		BX.SidePanel.Instance.open('spaces-settings-space-info-page', {
			cacheable: false,
			contentCallback: (slider) => {
				Dom.addClass(slider.getOverlay(), 'sn-group-settings_overlay');

				return this.#render();
			},
			width: this.SLIDER_WIDTH,
			data: {
				spaceSettingsSliderId: this.#groupData.id,
			},
			events: {
				onBeforeCloseComplete: this.#beforeCloseComplete.bind(this),
			},
		});
	}

	#beforeCloseComplete()
	{
		if (this.#circle)
		{
			this.#circle.stop();
		}
	}

	#subscribeToPull()
	{
		const pullRequests = new PullRequests(this.#groupData.id);
		pullRequests.subscribe('update', this.#handleUpdateSpace.bind(this));

		Pull.subscribe(pullRequests);
	}

	#handleUpdateSpace()
	{
		Controller.getGroupData(
			this.#groupData.id,
			[
				'AVATAR',
				'ACTIONS',
				'NUMBER_OF_MEMBERS',
				'LIST_OF_MEMBERS',
				'GROUP_MEMBERS_LIST',
				'PRIVACY_TYPE',
				'PIN',
				'USER_DATA',
				'COUNTERS',
				'DESCRIPTION',
				'EFFICIENCY',
				'SUBJECT_DATA',
				'DATE_CREATE',
			],
		).then((groupData: GroupData) => {
			this.#groupData = groupData;
			this.#update(this.#groupData);
		});
	}

	#update(groupData: GroupData): void
	{
		this.#groupData = groupData;

		if (this.#isAvatar(this.#groupData.avatar))
		{
			this.#logo = {
				id: this.#groupData.avatar,
				type: 'image',
			};

			this.#layout.editAvatar.setAvatar(this.#groupData.avatar);
		}

		this.#layout.editTitle.setTitle(this.#groupData.name);
		this.#layout.editPrivacy.setPrivacy(this.#groupData.privacyCode);
		this.#layout.membersSection.setMembers(this.#groupData.listOfMembers);
	}

	#render(): HTMLElement
	{
		this.#layout.wrap = Tag.render`
			<div class="sn-group-settings__container">
				<div class="sn-group-settings__toolbar">
					${this.#renderActionsButton()}
				</div>
				${this.#renderSpaceCard()}
				${this.#renderFooterSection()}
			</div>
		`;

		return this.#layout.wrap;
	}

	#renderThemesButton(): HTMLElement | string
	{
		if (!this.#groupData.actions.canEdit)
		{
			return '';
		}

		return new Button({
			className: 'sn-group-settings__icon-themes',
			text: Loc.getMessage('SN_GROUP_SETTINGS_THEMES'),
			color: ButtonColor.LIGHT_BORDER,
			size: ButtonSize.MEDIUM,
			dependOnTheme: true,
			round: true,
			onclick: () => BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(),
		}).render();
	}

	#renderActionsButton(): HTMLElement
	{
		this.#layout.actionsButton = new ActionsButton({
			isPin: this.#groupData.isPin,
			isSubscribed: this.#groupData.isSubscribed,
			actions: this.#groupData.actions,
			pinChanged: (isPin) => {
				this.#groupData.isPin = isPin;
				Controller.changePin(this.#groupData.id, this.#groupData.isPin);
			},
			followChanged: (isSubscribed) => {
				this.#groupData.isSubscribed = isSubscribed;
				Controller.setSubscription(this.#groupData.id, this.#groupData.isSubscribed);
			},
			leave: () => Controller
				.leaveGroup(this.#groupData.id)
				.then(() => Controller.openCommonSpace()),
			delete: () => {
				Controller.deleteGroup(this.#groupData.id).then((response) => {
					const errorMessage = response.data;

					if (Type.isStringFilled(errorMessage))
					{
						new MessageBox({
							message: errorMessage,
							buttons: MessageBoxButtons.OK,
						}).show();
					}
					else
					{
						Controller.openCommonSpace();
					}
				});
			},
		});

		return this.#layout.actionsButton.render();
	}

	#renderSpaceCard(): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__card">
				${this.#renderHeaderSection()}
				${this.#renderMembersSection()}
			</div>
		`;
	}

	#renderHeaderSection(): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__header-section">
				<div class="sn-group-settings__header">
					${this.#renderSpaceAvatar()}
					<div class="sn-group-settings__header-title">
						${this.#renderSpaceTitleEdit()}
						${this.#renderSpacePrivacy()}
					</div>
				</div>
				${this.#renderDescription()}
			</div>
		`;
	}

	#renderSpaceAvatar(): HTMLElement
	{
		this.#layout.editAvatar = new EditAvatar({
			logo: this.#logo,
			canEdit: this.#groupData.actions.canEdit,
			onChange: (file) => {
				Controller.updatePhoto(this.#groupData.id, file);
				this.emit('changeAvatar', file);
			},
		});

		return this.#layout.editAvatar.render();
	}

	#renderSpaceTitleEdit(): HTMLElement
	{
		this.#layout.editTitle = new EditTitle({
			name: this.#groupData.name,
			canEdit: this.#groupData.actions.canEdit,
			onChange: (name) => {
				this.#groupData.name = name;
				Controller.changeTitle(this.#groupData.id, this.#groupData.name);
				this.emit('changeTitle', this.#groupData.name);
			},
		});

		return this.#layout.editTitle.render();
	}

	#renderSpacePrivacy(): HTMLElement
	{
		this.#layout.editPrivacy = new EditPrivacy({
			privacyCode: this.#groupData.privacyCode,
			canEdit: this.#groupData.actions.canEdit,
			onChange: (privacyCode) => {
				this.#groupData.privacyCode = privacyCode;
				Controller.changePrivacy(this.#groupData.id, this.#groupData.privacyCode);
				this.emit('changePrivacy', this.#groupData.privacyCode);
			},
		});

		return this.#layout.editPrivacy.render();
	}

	#renderDescription(): HTMLElement
	{
		this.#layout.editDescription = new EditDescription({
			description: this.#groupData.description,
			canEdit: this.#groupData.actions.canEdit,
			onChange: (description) => {
				this.#groupData.description = description;
				Controller.changeDescription(this.#groupData.id, this.#groupData.description);
			},
		});

		return this.#layout.editDescription.render();
	}

	#renderMembersSection(): HTMLElement
	{
		this.#layout.membersSection = new MembersSection({
			listOfMembers: this.#groupData.listOfMembers,
			onShowMembers: () => Controller.openGroupUsers('all'),
		});

		return this.#layout.membersSection.render();
	}

	#isAvatar(avatar): boolean
	{
		return Type.isStringFilled(avatar) && avatar !== '/bitrix/images/1.gif';
	}

	#renderFooterSection(): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__footer-section">
				<div class="sn-group-settings__footer-main">
					${this.#renderEfficiency()}
					<div class="sn-group-settings__footer-buttons-container">
						${this.#renderAccess()}
						${this.#renderSettings()}
					</div>
				</div>
				<div class="sn-group-settings__footer">
					${this.#getFooterText()}
				</div>
			</div>
		`;
	}

	#getFooterText(): string
	{
		return Loc.getMessage('SN_GROUP_SETTINGS_SPACE_CARD_FOOTER', {
			'#DATE#': this.#groupData.dateCreate,
		});
	}

	#renderEfficiency(): HTMLElement
	{
		if (!Type.isNumber(this.#groupData.efficiency))
		{
			return this.#renderEfficiencyEmptyState();
		}

		return Tag.render`
			<div class="sn-group-settings__efficiency-container">
				${this.#renderEfficiencyNode()}
				<div class="sn-group-settings__efficiency-text">
					${Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY')}
				</div>
				${this.#renderEfficiencyHelper()}
			</div>
		`;
	}

	#renderEfficiencyEmptyState()
	{
		return Tag.render`
			<div class="sn-group-settings__efficiency-container">
				<div class="sn-group-settings__efficiency-container-empty-state-icon"></div>
				<div class="sn-group-settings__efficiency-text">
					${Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY_UNAVAILABLE')}
				</div>
			</div>
		`;
	}

	#renderEfficiencyHelper(): HTMLElement
	{
		this.#layout.efficiencyHelper = Tag.render`
			<div class="sn-group-settings__efficiency-helper">
				${Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY_HELPER')}
			</div>
		`;

		Event.bind(this.#layout.efficiencyHelper, 'click', () => {
			top.BX.Helper.show('redirect=detail&code=6576263');
		});

		return this.#layout.efficiencyHelper;
	}

	#renderEfficiencyNode(): HTMLElement
	{
		const efficiencyNode = Tag.render`
			<div class="sn-group-settings__efficiency"></div>
		`;

		Runtime.loadExtension('ui.graph.circle').then((exports) => {
			const { Circle } = exports;
			this.#circle = new Circle(efficiencyNode, 100, this.#groupData.efficiency, null, null);
			this.#circle.show();
		});

		return efficiencyNode;
	}

	#renderAccess(): HTMLElement
	{
		const canEdit = this.#groupData.actions.canEditFeatures;

		const rolesNode = Tag.render`
			<div class="sn-group-settings__footer-button ${canEdit ? '' : '--disabled'}">
				<div class="ui-icon-set --lock"></div>
				<div>${Loc.getMessage('SN_GROUP_SETTINGS_MENU_ROLES')}</div>
			</div>
		`;

		if (canEdit)
		{
			Event.bind(rolesNode, 'click', () => Controller.openGroupFeatures());
		}

		return rolesNode;
	}

	#renderSettings(): HTMLElement
	{
		const canEdit = this.#groupData.actions.canEdit;

		const settingsNode = Tag.render`
			<div class="sn-group-settings__footer-button ${canEdit ? '' : '--disabled'}">
				<div class="ui-icon-set --settings-4"></div>
				<div>${Loc.getMessage('SN_GROUP_SETTINGS_SPACE_SETTINGS')}</div>
			</div>
		`;

		if (canEdit)
		{
			Event.bind(settingsNode, 'click', this.#openSpaceSettings.bind(this));
		}

		return settingsNode;
	}

	#openSpaceSettings(): void
	{
		new SpaceSettings({
			width: this.SLIDER_WIDTH,
			spaceId: this.#groupData.id,
		}).openInSlider();
	}
}
