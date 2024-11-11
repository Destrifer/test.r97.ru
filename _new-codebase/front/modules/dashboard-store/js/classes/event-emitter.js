class EventEmitter{


    constructor() {
        this.handlers = {};
    }


    on(event, handler) {
        const events = event.split(' ');
        events.forEach((event) => {
            if (!this.handlers[event]) {
                this.handlers[event] = [];
            }
            this.handlers[event].push(handler);
        });
    }


    _fire(event) {
        if (!this.handlers[event]) {
            return;
        }
        this.handlers[event].forEach((handler) => {
            handler(this.data);
        });
    }

}