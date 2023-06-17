import {Extension, Type} from 'main.core';

const isDesktop = Type.isObject(window.BXDesktopSystem);
const settings = Extension.getSettings('im.v2.lib.parser');
const v2 = settings.get('v2') && !isDesktop;

const getCore = () => {
	return v2 ? BX.Messenger.v2.Application.Core : BX.Messenger.Embedding.Application.Core;
};
const getUtils = () => {
	return v2 ? BX.Messenger.v2.Lib.Utils : BX.Messenger.Embedding.Lib.Utils;
};
const getLogger = () => {
	return v2 ? BX.Messenger.v2.Lib.Logger : BX.Messenger.Embedding.Lib.Logger;
};
const getConst = () => {
	return v2 ? BX.Messenger.v2.Const : BX.Messenger.Embedding.Const;
};
const getSmileManager = () => {
	return v2 ? BX.Messenger.v2.Lib.SmileManager : BX.Messenger.Embedding.Lib.SmileManager;
};
const getBigSmileOption = () => {
	if (v2)
	{
		const settingName = BX.Messenger.v2.Const.Settings.dialog.bigSmiles;
		return getCore().getStore().getters['application/settings/get'](settingName);
	}

	return getCore().getStore().getters['application/getOption']('bigSmileEnable');
};

export {getCore, getUtils, getLogger, getConst, getSmileManager, getBigSmileOption};

export type Smile = {
	id: string;
	setId: string;
	name: string;
	image: string;
	typing: string;
	alternative: boolean;
	width: number;
	height: number;
	definition: string;
};