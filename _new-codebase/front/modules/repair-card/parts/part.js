function Part($part) {
    this.$elem = $part;
}

Part.prototype.isNew = function() {
    return !!this.$elem.find('[data-input="new-flag"]').val();
};

Part.prototype.getURI = function() {
    return `${this.getID()}-${this.getDepotID()}-${this.getOrigin()}`;
};

Part.prototype.getID = function() {
    return this.$elem.data('id');
};

Part.prototype.getDepotID = function() {
    return this.$elem.find('[data-input="depot-id"]').val();
};

Part.prototype.getAvailableDepotIDs = function() {
    const depots = this.$elem[0].querySelectorAll('[data-depot-id]');
    const res = [];
    depots.forEach(depot => {
        res.push(depot.dataset.depotId);
    });
    return res;
};

Part.prototype.getOrigin = function() {
    return this.$elem.data('origin');
};

Part.prototype.getName = function() {
    return this.$elem[0].querySelector('[data-elem="name"]').innerText;
};

Part.prototype.getPartCode = function() {
    const elem = this.$elem[0].querySelector('[data-elem="part-code"]');
    return (elem) ? elem.innerText : '';
};

Part.prototype.getGroup = function() {
    return this.$elem[0].querySelector('[data-elem="group"]').innerText;
};

Part.prototype.getGroupID = function() {
    return +this.$elem[0].dataset.groupId;
};

Part.prototype.getAttrID = function() {
    return +this.$elem[0].dataset.attrId;
};

Part.prototype.getTypeID = function() {
    return +this.$elem[0].dataset.typeId;
};

Part.prototype.hasOriginal = function() {
    return +this.$elem[0].dataset.hasOriginalFlag;
};

Part.prototype.cancel = function() {
    this.$elem.addClass('cancel');
};

Part.prototype.undoCancel = function() {
    this.$elem.removeClass('cancel');
};

Part.prototype.show = function() {
    return this.$elem.show();
};

Part.prototype.hide = function() {
    return this.$elem.hide();
};

Part.prototype.remove = function() {
    this.$elem.remove();
};