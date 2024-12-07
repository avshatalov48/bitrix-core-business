import { CardBoxTrait } from './card-box-trait';
import { ReplaceLangPhraseTrait } from './replace-lang-phrase-tait';

export const CardBoxOnec = {
	mixins: [
		CardBoxTrait,
		ReplaceLangPhraseTrait,
	],
	template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			@click="onClick"
			class="inventory-management__card-item --1c"
			:class="cardItemClass"
			:style="cardItemStyle"
		>
			<div class="inventory-management__card-logo"></div>
			<div
				v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_TITLE_1C')"
				class="inventory-management__card-title"
			>
			</div>
			<ul class="inventory-management__card-list">
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_1')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_2')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_3')}}
				</li>
			</ul>
			<div class="inventory-management__card-action-box">
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --cubes-3"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_1')"
						class="inventory-management__card-action-text">
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_1_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --shop-list"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_2')"
						class="inventory-management__card-action-text"
					>
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_2_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --persons-3"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_3')"
						class="inventory-management__card-action-text"
					>
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_3_HINT')"
					></action-hint>
				</div>
			</div>
			<div class="inventory-management__card-select-box">
				<div
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_1C')"
					class="inventory-management__card-select-text"
				>
				</div>
				<div class="inventory-management__card-select-icon">
					<div class="ui-icon-set --check"></div>
				</div>
			</div>
			<div class="inventory-management__card-control-box">
				<button
					v-if="!isActive"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON')}}
				</button>
				<card-box-help></card-box-help>
			</div>
		</div>
	`,
};
