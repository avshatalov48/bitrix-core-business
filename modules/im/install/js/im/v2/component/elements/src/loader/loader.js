import {Loader as UILoader} from 'ui.loader';
import './loader.css';

const LOADER_SIZE = 'xs';
const LOADER_TYPE = 'BULLET';

// @vue/component
export const Loader = {
	name: 'MessengerLoader',
	mounted()
	{
		this.loader = new UILoader({
			target: this.$refs['messenger-loader'],
			type: LOADER_TYPE,
			size: LOADER_SIZE,
		});
		this.loader.render();
		this.loader.show();
	},
	beforeUnmount()
	{
		this.loader.hide();
		this.loader = null;
	},
	template: `
		<div class="bx-im-elements-loader__container" ref="messenger-loader"></div>
	`
};