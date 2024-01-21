import { Popup, PopupManager } from 'main.popup';
import { Type } from 'main.core';

const POPUP_CONTAINER_PREFIX = '#popup-window-content-';
// @vue/component
export const BasePopup = {
	name: 'BasePopup',
	props: {
		id: {
			type: String,
			required: true,
		},
		config: {
			type: Object,
			required: false,
			default(): Object {
				return {};
			},
		},
	},
	emits: ['close'],
	computed: {
		popupContainer(): string
		{
			return `${POPUP_CONTAINER_PREFIX}${this.id}`;
		},
	},
	created()
	{
		this.instance = this.getPopupInstance();
		this.instance.show();
	},
	mounted()
	{
		this.instance.adjustPosition({
			forceBindPosition: true,
			position: this.getPopupConfig().bindOptions.position,
		});
	},
	beforeUnmount()
	{
		if (!this.instance)
		{
			return;
		}

		this.closePopup();
	},
	methods: {
		getPopupInstance(): Popup
		{
			if (!this.instance)
			{
				PopupManager.getPopupById(this.id)?.destroy();
				this.instance = new Popup(this.getPopupConfig());
			}

			return this.instance;
		},
		getDefaultConfig(): Object
		{
			return {
				id: this.id,
				className: 'ui-test-popup',
				autoHide: true,
				animation: 'fading-slide',
				bindOptions: {
					position: 'bottom',
				},
				cacheable: false,
				events: {
					onPopupClose: this.closePopup.bind(this),
				},
			};
		},
		getPopupConfig(): Object
		{
			const defaultConfig = this.getDefaultConfig();
			const modifiedOptions = {};

			const defaultClassName = defaultConfig.className;
			if (this.config.className)
			{
				modifiedOptions.className = `${defaultClassName} ${this.config.className}`;
			}

			const offsetTop = this.config.offsetTop ?? defaultConfig.offsetTop;
			// adjust for default popup margin for shadow
			if (this.config.bindOptions?.position === 'top' && Type.isNumber(this.config.offsetTop))
			{
				modifiedOptions.offsetTop = offsetTop - 10;
			}

			return { ...defaultConfig, ...this.config, ...modifiedOptions };
		},
		closePopup()
		{
			this.$emit('close');
			this.instance.destroy();
			this.instance = null;
		},
		enableAutoHide()
		{
			this.getPopupInstance().setAutoHide(true);
		},
		disableAutoHide()
		{
			this.getPopupInstance().setAutoHide(false);
		},
		adjustPosition()
		{
			this.getPopupInstance().adjustPosition({
				forceBindPosition: true,
				position: this.getPopupConfig().bindOptions.position,
			});
		},
	},
	template: `
		<Teleport :to="popupContainer">
			<slot
				:adjustPosition="adjustPosition"
				:enableAutoHide="enableAutoHide"
				:disableAutoHide="disableAutoHide"
			></slot>
		</Teleport>
	`,
};
