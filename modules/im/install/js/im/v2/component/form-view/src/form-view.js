import {Checkbox} from './components/checkbox';
import {Select} from './components/select';

import './css/form-view.css';

export type Formatters = {
	boolean: Checkbox;
	select: Select;
};

// @vue/component
export const FormView = {
	name: 'FormView',
	components: {Checkbox, Select},
	emits: ['changeData'],
	props: {
		sections: {
			type: Object,
			default: {}
		},
		formMetadata: {
			type: Object,
			required: true
		},
		formData: {
			type: Object,
			required: true
		},
		order: {
			type: Array,
			default: []
		}
	},
	computed:
	{
		formatters(): Formatters
		{
			return {
				boolean: Checkbox,
				select: Select
			};
		},
		propsInSection(): {[key: string]: Array<string>}
		{
			const sectionIds = Object.keys(this.sections);
			const formMetadataIds = Object.keys(this.formMetadata);

			return sectionIds.reduce((acc, sectionId) => {
				const props = formMetadataIds.filter((fieldMetadata) => {
					return this.formMetadata[fieldMetadata].section === sectionId;
				});

				return {...acc, [sectionId]: props};
			}, {});
		}
	},
	methods:
	{
		changeData(propName, newValue)
		{
			const oldValue = this.formData[propName];
			const detail = {
				propName,
				oldValue,
				newValue
			};
			this.$emit('changeData', detail);
		}
	},
	template: `
		<div class="bx-im-form-view__scope">
			<template v-for="orderItem in order">
				<template v-if="sections[orderItem]">
					<div class="bx-im-form-view__section" :key="orderItem">
						<p class="bx-im-form-view__section_title">
							{{sections[orderItem]}}
						</p>
						<div
							v-for="propName in propsInSection[orderItem]"
							class="bx-im-form-view__field"
							:key="orderItem + '-' + propName"
						>
							<Component
								:is="formatters[formMetadata[propName].type]"
								:fieldMetadata="formMetadata[propName]"
								:fieldValue="formData[propName]"
								@changeData="(event) => changeData(propName, event)"
							/>
						</div>
					</div>
				</template>
				<div v-else class="bx-im-form-view__field" :key="orderItem">
					<Component
						:is="formatters[formMetadata[orderItem].type]"
						:fieldMetadata="formMetadata[orderItem]"
						:fieldValue="formData[orderItem]"
						@changeData="(event) => changeData(orderItem, event)"
					/>
				</div>
			</template>
		</div>
	`
};