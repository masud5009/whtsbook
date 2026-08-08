(function ($) {
    "use strict";
    var lineChart = document.getElementById('lineChart').getContext('2d');
    var myLineChart = new Chart(lineChart, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: __MonthlyIncome__,
                borderColor: "#1d7af3",
                pointBorderColor: "#FFF",
                pointBackgroundColor: "#1d7af3",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: 'transparent',
                fill: true,
                borderWidth: 2,
                data: inTotals
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    fontColor: '#1d7af3',
                }
            },
            tooltips: {
                bodySpacing: 4,
                mode: "nearest",
                intersect: 0,
                position: "nearest",
                xPadding: 10,
                yPadding: 10,
                caretPadding: 10
            },
            layout: {
                padding: { left: 15, right: 15, top: 15, bottom: 15 }
            }
        }
    });

    var usersChart = document.getElementById('usersChart').getContext('2d');
    var myUsersChart = new Chart(usersChart, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: __MonthlyPremiumUsers__,
                borderColor: "#1d7af3",
                pointBorderColor: "#FFF",
                pointBackgroundColor: "#1d7af3",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: 'transparent',
                fill: true,
                borderWidth: 2,
                data: userTotals
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    fontColor: '#1d7af3',
                }
            },
            tooltips: {
                bodySpacing: 4,
                mode: "nearest",
                intersect: 0,
                position: "nearest",
                xPadding: 10,
                yPadding: 10,
                caretPadding: 10
            },
            layout: {
                padding: { left: 15, right: 15, top: 15, bottom: 15 }
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
            labels: [
                __UsedTokens__,
                __RemainingTokens__,
                __EstimatedUsage__
            ],
            datasets: [{
                data: [usedToken, availableToken, estimatedToken],
                backgroundColor: ['#DC143C', '#3CB371', '#FFA500'],
                hoverOffset: 10,
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 12,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw}`
                    }
                }
            }
        }
    });


})(jQuery);
