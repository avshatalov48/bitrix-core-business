import { Text } from 'main.core';
import { Popup as MainPopup, PopupManager, type PopupOptions } from 'main.popup';
import { SidePanelIntegration } from './side-panel-integration';

// @vue/component
export const Popup = {
	props: {
		id: {
			type: String,
			default: () => `ui-vue3-popup-${Text.getRandom()}`,
		},
		options: {
			/** @type PopupOptions */
			type: Object,
			default: null,
		},
	},
	emits: ['close'],
	computed: {
		popupContainer(): string
		{
			return `#${this.popupOptions.id}`;
		},
		container(): HTMLElement
		{
			return this.getPopupInstance().getPopupContainer();
		},
		popupOptions(): PopupOptions
		{
			return { ...this.defaultOptions, ...this.options };
		},
		defaultOptions(): PopupOptions
		{
			return {
				id: this.id,
				cacheable: false,
				autoHide: true,
				autoHideHandler: this.autoHideHandler,
				closeByEsc: true,
				animation: 'fading',
				bindOptions: {
					forceBindPosition: true,
				},
				events: {
					onPopupClose: this.closePopup,
					onPopupDestroy: this.closePopup,
				},
			};
		},
	},
	created(): void
	{
		new SidePanelIntegration(this);
	},
	beforeMount(): void
	{
		this.getPopupInstance().show();
	},
	mounted(): void
	{
		this.adjustPosition();
		this.getPopupInstance().getContentContainer().remove();
	},
	beforeUnmount(): void
	{
		this.popupHTML = this.instance.getPopupContainer()?.innerHTML;
	},
	unmounted(): void
	{
		if (this.popupHTML)
		{
			this.instance.getPopupContainer().innerHTML = this.popupHTML;
		}

		this.instance?.close();
	},
	methods: {
		contains(element: HTMLElement): boolean
		{
			return this.container.contains(element) ?? false;
		},
		adjustPosition(): void
		{
			this.getPopupInstance().adjustPosition(this.popupOptions.bindOptions);
		},
		freeze(): void
		{
			this.getPopupInstance().setAutoHide(false);
			this.getPopupInstance().setClosingByEsc(false);
		},
		unfreeze(): void
		{
			this.getPopupInstance().setAutoHide(this.popupOptions.autoHide);
			this.getPopupInstance().setClosingByEsc(this.popupOptions.closeByEsc);
		},
		getPopupInstance(): MainPopup
		{
			if (!this.instance)
			{
				PopupManager.getPopupById(this.popupOptions.id)?.destroy();

				this.instance = new MainPopup(this.popupOptions);
			}

			return this.instance;
		},
		autoHideHandler({ target }): boolean
		{
			const parentAutoHide = target !== this.container && !this.container.contains(target);
			const isAhaMoment = target.closest('.popup-window-ui-tour');

			return parentAutoHide && !isAhaMoment;
		},
		closePopup(): void
		{
			this.$emit('close');
		},
	},
	template: `
		<Teleport :to="popupContainer">
			<slot :adjustPosition="adjustPosition" :freeze="freeze" :unfreeze="unfreeze"></slot>
		</Teleport>
	`,
};
