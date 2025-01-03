$.Redactor.prototype.advanced = function()
{
    return {
        getTemplate: function()
        {
            return String()
            + '<div class="modal-section" id="redactor-modal-advanced">'
                + '<section>'
                    + '<label>Enter a text</label>'
                    + '<textarea id="mymodal-textarea" rows="6"></textarea>'
                + '</section>'
                + '<section>'
                    + '<button id="redactor-modal-button-action">Insert</button>'
                    + '<button id="redactor-modal-button-cancel">Cancel</button>'
                + '</section>'
            + '</div>';
        },
        init: function ()
        {
            var button = this.button.add('advanced', 'Advanced');
            this.button.addCallback(button, this.advanced.show);
        },
        show: function()
        {
            this.modal.addTemplate('advanced', this.advanced.getTemplate());
            this.modal.load('advanced', 'Advanced Modal', 400);

            var button = this.modal.getActionButton();
            button.on('click', this.advanced.insert);

            this.modal.show();

            $('#mymodal-textarea').focus();
        },
        insert: function()
        {
            var html = $('#mymodal-textarea').val();

            this.modal.close();
            this.insert.html(html);
        }
    };
};