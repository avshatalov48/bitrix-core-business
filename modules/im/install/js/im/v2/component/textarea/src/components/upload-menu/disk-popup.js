import {ajax} from 'main.core';

const FILE_DIALOG_ID = 'im-file-dialog';

/* eslint-disable bitrix-rules/no-bx */
// @vue/component
export const DiskPopup = {
	name: 'DiskPopup',
	emits: ['close', 'diskFileSelect'],
	created()
	{
		if (!BX.DiskFileDialog)
		{
			console.error('Couldn\'t initialize disk popup');

			return;
		}

		this.subscribeEvents();
		this.open();
	},
	beforeUnmount()
	{
		this.unsubscribeEvents();
	},
	methods:
	{
		subscribeEvents()
		{
			BX.addCustomEvent(BX.DiskFileDialog, 'inited', this.onInited);
			BX.addCustomEvent(BX.DiskFileDialog, 'loadItems', this.onLoadItems);
		},
		unsubscribeEvents()
		{
			BX.removeCustomEvent(BX.DiskFileDialog, 'inited', this.onInited);
			BX.removeCustomEvent(BX.DiskFileDialog, 'loadItems', this.onLoadItems);
		},
		onInited(name: string)
		{
			if (name !== FILE_DIALOG_ID)
			{
				return;
			}

			BX.DiskFileDialog.obCallback[name] = {
				'saveButton': (tab, path, selected) => {
					this.$emit('diskFileSelect', {files: selected});
				},
				'popupDestroy': () => {
					this.unsubscribeEvents();
					this.$emit('close');
				}
			};

			BX.DiskFileDialog.openDialog(name);
		},
		onLoadItems(link: string, name: string)
		{
			if (name !== FILE_DIALOG_ID)
			{
				return;
			}

			BX.DiskFileDialog.target[name] = link.replace(
				'/bitrix/tools/disk/uf.php',
				'/bitrix/components/bitrix/im.messenger/file.ajax.php'
			);
		},
		open()
		{
			ajax({
				url: `/bitrix/components/bitrix/im.messenger/file.ajax.php?action=selectFile&dialogName=${FILE_DIALOG_ID}`,
				method: 'GET',
				skipAuthCheck: true,
				timeout: 30,
			});
		},
	},
	template: `<template></template>`
};