import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['mainContent', 'addRequired', 'resetValue']

    toggle()
    {
       this.mainContentTarget.classList.toggle('hidden');

       this.addRequiredTargets.forEach(target => {
           target.toggleAttribute("required")
           target.value = null;
       })

        this.resetValueTargets.forEach(target => {

            target.value = null;
        })

    }

}
