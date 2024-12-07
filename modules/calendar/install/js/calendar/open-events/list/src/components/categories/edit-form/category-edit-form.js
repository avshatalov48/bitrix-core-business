import { PopupManager } from 'main.popup';
import { TagSelector } from 'ui.entity-selector';
import { Switcher, SwitcherSize } from 'ui.switcher';
import { CategoryManager } from '../../../data-manager/category-manager/category-manager';
import { AppSettings } from '../../../helpers/app-settings';
import { CategoryModel } from '../../../model/category/category';
import { BaseEvent } from 'main.core.events';
import 'ui.icon-set.main';
import './category-edit-form.css';

type Params = {
	category: CategoryModel,
	create: boolean,
};

export const CategoryEditForm = {
	data(): Object
	{
		return {
			id: 'calendar-open-events-category-edit-popup',
			params: {},
			category: null,
			create: false,
			popup: null,
			name: '',
			description: '',
			closed: false,
			selectedChannelId: null,
		};
	},
	computed: {
		isEdit(): boolean
		{
			return !this.create;
		},
	},
	methods: {
		show(params: Params = {}): void
		{
			this.create = params.create;
			this.category = params.category;

			if (this.category)
			{
				if (!this.category.channel)
				{
					CategoryManager.getChannelInfo(this.category.id).then((channelInfo: ChannelInfo) => {
						this.category.channel = channelInfo;
					});
				}

				this.name = this.category.name;
				this.description = this.category.description;
				this.closed = this.category.closed;
			}

			PopupManager.getPopupById(this.id)?.destroy();

			this.popup = PopupManager.create({
				id: this.id,
				autoHide: true,
				autoHideHandler: (event) => {
					const isClickInside = this.popup.getPopupContainer().contains(event.target);

					let isClickUserSelector = false;
					if (this.userSelector)
					{
						const userSelectorPopup = this.userSelector.getDialog().getPopup();
						isClickUserSelector = userSelectorPopup.getPopupContainer().contains(event.target);
					}

					let isClickChannelSelector = false;
					if (this.channelSelector)
					{
						const channelSelectorPopup = this.channelSelector.getDialog().getPopup();
						isClickChannelSelector = channelSelectorPopup.getPopupContainer().contains(event.target);
					}

					return !isClickInside && !isClickUserSelector && !isClickChannelSelector;
				},
				width: 600,
				content: this.$refs.popupContent,
				className: 'calendar-open-events-category-edit-popup-container',
				titleBar: true,
				draggable: true,
			});

			this.renderSwitcher();
			if (this.create)
			{
				this.renderChannelSelector();
				this.renderUserSelector();
			}

			this.popup.show();

			this.$refs.inputName.focus();
		},
		async onCreateButtonClick(): Promise<void>
		{
			const attendees = this.userSelector?.getTags()
				.filter((tag) => tag.entityId === 'user')
				.map((tag) => tag.id)
			;
			const departmentIds = this.userSelector?.getTags()
				.filter((tag) => tag.entityId === 'department')
				.map((tag) => tag.id)
			;

			await CategoryManager.addCategory({
				name: this.name,
				description: this.description,
				closed: this.closed,
				attendees: this.closed ? attendees : [],
				departmentIds: this.closed ? departmentIds : [],
				channelId: this.selectedChannelId,
			});

			this.clearFields();

			this.popup.close();
		},
		async onSaveButtonClick(): Promise<void>
		{
			await CategoryManager.updateCategory({
				id: this.category.id,
				name: this.name,
				description: this.description,
			});

			this.clearFields();

			this.popup.close();
		},
		onCancelButtonClick(): void
		{
			this.clearFields();

			this.popup.close();
		},
		clearFields(): void
		{
			this.name = '';
			this.description = '';
			this.closed = false;
			this.userSelector?.getTags().forEach((tag) => {
				if (tag.getEntityId() === 'user' && tag.getId() === AppSettings.currentUserId)
				{
					return;
				}

				this.userSelector.removeTag(tag, false);
			});
			this.channelSelector?.getTags().forEach(tag => this.channelSelector.removeTag(tag, false));
			this.selectedChannelId = null;
		},
		renderSwitcher(): void
		{
			if (this.switcher)
			{
				this.switcher.check(this.closed);
				this.switcher.disable(Boolean(this.selectedChannelId));

				return;
			}

			this.switcher = new Switcher({
				node: this.$refs.closedSwitcher,
				checked: this.closed,
				size: SwitcherSize.extraSmall,
				disabled: Boolean(this.selectedChannelId),
				handlers: {
					toggled: () => {
						this.closed = this.switcher.isChecked();
					},
				},
			});
		},
		renderUserSelector(): void
		{
			if (this.userSelector)
			{
				this.userSelector.renderTo(this.$refs.userSelector);

				return;
			}

			const currentUserItem = ['user', AppSettings.currentUserId];

			this.userSelector = new TagSelector({
				dialogOptions: {
					context: 'CALENDAR_OPEN_EVENTS_CATEGORY_EDIT_FORM',
					showAvatars: true,
					dropdownMode: true,
					preload: true,
					entities: [
						{
							id: 'user',
						},
						{
							id: 'department',
							options: {
								selectMode: 'usersAndDepartments',
								allowFlatDepartments: true,
								allowSelectRootDepartment: true,
							},
						},
					],
					preselectedItems: [currentUserItem],
					undeselectedItems: [currentUserItem],
				},
			});

			this.userSelector.renderTo(this.$refs.userSelector);
		},
		renderChannelSelector(): void
		{
			if (this.channelSelector)
			{
				this.channelSelector.renderTo(this.$refs.channelSelector);

				return;
			}

			this.channelSelector = new TagSelector({
				multiple: false,
				dialogOptions: {
					context: 'CALENDAR_OPEN_EVENTS_CATEGORY_EDIT_FORM',
					dropdownMode: true,
					preload: true,
					entities: [
						{
							id: 'im-channel',
							dynamicLoad: true,
						},
					],
					events: {
						'Item:onSelect': this.onChannelSelected.bind(this),
						'Item:onDeselect': this.onChannelDeselected.bind(this),
					},
					multiple: false,
				},
			});

			this.channelSelector.renderTo(this.$refs.channelSelector);
		},
		onChannelSelected(event: BaseEvent): void
		{
			const { item: tag } = event.getData();
			this.selectedChannelId = tag.id;
			this.closed = tag.customData.get('closed');

			if (!this.name || !this.userChangedName)
			{
				this.name = tag.getTitle();
			}

			this.renderSwitcher();
		},
		onChannelDeselected(event: BaseEvent): void
		{
			const { item: tag } = event.getData();
			this.selectedChannelId = null;
			this.closed = false;

			if (this.name === tag.getTitle())
			{
				this.name = '';
				this.userChangedName = false;
			}

			this.renderSwitcher();
		},
		getFirstLetters(text): string
		{
			const words = text.split(/[\s,]/).filter((word) => /[\p{L}\p{N} ]/u.test(word[0]));

			return (words[0]?.[0] ?? '') + (words[1]?.[0] ?? '');
		},
		onNameInput(): void
		{
			this.userChangedName = true;
		}
	},
	template: `
		<div class="calendar-open-events-category-edit-popup" ref="popupContent">
			<input
				class="calendar-open-events-category-edit-name-input"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_NAME')"
				v-model="name"
				@input="onNameInput"
				ref="inputName"
			>
			<div class="calendar-open-events-category-edit-channel --edit" v-show="create">
				<div class="ui-icon-set --speaker-mouthpiece" v-if="create"></div>
				<div class="calendar-open-events-category-edit-channel-text" v-if="create">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CHOOSE_CHANNEL') }}
				</div>
				<div
					class="calendar-open-events-category-edit-channel-selector"
					ref="channelSelector"
					v-show="create"
				></div>
			</div>
			<div class="calendar-open-events-category-edit-channel --edit" v-if="!create && !category?.channel">
				<div class="ui-icon-set --speaker-mouthpiece"></div>
				<div class="calendar-open-events-category-edit-channel-loader"></div>
			</div>
			<div class="calendar-open-events-category-edit-channel" v-if="category?.channel">
				<div class="ui-icon-set --speaker-mouthpiece"></div>
				<img
					v-if="category.channel.avatar"
					class="calendar-open-events-category-edit-channel-avatar"
					:src="category.channel.avatar"
				>
				<div
					v-if="!category.channel.avatar && getFirstLetters(category.channel.title)"
					class="calendar-open-events-category-edit-channel-avatar"
					:style="'background-color: ' + category.channel.color"
				>
					{{ getFirstLetters(category.channel.title) }}
				</div>
				<div class="calendar-open-events-category-edit-channel-name">{{ category.channel.title }}</div>
			</div>
			<div
				class="calendar-open-events-category-edit-close"
				:class="{
					'--closed': closed,
					'--disabled': !create,
				}"
			>
				<div class="calendar-open-events-category-edit-close-switcher">
					<div ref="closedSwitcher"></div>
				</div>
				<div class="calendar-open-events-category-edit-close-body">
					<div class="calendar-open-events-category-edit-close-title">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CLOSE') }}
					</div>
					<div class="calendar-open-events-category-edit-close-hint">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CLOSE_HINT') }}
					</div>
					<div
						class="calendar-open-events-category-edit-close-users"
						ref="userSelector"
						v-show="create && closed && !selectedChannelId"
					></div>
				</div>
			</div>
			<textarea
				class="calendar-open-events-category-edit-description-textarea"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_DESCRIPTION')"
				v-model="description"
			></textarea>
			<div class="calendar-open-events-category-edit-buttons">
				<div
					v-if="create"
					class="calendar-open-events-category-edit-button-create"
					@click="onCreateButtonClick"
				>
					<div class="ui-icon-set --calendar-1"></div>
					<div class="calendar-open-events-category-edit-button-create-text">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CREATE') }}
					</div>
				</div>
				<div
					v-if="isEdit"
					class="calendar-open-events-category-edit-button-create"
					@click="onSaveButtonClick"
				>
					<div class="ui-icon-set --calendar-1"></div>
					<div class="calendar-open-events-category-edit-button-create-text">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_SAVE') }}
					</div>
				</div>
				<div class="calendar-open-events-category-edit-button-cancel" @click="onCancelButtonClick">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CANCEL') }}
				</div>
			</div>
		</div>
	`,
};
