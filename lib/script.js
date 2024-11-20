<script>
    const studentData = {
        labels: <?php echo json_encode($student_labels); ?>,
        datasets: [{
            label: 'Number of Students',
            data: <?php echo json_encode($student_data); ?>,
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',  // Blue with opacity
                'rgba(40, 167, 69, 0.8)',  // Green with opacity
                'rgba(255, 193, 7, 0.8)',  // Yellow with opacity
                'rgba(220, 53, 69, 0.8)'   // Red with opacity
            ],
            borderColor: [
                '#0056b3', // Darker Blue
                '#1e7e34', // Darker Green
                '#d39e00', // Darker Yellow
                '#b21f2d'  // Darker Red
            ],
            borderWidth: 1 // Thickness of the bar border
        }]
    };

    const studentCtx = document.getElementById('studentsChart').getContext('2d');
    new Chart(studentCtx, {
        type: 'bar',
        data: studentData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#ffffff', // Legend text color
                        font: {
                            size: 14 // Font size of the legend
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.7)', // Tooltip background color
                    titleColor: '#ffffff', // Tooltip title color
                    bodyColor: '#ffffff', // Tooltip body text color
                    footerColor: '#ffffff' // Tooltip footer text color
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#ffffff', // X-axis labels color
                        font: {
                            size: 14 // Font size of X-axis labels
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.2)', // Light white grid lines
                    }
                },
                y: {
                    ticks: {
                        color: '#ffffff', // Y-axis labels color
                        font: {
                            size: 14 // Font size of Y-axis labels
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.2)', // Light white grid lines
                    },
                    title: {
                        display: true,
                        text: 'Number of Students', // Y-axis title
                        color: '#ffffff', // Y-axis title color
                        font: {
                            size: 16 // Font size of the Y-axis title
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 20, // Add space at the top
                    bottom: 20 // Add space at the bottom
                }
            }
        }
    });
</script>
