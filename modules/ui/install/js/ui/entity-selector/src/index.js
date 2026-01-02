import Dialog from './dialog/dialog';
import EntityError from './entity/entity-error';
import Item from './item/item';
import ItemNode from './item/item-node';
import Tab from './dialog/tabs/tab';
import Entity from './entity/entity';
import TagSelector from './tag-selector/tag-selector';
import TagItem from './tag-selector/tag-item';
import BaseHeader from './dialog/header/base-header';
import DefaultHeader from './dialog/header/default-header';
import BaseFooter from './dialog/footer/base-footer';
import DefaultFooter from './dialog/footer/default-footer';
import BaseStub from './dialog/tabs/base-stub';
import DefaultStub from './dialog/tabs/default-stub';

import type { DialogOptions } from './dialog/dialog-options';
import type { TabOptions } from './dialog/tabs/tab-options';
import type { ItemOptions, ItemSelectOptions } from './item/item-options';
import type { EntityOptions } from './entity/entity-options';
import type { EntityFilterOptions } from './entity/entity-filter-options';
import type { EntityErrorOptions } from './entity/entity-error-options';
import type { EntityBadgeOptions } from './entity/entity-badge-options';
import type { TagSelectorOptions } from './tag-selector/tag-selector-options';
import type { ItemId } from './item/item-id';
import type { ItemBadgeOptions } from './item/item-badge-options';
import type { ItemNodeOptions } from './item/item-node-options';
import type { BadgesOptions } from './item/badges-options';
import type { AvatarOptions } from './item/avatar-options';
import type { CaptionOptions } from './item/caption-options';
import type { SearchOptions } from './dialog/search-options';

import './css/dialog.css';
import './css/tab.css';
import './css/item.css';
import './css/tag-selector.css';

const EntitySelector = {
	Dialog,
	Item,
	Tab,
	Entity,
	TagSelector,
	TagItem,
	BaseHeader,
	DefaultHeader,
	BaseFooter,
	DefaultFooter,
	BaseStub,
	DefaultStub,
	EntityError,
};

/**
 * @namespace BX.UI.EntitySelector
 */
export {
	EntitySelector,
	Dialog,
	Item,
	Tab,
	Entity,
	TagSelector,
	TagItem,
	BaseHeader,
	DefaultHeader,
	BaseFooter,
	DefaultFooter,
	BaseStub,
	DefaultStub,
	EntityError,
};

export type {
	DialogOptions,
	TabOptions,
	ItemOptions,
	ItemSelectOptions,
	ItemBadgeOptions,
	ItemNodeOptions,
	EntityOptions,
	EntityFilterOptions,
	EntityErrorOptions,
	EntityBadgeOptions,
	TagSelectorOptions,
	ItemId,
	ItemNode,
	BadgesOptions,
	AvatarOptions,
	CaptionOptions,
	SearchOptions,
};
