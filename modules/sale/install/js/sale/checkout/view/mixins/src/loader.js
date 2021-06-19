import { Loader as LoaderConst } from 'sale.checkout.const';
import { Loader } from 'main.loader';

export default {
    methods:
        {
            changeStatus()
            {
                if(this.config.status === LoaderConst.status.wait)
                {
                    this.loader.show(this.$refs.container);
                }
                else
                {
                    this.loader.hide();
                }
            },
            initLoader()
            {
                this.loader = new Loader({size: 64});
            }
        },
    computed:
        {
            getStatus()
            {
                return this.config.status;
            }
        },
    watch:
        {
            getStatus()
            {
                this.changeStatus()
            }
        },
    created()
    {
        this.initLoader();
    },
    mounted()
    {
        if(this.config.status === LoaderConst.status.wait)
        {
            this.loader.show(this.$refs.container);
        }
    }
};