import { Controller } from '@hotwired/stimulus';
import { useClickOutside, useDebounce, useTransition } from 'stimulus-use'

export default class extends Controller {

    static targets = ['result', 'input']

    static values = {
        url: String
    }

    static debounces = ['search']

    connect() {
        useDebounce(this);
        useClickOutside(this, {element: this.resultTarget});
        useTransition(this, {
            element: this.resultTarget,
            enterActive: 'transition ease-out duration-200',
            enterFrom: 'transform opacity-0 scale-95',
            enterTo: 'transform opacity-100 scale-100',
            leaveActive: 'transition ease-in duration-200',
            leaveFrom: 'transform opacity-100 scale-100',
            leaveTo: 'transform opacity-0 scale-95',
            hiddenClass: 'hidden'
        });
    }

    onSearchInput() {
        this.search()
    }

    async search()
    {
        const term = this.inputTarget.value;

        if(term === "")
        {
            this.leave();
            return;
        }

        const response = await fetch(`${this.urlValue}?term=${term}`);
        this.resultTarget.innerHTML = await response.text();
        this.enter();
    }

    clickOutside(event) {
        this.leave();
    }

}
