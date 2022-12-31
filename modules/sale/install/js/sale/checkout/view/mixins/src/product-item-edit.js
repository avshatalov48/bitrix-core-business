export default {
	computed:
		{
			getSrc()
			{
				return encodeURI(this.item.product.picture)
			}
		},
	methods:
		{
			hasSkyTree()
			{
				let tree = this.item.sku.tree.SELECTED_VALUES ?? {};
				return Object.keys(tree).length > 0;
			},
			hasProps()
			{
				return this.item.props.length > 0;
			}
		},
};