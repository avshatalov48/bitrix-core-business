import {Vue} from 'ui.vue';
import 'main.polyfill.promise';

let loadAppPromise = null;

Vue.component('bx-crm-form', {
	props: {
		id: {
			type: String,
			required: true,
		},
		sec: {
			type: String,
			required: true,
		},
		lang: {
			type: String,
			required: true,
			default: 'en',
		},
		address: {
			type: String,
			required: true,
			default: function () {
				return window.location.origin;
			},
		},
		design: {
			type: Object,
			required: false,
			default: function () {
				return {
					compact: true,
				};
			},
		},
	},
	data()
	{
		return {
			message: '',
			obj: {

			},
		}
	},
	beforeDestroy()
	{
		if (this.obj.instance)
		{
			this.obj.instance.destroy();
		}
	},
	mounted()
	{
		const loadForm = () => {
			this.message = '';
			this.obj.config.data.node = this.$el;
			this.obj.config.data.design = {
				...this.obj.config.data.design,
				...this.design
			};
			this.obj.instance = window.b24form.App.createForm24(
				this.obj.config,
				this.obj.config.data
			);
			this.obj.instance.subscribeAll((data, instance, type) => {
				this.$emit('form:' + type, data);
			})
		};

		this.message = '...Loading';
		let promise = null;
		if (window.fetch)
		{
			const formData = new FormData();
			formData.append('id', this.id);
			formData.append('sec', this.sec);
			promise = fetch(
				this.address + `/bitrix/services/main/ajax.php?action=crm.site.form.get`,
				{
					method: 'POST',
					body: formData,
					mode: "cors",
				}
			);
		}
		else
		{
			this.message = 'error';
			return;
		}

		promise.then(response => response.json())
			.then(data => {
				if (data.error)
				{
					throw new Error(data.error_description)
				}
				this.obj.config = data.result.config;

				if (window.b24form && window.b24form.App)
				{
					loadForm();
					return;
				}

				if (!loadAppPromise)
				{
					loadAppPromise = new Promise((resolve, reject) => {
						const node = document.createElement('script');
						node.src = data.result.loader.app.link;
						node.onload = resolve;
						node.onerror = reject;
						document.head.appendChild(node);
					});
				}
				loadAppPromise.then(loadForm).catch((e) => {
					this.message = 'App load failed:' + e;
				});

			}).catch(error => {
			this.message = error;
		});
	},
	template: `
		<div>
			<div v-if="message">{{ message }}</div>
		</div>
	`
});