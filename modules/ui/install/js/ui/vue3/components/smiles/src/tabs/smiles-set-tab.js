import { lazyload } from 'ui.vue3.directives.lazyload';

// @vue/component
export const SmilesSetTab = {
	name: 'SmilesSetTab',
	directives: {
		lazyload,
	},
	props: {
		smiles: {
			type: Array,
			required: true,
		},
	},
	emits: ['selectSmile'],
	methods:
	{
		selectSmile(text)
		{
			this.$emit('selectSmile', { text });
		},
	},
	// language=Vue
	template: `
		<template v-for="smile in smiles">
			<div class="bx-ui-smiles-smile">
				<img v-lazyload :key="smile.id" 
					 class="bx-ui-smiles-smile-icon"
					 :data-lazyload-src="smile.image"
					 data-lazyload-error-class="bx-ui-smiles-smile-icon-error"
					 :title="smile.name"
					 :style="{
						height: (smile.originalHeight*0.5)+'px',
						width: (smile.originalWidth*0.5)+'px',
					}"
					 @click="selectSmile(smile.typing)"
				/>
			</div>
		</template>
	`,
};
