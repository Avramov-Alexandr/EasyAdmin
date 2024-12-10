import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['output'];

    connect() {
        console.log('Stimulus controller connected!');
        this.outputTarget.textContent = 'Connected to Stimulus!';
    }

    changeText() {
        this.outputTarget.textContent = 'You clicked the button!';
    }
}
