import { Type } from 'main.core';

export const TextToggleComponent = {
	props: [
		'id',
		'title',
		'dataItems',
	],

	data(): Object
	{
		return {
			dataTitle: this.title,
			dataId: this.id,
			value: null,
		};
	},

	methods: {
		handleClick(key): void
		{
			let index = this.dataItems.findIndex((item) => item.value === this.value);
			if (index >= this.dataItems.length - 1)
			{
				index = 0;
			}
			else
			{
				index++;
			}

			this.value = this.dataItems[index].value;

			this.$emit('onToggled', this.value);
		},
	},

	computed: {
		currentLabel(): string
		{
			if (this.value === null && Type.isArrayFilled(this.dataItems))
			{
				this.value = this.dataItems[0].value;

				return this.dataItems[0].label;
			}

			return this.dataItems.find((item) => item.value === this.value)?.label;
		},
	},

	template: `
		<div class="ui-checkbox-list__footer-custom-element --texttoggle" @click="handleClick">
			<span class="ui-checkbox-list__texttoggle__title">{{ dataTitle }}</span>
			<span class="ui-checkbox-list__texttoggle__value">{{ currentLabel }}</span>
			<input type="hidden" :name="dataId" v-model="value">
		</div>
	`,
};
