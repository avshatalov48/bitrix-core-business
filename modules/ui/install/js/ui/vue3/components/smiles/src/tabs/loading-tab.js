// @vue/component
export const LoadingTab = {
	name: 'LoadingTab',
	template: `
		<svg class="bx-ui-smiles-loading-circular" viewBox="25 25 50 50">
			<circle
				class="bx-ui-smiles-loading-path" 
				cx="50" 
				cy="50" 
				r="20" 
				fill="none"
				stroke-miterlimit="10"
			/>
			<circle
				class="bx-ui-smiles-loading-inner-path"
				cx="50"
				cy="50"
				r="20" 
				fill="none" 
				stroke-miterlimit="10"
			/>
		</svg>
	`,
};
