import {ImModelDialog} from 'im.v2.model';
import {DialogType, SidebarBlock, SidebarDetailBlock} from 'im.v2.const';
import {Parser} from 'im.v2.lib.parser';
import {hint} from 'ui.vue3.directives.hint';
import '../../css/info/preview.css';

const MAX_DESCRIPTION_SYMBOLS = 25;

// @vue/component
export const InfoPreview = {
	name: 'InfoPreview',
	directives: {hint},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['openDetail'],
	data() {
		return {
			expanded: false,
		};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		previewDescription(): string
		{
			if (this.dialog.description.length === 0)
			{
				return this.chatTypeText;
			}

			if (this.dialog.description.length > MAX_DESCRIPTION_SYMBOLS)
			{
				return `${this.dialog.description.slice(0, MAX_DESCRIPTION_SYMBOLS)}...`;
			}

			return this.dialog.description;
		},
		descriptionToShow(): string
		{
			const rawText = this.expanded ? this.dialog.description : this.previewDescription;

			return Parser.purifyText(rawText);
		},
		chatTypeText(): string
		{
			if (this.isUser)
			{
				return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_USER');
			}

			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
		},
		showExpandButton(): boolean
		{
			if (this.expanded)
			{
				return false;
			}

			return this.dialog.description.length >= MAX_DESCRIPTION_SYMBOLS;
		},
		favoriteCounter(): string
		{
			const counter = this.$store.getters['sidebar/favorites/getCounter'](this.chatId);

			return this.getCounterString(counter);
		},
		urlCounter(): string
		{
			const counter = this.$store.getters['sidebar/links/getCounter'](this.chatId);

			return this.getCounterString(counter);
		},
		isLinksAvailable(): boolean
		{
			return this.$store.state.sidebar.isLinksMigrated;
		},
		hintDirectiveContent(): Object
		{
			return {
				text: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_NOT_AVAILABLE'),
				popupOptions: {
					angle: true,
					targetContainer: document.body,
					offsetLeft: 141,
					offsetTop: -10,
					bindOptions: {
						position: 'top'
					}
				}
			};
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited()
		{
			return this.dialog.inited;
		},
		isLoadingState(): boolean
		{
			return !this.dialogInited || this.isLoading;
		}
	},
	methods:
	{
		getCounterString(counter: number): string
		{
			const MAX_COUNTER = 100;
			if (counter >= MAX_COUNTER)
			{
				return '99+';
			}

			return counter.toString();
		},
		onFavouriteClick()
		{
			this.$emit('openDetail', {block: SidebarBlock.info, detailBlock: SidebarDetailBlock.favorite});
		},
		onLinkClick()
		{
			if (!this.isLinksAvailable)
			{
				return;
			}

			this.$emit('openDetail', {block: SidebarBlock.info, detailBlock: SidebarDetailBlock.link});
		}
	},
	template: `
		<div class="bx-im-sidebar-info-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-info-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-info-preview__container" :class="[expanded ? '--expanded' : '']">
				<div class="bx-im-sidebar-info-preview__description-container">
					<div class="bx-im-sidebar-info-preview__description-text-container" :class="[expanded ? '--expanded' : '']">
						<div class="bx-im-sidebar-info-preview__description-icon bx-im-sidebar-info-preview__item-icon"></div>
						<div class="bx-im-sidebar-info-preview__description-text">
							{{descriptionToShow}}
						</div>
					</div>
					<button
						v-if="showExpandButton"
						class="bx-im-sidebar-info-preview__show-description-button"
						@click="expanded = !expanded"
					>
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_DESCRIPTION_SHOW') }}
					</button>
				</div>
				<div class="bx-im-sidebar-info-preview__items-container">
					<div class="bx-im-sidebar-info-preview__item-container" @click="onFavouriteClick">
						<div class="bx-im-sidebar-info-preview__title-container">
							<div class="bx-im-sidebar-info-preview__favorite-icon bx-im-sidebar-info-preview__item-icon"></div>
							<div class="bx-im-sidebar-info-preview__title-text">
								{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITE_DETAIL_TITLE') }}
							</div>
						</div>
						<div class="bx-im-sidebar-info-preview__counter-container">
							<span class="bx-im-sidebar-info-preview__counter">{{favoriteCounter}}</span>
						</div>
					</div>
					<div 
						class="bx-im-sidebar-info-preview__item-container" 
						:class="[isLinksAvailable ? '' : '--links-not-active']"
						@click="onLinkClick"
					>
						<div 
							v-if="!isLinksAvailable" 
							class="bx-im-sidebar-info-preview__hint-not-active" 
							v-hint="hintDirectiveContent"
						></div>
						<div class="bx-im-sidebar-info-preview__title-container">
							<div class="bx-im-sidebar-info-preview__link-icon bx-im-sidebar-info-preview__item-icon"></div>
							<div class="bx-im-sidebar-info-preview__title-text">
								{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_LINK_DETAIL_TITLE') }}
							</div>
						</div>
						<div class="bx-im-sidebar-info-preview__counter-container">
							<span class="bx-im-sidebar-info-preview__counter">{{urlCounter}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
};