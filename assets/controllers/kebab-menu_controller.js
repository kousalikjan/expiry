import { Controller } from '@hotwired/stimulus';
import { useClickOutside } from 'stimulus-use'

export default class extends Controller {

    static targets = ['content']

    connect() {
        useClickOutside(this);
        this.hideAll();
    }

    toggle(event)
    {
        const target = event.currentTarget.parentNode.parentNode.parentNode.querySelector('ul');
        const hidden = target.classList.contains('hidden');
        this.hideAll();
        if(hidden)
            target.classList.remove('hidden');
    }

    clickOutside() {
        this.hideAll();
    }

    hideAll = () => this.contentTargets.forEach(target => target.classList.add("hidden"));
}
