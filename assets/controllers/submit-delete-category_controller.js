import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2'

export default class extends Controller {

    static values = {
        categoryName: String,
    }

    onSubmit(event) {
        event.preventDefault();
        Swal.fire({
            title: `Delete ${this.categoryNameValue}?`,
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#b4bbc2',
            confirmButtonColor: '#dc5b5b',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                this.element.parentNode.submit();
            }
        })
    }

}