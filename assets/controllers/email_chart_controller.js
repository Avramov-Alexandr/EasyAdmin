import { Controller } from '@hotwired/stimulus';
import * as Chart from 'chart.js';

export default class extends Controller {
    static targets = ['chart'];

    connect() {
        const ctx = this.chartTarget.getContext('2d');
        const noneEmails = parseInt(this.data.get('none'), 10);
        const validEmails = parseInt(this.data.get('valid'), 10);
        const unknownEmails = parseInt(this.data.get('unknown'), 10);
        const riskyEmails = parseInt(this.data.get('risky'), 10);
        const invalidEmails = parseInt(this.data.get('invalid'), 10);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['None', 'Valid', 'Unknown', 'Risky', 'Invalid'],
                datasets: [{
                    data: [noneEmails, validEmails, unknownEmails, riskyEmails, invalidEmails],
                    backgroundColor: ['#cccccc', '#36a2eb', '#ffcd56', '#ff9f40', '#ff6384'],
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
    }
}
