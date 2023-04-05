import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input', 'uploadButton', 'spinner']

    sendSelectedFile()
    {
        if(this.inputTarget.value !== "")
        {
            this.spinnerTarget.classList.remove('hidden');
            this.element.requestSubmit();
        }

    }
}
