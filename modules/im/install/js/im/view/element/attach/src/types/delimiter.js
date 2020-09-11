/**
 * Bitrix Messenger
 * Vue component
 *
 * Delimiter (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./delimiter.css";

export const AttachTypeDelimiter =
{
	property: 'DELIMITER',
	name: 'bx-im-view-element-attach-delimiter',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		computed:
		{
			styles()
			{
				return {
					width: this.config.DELIMITER.SIZE? this.config.DELIMITER.SIZE+'px': '',
					backgroundColor: this.config.DELIMITER.COLOR? this.config.DELIMITER.COLOR: this.color,
				}
			}
		},
		template: `<div class="bx-im-element-attach-type-delimiter" :style="styles"></div>`
	},
};