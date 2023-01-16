import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['mainContent']

    connect() {
        console.log("hello");
    }

    toggle(event)
    {
       this.mainContentTarget.classList.toggle('hidden');
       // Remove data from form?
    }

}
