import {Utils} from 'im.v2.lib.utils';

import {MessengerPopup} from '../popup/popup';
import './css/menu.css';

export {MenuItem, MenuItemIcon} from './menu-item';

const ID_PREFIX = 'im-v2-menu';

// @vue/component
export const MessengerMenu = {
	name: 'MessengerMenu',
	components: {MessengerPopup},
	props:
	{
		config: {
			type: Object,
			required: true
		},
		className: {
			type: String,
			required: false,
			default: ''
		}
	},
	emits: ['close'],
	data()
	{
		return {
			id: ''
		};
	},
	created()
	{
		this.id = this.config.id ?? `${ID_PREFIX}-${Utils.text.getUuidV4()}`;
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="id"
		>
			<div class="bx-im-menu__container" :class="className">
				<slot name="header"></slot>
				<slot></slot>
				<slot name="footer"></slot>
			</div>
		</MessengerPopup>
	`
};