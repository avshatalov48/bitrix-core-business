export const CheckboxComponent = {
	props: [
		'id',
		'title',
	],

	data(): Object
	{
		return {
			dataTitle: this.title,
			dataId: this.id,
			checked: false,
		};
	},

	methods: {
		handleClick(key): void
		{
			this.checked = !this.checked;

			this.$emit('onToggled', this.checked);
		},
	},

	template: `
		<div class="ui-checkbox-list__footer-custom-element --checkbox" @click="handleClick">
			<input type="checkbox" :name="dataId" v-model="checked">
			<label :for="dataId">{{ dataTitle }}</label>
		</div>
	`,
};
