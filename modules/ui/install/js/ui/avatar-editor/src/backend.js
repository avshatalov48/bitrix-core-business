import {ajax, Loc} from 'main.core';

export default class Backend
{
	static #ajaxRepo = {};

	static saveMask({id, title, accessCode}, file)
	{
		if (Loc.getMessage('USER_ID') <= 0)
		{
			return;
		}

		const formObj = new FormData();
		formObj.append('id', id);
		formObj.append('title', title);
		if (accessCode.length > 0)
		{
			Array
				.from(accessCode)
				.forEach((accessCode, index) => {
					formObj.append('accessCode[' + index + '][0]', accessCode[0]);
					formObj.append('accessCode[' + index + '][1]', accessCode[1]);
				});
		}
		else
		{
			formObj.append('accessCode[]', '');
		}
		if (file instanceof Blob)
		{
			formObj.append('file[changed]', 'Y');
			formObj.append('file', file, file['name']);
		}
		else
		{
			formObj.append('file[changed]', 'N');
		}

		return ajax
			.runAction(
				'ui.avatar.mask.save',
				{
					data: formObj,
					analyticsLabel: {
						ui: 'avatarMask',
						actionType: 'edit',
						action: 'save',
					}
				}
			)
		;
	}

	static getMaskList(actionName: String, {page, size}): Promise
	{
		return new Promise((resolve, reject) => {
			ajax
				.runAction('ui.avatar.mask.get' + actionName, {
					data: {},
					navigation: {
						page: page,
						size: size
					},
					analyticsLabel: {
						ui: 'avatarMask',
						actionType: 'read',
						action: 'list',
					}
				})
				.then(({data: {groupedItems}}) => {
					resolve(groupedItems);
				})
				.catch(reject)
			;
		});
	}

	static getMaskInitialInfo({size, recentlyUsedListSize})
	{
		return new Promise((resolve, reject) => {
			if (this.#ajaxRepo['getMaskInitialInfo'])
			{
				return resolve(this.#ajaxRepo['getMaskInitialInfo']);
			}
			ajax
				.runAction('ui.avatar.mask.getMaskInitialInfo', {
					data: {
						recentlyUsedListSize: recentlyUsedListSize
					},
					navigation: {
						page: 1,
						size: size
					},
					analyticsLabel: {
						ui: 'avatarMask',
						actionType: 'read',
						action: 'initialInfo',
					}
				})
				.then(({data: {initialInfo}}) => {
					this.#ajaxRepo['getMaskInitialInfo'] = initialInfo;
					resolve(initialInfo)
				})
				.catch(reject)
			;
		})
	}

	static getMaskAccessCode(itemId)
	{
		return ajax
			.runAction('ui.avatar.mask.getMaskAccessCode', {
				data: {
					id: itemId
				},
				analyticsLabel: {
					ui: 'avatarMask',
					actionType: 'edit',
					action: 'accessCode',
				}
			})
		;
	}

	static deleteMask(itemId)
	{
		return ajax
			.runAction('ui.avatar.mask.delete', {
				data: {
					id: itemId
				},
				analyticsLabel: {
					ui: 'avatarMask',
					actionType: 'edit',
					action: 'delete',
				}
			})
		;
	}

	static useRecently(itemId)
	{
		return ajax
			.runAction('ui.avatar.mask.useRecently', {
				data: {
					id: itemId
				},
				analyticsLabel: {
					ui: 'avatarMask',
					actionType: 'read',
					action: 'read',
				}
			})
		;
	}

	static cleanUp()
	{
		return ajax
			.runAction('ui.avatar.mask.cleanUp', {
				analyticsLabel: {
					ui: 'avatarMask',
					actionType: 'edit',
					action: 'cleanUp',
				}
			})
			;
	}
}