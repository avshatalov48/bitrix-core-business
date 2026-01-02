import { CloseIconSize } from 'main.popup';
import { Tooltip } from 'ui.dialogs.tooltip';
import { CollabEntityType, PromoId } from 'im.v2.const';
import { PromoManager } from 'im.v2.lib.promo';

import { CollabTooltipContent } from '../../classes/tooltip-content/tooltip-content';
import { IconKey } from '../../classes/tooltip-content/icon-key';
import { EntityLink } from './components/entity-link';

import './css/entities-panel.css';

import type { ImModelChat, ImModelCollabInfo } from 'im.v2.model';
import type { PromoParams } from 'im.v2.provider.pull';

// @vue/component
export const EntitiesPanel = {
	name: 'EntitiesPanel',
	components: { EntityLink },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		compactMode: {
			type: Boolean,
			required: true,
		},
	},
	data(): Object
	{
		return {
			highlightMode: false,
		};
	},
	computed:
	{
		CollabEntityType: () => CollabEntityType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		collabInfo(): ImModelCollabInfo
		{
			return this.$store.getters['chats/collabs/getByChatId'](this.dialog.chatId);
		},
		tasksInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.tasks;
		},
		tasksUrl(): string
		{
			return this.tasksInfo.url;
		},
		tasksCounter(): number
		{
			return this.tasksInfo.counter;
		},
		filesInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.files;
		},
		filesUrl(): string
		{
			return this.filesInfo.url;
		},
		calendarInfo(): { url: string, counter: number }
		{
			return this.collabInfo.entities.calendar;
		},
		calendarUrl(): string
		{
			return this.calendarInfo.url;
		},
		calendarCounter(): number
		{
			return this.calendarInfo.counter;
		},
	},
	mounted()
	{
		this.initPromo();
	},
	methods:
	{
		initPromo(): void
		{
			const promoManager = PromoManager.getInstance();

			const promoId = PromoId.collabEntities;
			const promoParams = { chatId: this.dialog.chatId };

			if (promoManager.needToShow(promoId, promoParams))
			{
				void this.showCollabEntitiesPromo(promoId, promoParams);
			}
		},
		async showCollabEntitiesPromo(promoId: $Values<typeof PromoId>, promoParams: PromoParams): Promise<void>
		{
			const tooltip = new Tooltip({
				bindElement: this.$refs['collab-entities'],
				content: this.renderTooltipContent(),
				minWidth: 410,
				popupOptions: {
					offsetTop: 11,
					offsetLeft: 38,
					autoHide: true,
					closeIcon: true,
					closeIconSize: CloseIconSize.LARGE,
					events: {
						onPopupShow: () => {
							this.highlightMode = true;
						},
						onPopupClose: () => {
							this.highlightMode = false;
						},
					},
				},
			});

			tooltip.show();
			void PromoManager.getInstance().markAsWatched(promoId, promoParams);
		},
		renderTooltipContent(): HTMLElement
		{
			const tooltipContent = new CollabTooltipContent({
				title: this.loc('IM_CONTENT_COLLAB_ONBOARDING_ENTITIES_TITLE'),
				text: this.loc('IM_CONTENT_COLLAB_ONBOARDING_ENTITIES_TEXT'),
				iconKey: IconKey.collabEntities,
			});

			return tooltipContent.render();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-collab-header__links-container"
			:class="{'--compact': compactMode, '--highlight': highlightMode}"
			ref="collab-entities"
		>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="tasksUrl"
				:type="CollabEntityType.tasks"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_TASKS')"
				:counter="tasksCounter"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="filesUrl"
				:type="CollabEntityType.files"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_FILES')"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="calendarUrl"
				:type="CollabEntityType.calendar"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_CALENDAR')"
				:counter="calendarCounter"
			/>
		</div>
	`,
};
