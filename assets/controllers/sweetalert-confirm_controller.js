import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2'
import { visit } from '@hotwired/turbo';

export default class extends Controller {

    static values = {
        title: String,
        text: String,
        confirmButtonText: String,
        cancelButtonText: String,
        urlConfirm: {type: String, default: 'none'},
        icon: {type: String, default: 'warning'},
        confirmColor: {type: String, default: '#dc5b5b'},
        dataFrame: {type: String, default: '_top'}
    }

    onSubmit(event) {
        event.preventDefault();
        Swal.fire({
            title: this.titleValue,
            text: this.textValue,
            icon: this.iconValue,
            showCancelButton: true,
            cancelButtonText: this.cancelButtonTextValue,
            cancelButtonColor: '#b4bbc2',
            confirmButtonColor: this.confirmColorValue,
            confirmButtonText: this.confirmButtonTextValue,
        }).then((result) => {
            if (result.isConfirmed) {
                if(this.urlConfirmValue === 'none')
                    this.element.parentNode.submit();
                else
                    visit(this.urlConfirmValue, {frame: this.dataFrameValue});
            }
        })
    }
}