import { Controller } from '@hotwired/stimulus';
import {useTransition} from "stimulus-use";

export default class extends Controller {

    DEFAULT_TIMEOUT = 6500;

    connect()
    {
        useTransition(this, {
            enterActive: 'transition ease-out duration-300',
            enterFrom: 'transform opacity-0 scale-95',
            enterTo: 'transform opacity-100 scale-100',
            leaveActive: 'transition ease-in duration-300',
            leaveFrom: 'transform opacity-100 scale-100',
            leaveTo: 'transform opacity-0 scale-95',
            hiddenClass: 'hidden',
            transitioned: true
        });
        setTimeout(() => {
            this.leave();
        }, this.DEFAULT_TIMEOUT);
    }

    close(){
        this.leave();
    }



}
