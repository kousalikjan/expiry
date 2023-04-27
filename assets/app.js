import Swal from 'sweetalert2';

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// Taken from: https://symfonycasts.com/screencast/turbo/sweet-alert
document.addEventListener('turbo:before-cache', () => {
    if (document.body.classList.contains('modal-open')) {
        const modalEl = document.querySelector('.modal');
        const modal = Modal.getInstance(modalEl);
        modalEl.classList.remove('fade');
        modal._backdrop._config.isAnimated = false;
        modal.hide();
        modal.dispose();
    }
    if (Swal.isVisible()) {
        Swal.getPopup().style.animationDuration = '0ms'
        Swal.close();
    }
});

document.addEventListener('turbo:frame-missing', event => {
    const { detail: { response, visit } } = event;
    event.preventDefault();
    visit(response.url);
})