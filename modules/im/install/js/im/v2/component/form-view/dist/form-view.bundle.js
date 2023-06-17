this.BX = this.BX || {};
this.BX.Im = this.BX.Im || {};
this.BX.Im.V2 = this.BX.Im.V2 || {};
(function (exports,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const Checkbox = {
	  name: 'Checkbox',
	  emits: ['changeData'],
	  props: {
	    fieldValue: {
	      type: Boolean,
	      required: true
	    },
	    fieldMetadata: {
	      type: Object,
	      required: true
	    }
	  },
	  template: `
		<input
			type="checkbox"
			:checked=fieldValue
			:id="fieldMetadata.id"
			class="bx-im-form-view__field_checkbox"
			@change="(event) => {
				this.$emit('changeData', event.target.checked);
			}"
		/>
		<label
			class="bx-im-form-view__field_label"
			:for="fieldMetadata.id"
		>
			{{fieldMetadata.label}}
		</label>
	`
	};

	// @vue/component
	const Select = {
	  name: 'Select',
	  components: {
	    Dropdown: im_v2_component_elements.Dropdown
	  },
	  emits: ['changeData'],
	  props: {
	    fieldValue: {
	      type: String,
	      required: true
	    },
	    fieldMetadata: {
	      type: Object,
	      required: true
	    }
	  },
	  template: `
		<label class="bx-im-form-view__field_label">
			{{fieldMetadata.label}}
		</label>
		<Dropdown
			class="bx-im-form-view__field_select"
			:id="fieldMetadata.id"
			:items="fieldMetadata.options"
			@itemChange="(event) => {
				this.$emit('changeData', event);
			}"
		/>
	`
	};

	// @vue/component
	const FormView = {
	  name: 'FormView',
	  components: {
	    Checkbox,
	    Select
	  },
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
	  computed: {
	    formatters() {
	      return {
	        boolean: Checkbox,
	        select: Select
	      };
	    },
	    propsInSection() {
	      const sectionIds = Object.keys(this.sections);
	      const formMetadataIds = Object.keys(this.formMetadata);
	      return sectionIds.reduce((acc, sectionId) => {
	        const props = formMetadataIds.filter(fieldMetadata => {
	          return this.formMetadata[fieldMetadata].section === sectionId;
	        });
	        return {
	          ...acc,
	          [sectionId]: props
	        };
	      }, {});
	    }
	  },
	  methods: {
	    changeData(propName, newValue) {
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

	exports.FormView = FormView;

}((this.BX.Im.V2.Component = this.BX.Im.V2.Component || {}),BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=form-view.bundle.js.map
