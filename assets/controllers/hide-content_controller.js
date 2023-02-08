import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['content', 'text'];
    static values = {
        hiddenText: String,
        visibleText: String,
        visible: Boolean
    };

    connect() {
        this.visibleValue = !this.visibleValue;
        this.toggle();
    }

    toggle() {
        this.visibleValue = !this.visibleValue;
        if(this.visibleValue)
            this.show();
        else
            this.hide();
    }

    show() {
        this.textTarget.innerText = this.visibleTextValue;
        this.contentTarget.classList.remove('hidden');
    }

    hide() {

        this.textTarget.innerText = this.hiddenTextValue;
        this.contentTarget.classList.add('hidden');
    }

}
