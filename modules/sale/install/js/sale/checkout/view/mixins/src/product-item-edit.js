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
				return Object.keys(this.item.sku.tree).length > 0;
			},
			hasProps()
			{
				return this.item.props.length > 0;
			}
		},
};