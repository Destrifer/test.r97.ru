/* global Table, Sorting, Filter, Paginator, State, ColsBuilder, Resizer, Preloader, TopBtn */

document.addEventListener('DOMContentLoaded', async function () {
    const state = new State();
    const table = new Table();
    const preloader = new Preloader();
    state.on('loading', () => {
        preloader.show();
    }); 
    state.on('changed loaded', () => {
        table.load();
    });
    await state.load();
    const filter = new Filter(state);
    const topBtn = new TopBtn(table.viewport);
    const colsBuilder = new ColsBuilder(state.tab);
    const resizer = new Resizer(colsBuilder);
    const paginator = new Paginator(state);
    const sorting = new Sorting(state);
    table.on('loading', () => {
        preloader.show();
    });
    table.on('loaded', () => {
        preloader.hide();
        sorting.updateView(table.rows);
        paginator.updateView(table.pagination);
    });
});