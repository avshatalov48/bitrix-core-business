/**
 * Bitrix Videoconf
 * Smiles component (Vue component)
 *
 * @package bitrix
 * @subpackage imopenlines
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from "ui.vue";
import 'ui.vue.components.smiles';

Vue.cloneComponent('bx-im-component-call-smiles', 'bx-smiles',
	{
		methods:
			{
				hideForm(event)
				{
					this.$parent.hideSmiles();
				},
			},
		template: `
		<div class="bx-im-component-smiles-box">
			<div class="bx-im-component-smiles-box-close" @click="hideForm"></div>
			<div class="bx-livechat-alert-smiles-box">
				#PARENT_TEMPLATE#
			</div>
		</div>
	`
	});