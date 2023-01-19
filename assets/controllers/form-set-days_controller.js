import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input']

    set(e)
    {
        e.preventDefault();
        if(e.target.dataset.days)
        {
            this.inputTarget.value = e.target.dataset.days;
        }
    }

}
