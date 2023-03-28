import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    checkAndClose(event)
    {
        // Taken from https://github.com/saadeghi/daisyui/issues/157#issuecomment-979073401
        let targetEl = event.currentTarget;
        if(targetEl && targetEl.matches(':focus'))
        {
            setTimeout(function(){
                targetEl.blur();
            }, 0);
        }
    }

}
