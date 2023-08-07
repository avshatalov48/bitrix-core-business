const Filter = {
    replace: value => {
        return (value || '').replace(/[^+\d]/g, '');
    },
};

export {Filter};