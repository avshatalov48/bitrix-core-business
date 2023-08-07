class Pool
{
    constructor()
    {
        this.pool = {};
    }

    add(cmd, index, fields)
    {
        if (!this.pool.hasOwnProperty(index))
        {
            this.pool[index] = [];
        }

        this.pool[index].push({
            [cmd]: {fields}
        });
    }

    get()
    {
        return this.pool;
    }

    clean()
    {
        this.pool = {};
    }

    isEmpty()
    {
        return Object.keys(this.pool).length === 0;
    }
}

export {
    Pool
}