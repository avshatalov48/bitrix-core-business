export default {
	data()
	{
		return {
			wait: false
		}
	},
	methods:
	{
		setWait()
		{
			this.wait = true;
		}
	},
	computed:
	{
		getObjectClass()
		{
			const classes = [
				'btn',
				'btn-checkout-order-status',
				'btn-md',
				'rounded-pill'
			];
			
			if(this.wait)
			{
				classes.push('btn-wait')
			}
			return classes;
		}
	}
};