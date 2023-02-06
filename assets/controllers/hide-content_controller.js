import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['content', 'text'];
    static values = {
        hiddenText: String,
        visibleText: String,
    };

    visible = false;


    toggle(event)
    {
        this.visible = !this.visible;
        this.textTarget.innerText = this.visible ? this.visibleTextValue : this.hiddenTextValue;
        this.contentTarget.classList.toggle('hidden')
    }

}
