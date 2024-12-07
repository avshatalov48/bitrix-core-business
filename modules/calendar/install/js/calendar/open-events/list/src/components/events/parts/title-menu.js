import { Menu } from 'main.popup';
import { Messenger } from 'im.public.iframe';
import { CategoryManager } from '../../../data-manager/category-manager/category-manager';
import { CategoryModel } from '../../../model/category/category';
import { CategoryEditForm } from '../../categories/edit-form/category-edit-form';
import 'ui.icon-set.main';

export const TitleMenu = {
	props: {
		category: CategoryModel,
	},
	data(): Object {
		return {
			menu: Menu,
		};
	},
	methods: {
		openMenu(): void
		{
			this.menu = new Menu({
				bindElement: this.$refs.menuIcon,
				closeByEsc: true,
				items: this.getMenuItems(),
			});

			this.menu.show();
		},
		redrawMenu(): void
		{
			const itemIds = this.menu.getMenuItems().map((item) => item.getId());
			itemIds.forEach((id) => this.menu.removeMenuItem(id, {
				destroyEmptyPopup: false,
			}));
			this.getMenuItems().forEach((item) => this.menu.addMenuItem(item));
		},
		getMenuItems(): any[]
		{
			const items = [
				this.getInfoItem(),
				this.getOpenChatItem(),
			];

			if (!this.category.isBanned)
			{
				items.push(this.getMuteItem());
			}

			items.push(this.getBanItem());

			if (this.category.permissions.edit === true)
			{
				items.push(this.getEditItem());
			}

			if (this.category.permissions.delete === true)
			{
				items.push(this.getDeleteItem());
			}

			return items;
		},
		getInfoItem()
		{
			return {
				html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --info-circle"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_ABOUT_CATEGORY')}</span>
					</div>
				`,
				onclick: () => {
					this.menu.close();
					alert('info');
				},
			};
		},
		getOpenChatItem(): any
		{
			return {
				html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --chats-2"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_OPEN_CHANNEL')}</span>
					</div>
				`,
				onclick: () => {
					this.menu.close();
					Messenger.openChat(`chat${this.category.channelId}`);
				},
			};
		},
		getMuteItem(): any
		{
			return {
				html: this.renderMuteItem(),
				onclick: () => {
					this.category.isMuted = !this.category.isMuted;

					this.muteCategory(this.category.isMuted);

					this.redrawMenu();
				},
			};
		},
		renderMuteItem(): string
		{
			const icon = this.category.isMuted ? '--notifications-off' : '--bell-1';

			const text = this.category.isMuted
				? this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_ENABLE_NOTIFY')
				: this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_DISABLE_NOTIFY')
			;

			return `
				<div class="calendar-open-events-list-menu-item">
					<div class="ui-icon-set ${icon}"></div>
					<span>${text}</span>
				</div>
			`;
		},
		getBanItem(): any
		{
			return {
				html: this.renderBanItem(),
				onclick: () => {
					this.category.isBanned = !this.category.isBanned;

					this.banCategory(this.category.isBanned);

					this.redrawMenu();
				},
			};
		},
		renderBanItem(): any
		{
			const icon = this.category.isBanned ? '--bell-1' : '--unavailable';

			const text = this.category.isBanned
				? this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_SUBSCRIBE')
				: this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_UNSUBSCRIBE')
			;

			return `
				<div class="calendar-open-events-list-menu-item">
					<div class="ui-icon-set ${icon}"></div>
					<span>${text}</span>
				</div>
			`;
		},
		getEditItem(): any
		{
			return {
				html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --pencil-40"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_EDIT')}</span>
					</div>
				`,
				onclick: () => {
					this.menu.close();
					this.openEditCategoryForm();
				},
			};
		},
		getDeleteItem(): any
		{
			return {
				html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --cross-40"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_DELETE')}</span>
					</div>
				`,
				onclick: () => {
					this.menu.close();
					this.deleteCategory();
				},
			};
		},
		muteCategory(isMuted: boolean): void
		{
			void CategoryManager.setMute(this.category.id, isMuted);
		},
		banCategory(isBanned: boolean): void
		{
			void CategoryManager.setBan(this.category.id, isBanned);
		},
		openEditCategoryForm(): void
		{
			this.$refs.editForm.show({
				category: this.category,
			});
		},
		deleteCategory(): void
		{
			alert('delete category ' + this.category.id);
		},
	},
	components: {
		CategoryEditForm,
	},
	template: `
		<div
			class="calendar-open-events-list-item__list-header__menu ui-icon-set --more-information"
			@click="openMenu"
			ref="menuIcon"
		></div>
		<CategoryEditForm ref="editForm"/>
	`,
}
