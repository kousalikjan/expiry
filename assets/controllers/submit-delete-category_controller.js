import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2'

export default class extends Controller {

    static values = {
        title: String,
        text: String,
        confirmButtonText: String
    }

    onSubmit(event) {
        event.preventDefault();
        Swal.fire({
            title: this.titleValue,
            text: this.textValue,
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#b4bbc2',
            confirmButtonColor: '#dc5b5b',
            confirmButtonText: this.confirmButtonTextValue,
        }).then((result) => {
            if (result.isConfirmed) {
                this.element.parentNode.submit();
            }
        })
    }

}