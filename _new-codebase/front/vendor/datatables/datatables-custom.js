const datatableDefaultConfig = {
    'stateSave': true,
    'pageLength': 50,
    'lengthMenu': [
        [25, 50, 100, 300, -1],
        [25, 50, 100, 300, 'Все'],
    ],
    'order': [
        [0, 'desc']
    ],
    'fixedHeader': true,
    'dom': `<'row'<'col-sm-1'l><'col-sm-6'p><'col-sm-5'f>>
               <'row'<'col-sm-12'tr>>
            <'row'<'col-sm-5'i><'col-sm-7'p>>`,
    'oLanguage': {
        'sLengthMenu': 'Кол-во: _MENU_ ',
        'sZeroRecords': 'Записей нет.',
        'sInfo': 'Показано от _START_ до _END_ из _TOTAL_ записей',
        'sInfoEmpty': 'Записей нет.',
        'sProcessing': 'Загружаются данные...',
        'oPaginate': {
            'sFirst': 'Первая',
            'sLast': 'Последняя',
            'sNext': '>>',
            'sPrevious': '<<',
        },
        'sSearch': 'Поиск',
        'sInfoFiltered': '(отфильтровано из _MAX_ записи/(ей)'
    }
};