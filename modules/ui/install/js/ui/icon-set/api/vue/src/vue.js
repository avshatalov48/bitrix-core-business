import {Type} from 'main.core';
import {Icon, Set} from 'ui.icon-set.api.core';

const BIcon  = {
	props: {
		name: {
			type: String,
			required: true,
			validator(value){
				return Object.values(Set).includes(value)
			},
		},
		color: {
			type: String,
		},
		size: {
			type: Number,
		},
	},

	computed: {
		className(){
			return [
				'ui-icon-set',
				`--${this.name}`,
			]
		},
		inlineSize() {
			return this.size ? '--ui-icon-set__icon-size: ' + this.size + 'px;' : ''
		},
		inlineColor() {
			return this.color ? '--ui-icon-set__icon-color: ' + this.color + ';' : ''
		},

		inlineStyle() {
			return this.inlineSize + this.inlineColor;
		}
	},

	template: `<div
				:class="className"
				:style="inlineStyle"
				>
	</div>`,
}

export {
	BIcon,
	Set,
}