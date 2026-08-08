(function ($) {
    "use strict";

    function fillMonths(mapperFn, rows) {
        const out = new Array(12).fill(0);

        rows.forEach(row => {
            const month = Number(row.month);

            if (month >= 1 && month <= 12) {
                out[month - 1] = mapperFn(row);
            }
        });

        return out;
    }

    function rowsByYear(rows, year) {
        return rows.filter(row => Number(row.year) === Number(year));
    }

    function bookingDatasets(rows) {
        return {
            completed: fillMonths(row => Number(row.completed || 0), rows),
            pending: fillMonths(row => Number(row.pending || 0), rows),
            cancelled: fillMonths(row => Number(row.cancelled || 0), rows)
        };
    }

    function incomeDatasets(rows) {
        return {
            income: fillMonths(row => Number(row.total || 0), rows),
            due: fillMonths(row => Number(row.due || 0), rows)
        };
    }

    /* Monthly Booking Chart
    */
    const bookingCtx = document.getElementById('MonthlyBookingStatusChart').getContext('2d');
    const initialBookingRows = rowsByYear(allBookingRows, bookingChartYear).length > 0 ? rowsByYear(allBookingRows, bookingChartYear) : bookingRows;
    const initialBookingData = bookingDatasets(initialBookingRows);
    const bookingChart = new Chart(bookingCtx, {
        type: 'bar',
        data: {
            labels: MONTHS,
            datasets: [
                {
                    label: CompletedText,
                    data: initialBookingData.completed,
                    backgroundColor: '#3CB371',
                    borderRadius: 6,
                    borderWidth: 0
                },
                {
                    label: PendingText,
                    data: initialBookingData.pending,
                    backgroundColor: '#FFA500',
                    borderRadius: 6,
                    borderWidth: 0
                },
                {
                    label: CancelledText,
                    data: initialBookingData.cancelled,
                    backgroundColor: '#DC143C',
                    borderRadius: 6,
                    borderWidth: 0
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Month-wise Bookings (Status)' }
            },
            scales: {
                x: { stacked: false },
                y: { beginAtZero: true }
            }
        }
    });
    /**
     * Ai Uage Chart
     */
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [UsedTokensText, AvailableTokensText],
            datasets: [{
                data: [usedTokens, availableToken],
                backgroundColor: ['#DC143C', '#3CB371'],
                hoverOffset: 10,
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '100%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10
                }
            }
        }
    });

    /**
     * Monthly Booking Income
     */
    const incomeCtx = document.getElementById('MonthlyIncomeChart').getContext('2d');
    const initialIncomeRows = rowsByYear(allIncomeRows, incomeChartYear).length > 0 ? rowsByYear(allIncomeRows, incomeChartYear) : incomeRows;
    const initialIncomeData = incomeDatasets(initialIncomeRows);
    const incomeChart = new Chart(incomeCtx, {
        type: 'bar',
        data: {
            labels: MONTHS,
            datasets: [{
                label: IncomeText,
                data: initialIncomeData.income,
                backgroundColor: '#3CB371',
                borderRadius: 6,
                borderWidth: 0
            },
            {
                label: DueText,
                data: initialIncomeData.due,
                backgroundColor: '#FFA500',
                borderRadius: 6,
                borderWidth: 0
            },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Month-wise Income' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const bookingYearSelect = document.getElementById('bookingYearSelect');
    const incomeYearSelect = document.getElementById('incomeYearSelect');

    if (bookingYearSelect) {
        bookingYearSelect.addEventListener('change', function () {
            const yearRows = rowsByYear(allBookingRows, this.value);
            const dataset = bookingDatasets(yearRows);

            bookingChart.data.datasets[0].data = dataset.completed;
            bookingChart.data.datasets[1].data = dataset.pending;
            bookingChart.data.datasets[2].data = dataset.cancelled;
            bookingChart.update();
        });
    }

    if (incomeYearSelect) {
        incomeYearSelect.addEventListener('change', function () {
            const yearRows = rowsByYear(allIncomeRows, this.value);
            const dataset = incomeDatasets(yearRows);

            incomeChart.data.datasets[0].data = dataset.income;
            incomeChart.data.datasets[1].data = dataset.due;
            incomeChart.update();
        });
    }
})(jQuery);
