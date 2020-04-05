/*

//ES6
import { Popup, PopupManager } from 'main.popup';
const popup = new Popup();
PopupManager.create();

//ES5
var popup = new BX.Main.Popup();
BX.Main.PopupManager.create();

//ES6
import { Menu, MenuItem, MenuManager } from 'main.popup';
const menu = new Menu();
const item = new MenuItem();
MenuManager.create();

//ES5
var menu = new BX.Main.Menu();
var item = new BX.Main.MenuItem();
BX.Main.MenuManager.create();

 */

import Popup from './popup/popup';
import PopupManager from './popup/popup-manager';
import Menu from './menu/menu';
import MenuManager from './menu/menu-manager';
import MenuItem from './menu/menu-item';
import { type PopupOptions } from './popup/popup-types';
import { type MenuOptions, type MenuItemOptions } from './menu/menu-types';

import PopupWindow from './compatibility/popup-window';
import PopupWindowButton from './compatibility/popup-window-button';
import PopupWindowButtonLink from './compatibility/popup-window-button-link';
import PopupWindowCustomButton from './compatibility/popup-window-custom-button';
import PopupMenuWindow from './compatibility/popup-menu-window';
import PopupMenuItem from './compatibility/popup-menu-item';
import InputPopup from './compatibility/input-popup';
import Button from './compatibility/button';
import ButtonLink from './compatibility/buttonlink';
import CustomButton from './compatibility/custombutton';

import { Reflection } from 'main.core';

import 'ui.fonts.opensans';
import './css/popup.css';

export {
	Popup,
	Menu,
	MenuItem,
	PopupManager,
	MenuManager
};

export type {
	PopupOptions,
	MenuOptions,
	MenuItemOptions
};

//Compatibility
export {
	PopupWindow,
	PopupMenuWindow,
	PopupMenuItem,
	PopupManager as PopupWindowManager,
	MenuManager as PopupMenu,
	PopupWindowButton,
	PopupWindowButtonLink,
	PopupWindowCustomButton
};

const BX = Reflection.namespace('BX');

/** @deprecated use BX.Main.Popup or import { Popup } from 'main.popup' */
BX.PopupWindow = Popup;

/** @deprecated use BX.Main.PopupManager or import { PopupManager } from 'main.popup' */
BX.PopupWindowManager = PopupManager;

/** @deprecated use BX.Main.Menu or import { Menu } from 'main.popup' */
BX.PopupMenuWindow = Menu;

/** @deprecated use BX.Main.MenuManager or import { MenuManager } from 'main.popup' */
BX.PopupMenu = MenuManager;

/** @deprecated use BX.Main.MenuItem or import { MenuItem } from 'main.popup' */
BX.PopupMenuItem = MenuItem;

/** @deprecated use BX.UI.Button */
BX.PopupWindowButton = Button;

/** @deprecated use BX.UI.Button */
BX.PopupWindowButtonLink = ButtonLink;

/** @deprecated use BX.UI.Button */
BX.PopupWindowCustomButton = CustomButton;

/** @deprecated use another API */
window.BXInputPopup = InputPopup;