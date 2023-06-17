this.BX = this.BX || {};
this.BX.Im = this.BX.Im || {};
this.BX.Im.V2 = this.BX.Im.V2 || {};
(function (exports) {
	'use strict';

	// @vue/component
	const Checkbox = {
	  name: 'Checkbox',
	  emits: ['changeData'],
	  props: {
	    cellValue: {
	      type: Boolean,
	      required: true
	    }
	  },
	  template: `
		<input
			type="checkbox"
			:checked="cellValue"
			class="bx-im-grid-view__cell_checkbox"
			@change="(event) => {
				this.$emit('changeData', event.target.checked);
			}"
		/>
	`
	};

	// @vue/component
	const GridView = {
	  name: 'GridView',
	  components: {
	    Checkbox
	  },
	  emits: ['changeData'],
	  props: {
	    head: {
	      type: Map,
	      required: true
	    },
	    rows: {
	      type: Map,
	      required: true
	    }
	  },
	  computed: {
	    formatters() {
	      return {
	        boolean: Checkbox
	      };
	    }
	  },
	  methods: {
	    changeData(headId, rowId, newValue) {
	      const oldValue = this.rows.get(rowId)[headId];
	      const detail = {
	        headId,
	        rowId,
	        oldValue,
	        newValue
	      };
	      this.$emit('changeData', detail);
	    }
	  },
	  template: `
		<div class="bx-im-grid-view__scope">
			<div class="bx-im-grid-view__head" role="row">
				<span
					class="bx-im-grid-view__cell bx-im-grid-view__head_cell"
					role="columnheader"
					v-for="[columnId, column] in head"
					:key="columnId"
				>
					{{column.label}}
				</span>
			</div>
			<div role="rowgroup">
				<div
					class="bx-im-grid-view__row"
					role="row"
					v-for="[rowId, row] in rows"
					:key="rowId"
				>
					<span
						class="bx-im-grid-view__cell bx-im-grid-view__row_cell"
						role="gridcell"
						v-for="[columnId, column] in head"
						:key="rowId + '-' + columnId"
						:style="{textAlign: column.align ?? 'center'}"
					>
						<Component
							v-if="column.type"
							:is="formatters[column.type]"
							:cellValue="row[columnId]"
							:headId="columnId"
							:rowId="rowId"
							@changeData="(event) => changeData(columnId, rowId, event)"
						/>
						<template v-else>{{row[columnId]}}</template>
					</span>
				</div>
			</div>
		</div>
	`
	};

	exports.GridView = GridView;

}((this.BX.Im.V2.Component = this.BX.Im.V2.Component || {})));
