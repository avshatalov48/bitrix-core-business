import { WarehouseSection } from './section';
import { mapGetters } from 'ui.vue3.vuex';

export const Content = {
	components: {
		WarehouseSection,
	},
	computed: {
		sectionTitlePrefix(): String
		{
			return 'CAT_WAREHOUSE_MASTER_NEW_SECTION_TITLE_';
		},
		sectionDescriptionPrefix(): String
		{
			return 'CAT_WAREHOUSE_MASTER_NEW_SECTION_DESCRIPTION_';
		},
		getMobileBoxClass(): Object
		{
			const result = {
				'catalog-warehouse__master-clear__mobile-box': true,
			};

			if (this.getPreviewLang !== 'ru')
			{
				result['--eng'] = true;
			}

			return result;
		},
		...mapGetters([
			'getPreviewLang',
		]),
	},

	// language = Vue
	template: `
		<div class="catalog-warehouse__master-clear--content">
			<div class="catalog-warehouse__master-clear_inner">
				<div class="catalog-warehouse-master-clear-title">
					<div class="catalog-warehouse-master-clear-title-text--new">
						{{ $Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_TITLE') }}
					</div>
				</div>
				<div class="catalog-warehouse__master-clear__box">
					<div :class="getMobileBoxClass"></div>
					<div class="catalog-warehouse__master-clear__section_box">
						<WarehouseSection
							:title="$Bitrix.Loc.getMessage(sectionTitlePrefix + 'DOCUMENTS')"
							:description="$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'DOCUMENTS')"
							:iconType="'documents'"
						/>

						<WarehouseSection
							:title="$Bitrix.Loc.getMessage(sectionTitlePrefix + 'CRM')"
							:description="$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'CRM')"
							:iconType="'crm'"
						/>

						<WarehouseSection
							:title="$Bitrix.Loc.getMessage(sectionTitlePrefix + 'MOBILE')"
							:description="$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'MOBILE')"
							:iconType="'mobile'"
						/>
					</div>
				</div>
			</div>
		</div>
	`,
};
