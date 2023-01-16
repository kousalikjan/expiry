import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input']

    value = 0;

    connect()
    {
        this.value = this.inputTarget.value;
    }

    minus(e)
    {
        e.preventDefault();
        this.inputTarget.value = --this.value;
    }

    plus(e)
    {
        e.preventDefault();
        this.inputTarget.value = ++this.value;
    }

}
