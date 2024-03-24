import { Tag, Loc } from 'main.core';
import { Dialog, type Item } from 'ui.entity-selector';
import 'ui.buttons';

import { Core } from 'im.v2.application.core';

export const openCallUserSelector = (params) => {
	const handleAddCLick = () => {
		const selectedItems = dialog.getSelectedItems();

		const preparedItems = prepareUser(selectedItems);

		params.onSelect({ users: preparedItems });
	};

	const handleCancelCLick = () => {
		dialog.hide();
	};

	const dialog = new Dialog({
		targetNode: params.bindElement,
		width: 400,
		enableSearch: true,
		dropdownMode: true,
		context: 'IM_CHAT_SEARCH',
		entities: [{
			id: 'user',
			dynamicLoad: true,
			itemOptions: {
				default: {
					linkTitle: '',
					link: '',
				},
			},
			options: {
				inviteEmployeeLink: false,
				'!userId': Core.getUserId(),
			},
			filters: [
				{
					id: 'im.userDataFilter',
				},
			],
		}],
		footer: getFooter(handleAddCLick, handleCancelCLick),
	});
	dialog.show();

	return Promise.resolve({
		close: () => {
			dialog.hide();
		},
	});
};

const prepareUser = (users) => {
	return users.map((user: Item) => {
		return {
			id: user.id,
			name: user.title.text,
			avatar: user.avatar,
			avatar_hr: user.avatar,
			gender: user.customData.get('imUser').GENDER,
		};
	});
};

const getFooter = (handleAddCLick, handleCancelCLick) => {
	const addButtonTitle = Loc.getMessage('IM_LIB_CALL_ADD_BUTTON');
	const cancelButtonTitle = Loc.getMessage('IM_LIB_CALL_CANCEL_BUTTON');

	return Tag.render`
		<button class="ui-btn ui-btn-xs ui-btn-primary" onclick="${handleAddCLick}">${addButtonTitle}</button>
		<button class="ui-btn ui-btn-xs ui-btn-light-border" onclick="${handleCancelCLick}">${cancelButtonTitle}</button>
	`;
};
