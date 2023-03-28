import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input', 'uploadButton']

    connect() {
        this.changeUploadButtonColor();
    }

    // used by sweetalert-confirm controller when editing an item
    hasSelectedFile()
    {
        return this.inputTarget.value !== "";
    }

    changeUploadButtonColor()
    {
        /*if(this.hasSelectedFile())
            this.element.requestSubmit();*/
        const ACTIVE_CLASSES = ['btn-primary'];
        const NON_ACTIVE_CLASSES = ['btn-info', 'btn-outline'];
        const submitBtn = document.querySelector('.submit-button');
        if(this.hasSelectedFile())
        {
            this.uploadButtonTarget.classList.remove(...NON_ACTIVE_CLASSES);
            this.uploadButtonTarget.classList.add(...ACTIVE_CLASSES);
            submitBtn.classList.remove(...ACTIVE_CLASSES);
            submitBtn.classList.add(...NON_ACTIVE_CLASSES)
        }
        else
        {
            this.uploadButtonTarget.classList.remove(...ACTIVE_CLASSES);
            this.uploadButtonTarget.classList.add(...NON_ACTIVE_CLASSES);
            submitBtn.classList.remove(...NON_ACTIVE_CLASSES);
            submitBtn.classList.add(...ACTIVE_CLASSES)
        }
    }

}
