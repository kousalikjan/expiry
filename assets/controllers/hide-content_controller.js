import { Controller } from '@hotwired/stimulus';
import { visit } from '@hotwired/turbo';

export default class extends Controller {

    static targets = ['content', 'text'];
    static values = {
        hiddenText: String,
        visibleText: String,
        visible: Boolean,
        resetUrl: {type: String, default: 'none'},
        dataFrame: {type: String, default: '_top'}
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

    resetForm(event) {
        if(this.resetUrlValue !== 'none')
        {
            visit(this.resetUrlValue, {frame: this.dataFrameValue, action: 'replace'});
        }

    }

}
