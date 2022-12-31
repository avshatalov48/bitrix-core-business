import '../../css/stubs/empty-content.css';

export const EmptyContent = {
	template: `
		<div class="ui-entity-catalog__content --help-block">
			<div class="ui-entity-catalog__empty-content">
				<div class="ui-entity-catalog__empty-content_icon">
					<img src="/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--search-icon.svg" alt="Choose a grouping">
				</div>
				<div class="ui-entity-catalog__empty-content_text">
					<slot/>
				</div>
			</div>
		</div>
		`
}