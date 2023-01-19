import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input']

    minus(e)
    {
        e.preventDefault();
        this.inputTarget.value = --this.inputTarget.value;
    }

    plus(e)
    {
        e.preventDefault();
        this.inputTarget.value = ++this.inputTarget.value;
    }

}
