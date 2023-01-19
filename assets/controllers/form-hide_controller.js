import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['mainContent', 'expirationInput', 'notifyInput']

    toggle()
    {
       this.mainContentTarget.classList.toggle('hidden');

       if(this.hasExpirationInputTarget)
       {
           this.expirationInputTarget.toggleAttribute("required");
           this.expirationInputTarget.value = this.expirationInputTarget.defaultValue;
       }

       if(this.hasNotifyInputTarget)
       {
           console.log("xddd");
           this.notifyInputTarget.value = this.notifyInputTarget.defaultValue;
       }
    }

}
