import { Controller } from '@hotwired/stimulus';
import * as Chart from 'chart.js';

export default class extends Controller {
    static targets = ['chart'];

    connect() {
        const ctx = this.chartTarget.getContext('2d');
        new Chart.Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April'],
                datasets: [
                    {
                        label: 'Sales',
                        data: [65, 59, 80, 81],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            },
        });
    }
}
