import {Reflection} from 'main.core';
import 'ui.fonts.opensans';
import 'ui.design-tokens';
import './css/core_avatar_editor.css';
import './css/logic.css';
import EditorInPopup from './editor-in-popup';
import EditorInSlider from './editor-in-slider';
import CameraTab from "./tabs/camera-tab";
import MaskEditor from "./mask-tool/mask-editor";
import type {FileType} from './editor';

export type {
	FileType
};

let currentEditor = EditorInPopup;
/**
 * @namespace BX.UI.AvatarEditor
 */
function createInstance()
{
	return currentEditor.createInstance(...arguments);
}

function isCameraAvailable()
{
	return currentEditor.isCameraAvailable();
}

function getInstanceById()
{
	return currentEditor.getInstanceById(...arguments);
}

function getOrCreateInstanceById()
{
	return currentEditor.getOrCreateInstanceById(...arguments);
}
export {
	currentEditor as Editor,
	MaskEditor,
	createInstance,
	getInstanceById,
	getOrCreateInstanceById,
	isCameraAvailable
};

CameraTab.check();
const BX = Reflection.namespace('BX');
BX.AvatarEditor = currentEditor;
