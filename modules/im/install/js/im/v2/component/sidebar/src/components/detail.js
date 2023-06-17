import {Loader} from 'im.v2.component.elements';
import '../css/detail.css';

// @vue/component
export const SidebarDetail = {
	name: 'SidebarDetail',
	components: {Loader},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
		service: {
			type: Object,
			required: true
		}
	},
	emits: ['onScroll'],
	data() {
		return {
			isLoading: false
		};
	},
	computed:
	{
		hasInitialData(): boolean
		{
			return this.$store.getters['sidebar/isInited'](this.chatId);
		},
	},
	created()
	{
		this.loadFirstPage();
	},
	methods:
	{
		loadFirstPage()
		{
			if (this.hasInitialData)
			{
				return;
			}

			this.isLoading = true;
			this.service.loadFirstPage().then(() => {
				this.isLoading = false;
			});
		},
		needToLoadNextPage(event)
		{
			return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
		},
		onScroll(event)
		{
			this.$emit('onScroll');

			if (this.isLoading)
			{
				return;
			}

			if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoading = true;
			this.service.loadNextPage().then(() => {
				this.isLoading = false;
			});
		},
	},
	template: `
		<div class="bx-im-sidebar-detail__container bx-im-sidebar-detail__scope" @scroll="onScroll">
			<slot :isLoading="isLoading" :chatId="chatId" :dialogId="dialogId"></slot>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
};