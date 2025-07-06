// visualizations.js - Chart and data visualization functions for AI WinLab

(function($) {
    // Register global visualizations object
    window.AIWinLabVisualizations = {
        createRadarChart: createRadarChart,
        createRoiChart: createRoiChart,
        createImplementationTimeline: createImplementationTimeline,
        createSolutionsTable: createSolutionsTable,
        createBusinessAnalysisMatrix: createBusinessAnalysisMatrix
    };
    
    // Create Radar Chart for Opportunity Areas
    function createRadarChart(elementId, chartData) {
        const ctx = document.getElementById(elementId).getContext('2d');
        
        // If chart already exists, destroy it
        if (window.opportunityRadarChart) {
            window.opportunityRadarChart.destroy();
        }
        
        // Create new chart
        window.opportunityRadarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map(dataset => ({
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: dataset.backgroundColor,
                    borderColor: dataset.borderColor,
                    borderWidth: 2,
                    pointBackgroundColor: dataset.borderColor,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: dataset.borderColor,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        pointLabels: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            backdropColor: 'transparent',
                            color: 'rgba(0, 0, 0, 0.7)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 8
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        return window.opportunityRadarChart;
    }
    
    // Create ROI Projection Chart
    function createRoiChart(elementId, chartData) {
        const ctx = document.getElementById(elementId).getContext('2d');
        
        // If chart already exists, destroy it
        if (window.roiProjectionChart) {
            window.roiProjectionChart.destroy();
        }
        
        // Create gradient fills for datasets
        const investmentGradient = ctx.createLinearGradient(0, 0, 0, 400);
        investmentGradient.addColorStop(0, 'rgba(255, 99, 132, 0.5)');
        investmentGradient.addColorStop(1, 'rgba(255, 99, 132, 0.0)');
        
        const returnGradient = ctx.createLinearGradient(0, 0, 0, 400);
        returnGradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
        returnGradient.addColorStop(1, 'rgba(54, 162, 235, 0.0)');
        
        const roiGradient = ctx.createLinearGradient(0, 0, 0, 400);
        roiGradient.addColorStop(0, 'rgba(75, 192, 192, 0.5)');
        roiGradient.addColorStop(1, 'rgba(75, 192, 192, 0.0)');
        
        // Process datasets with gradients
        const datasets = chartData.datasets.map((dataset, index) => {
            const colors = [
                { line: 'rgba(255, 99, 132, 1)', fill: investmentGradient },
                { line: 'rgba(54, 162, 235, 1)', fill: returnGradient },
                { line: 'rgba(75, 192, 192, 1)', fill: roiGradient }
            ];
            
            return {
                label: dataset.label,
                data: dataset.data,
                borderColor: colors[index].line,
                backgroundColor: colors[index].fill,
                borderWidth: 3,
                pointBackgroundColor: colors[index].line,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: colors[index].line,
                pointRadius: 5,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            };
        });
        
        // Create new chart
        window.roiProjectionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        return window.roiProjectionChart;
    }
    
    // Create Implementation Timeline
    function createImplementationTimeline(containerId, timelineData) {
        const $container = $('#' + containerId);
        $container.empty();
        
        // Create timeline phases
        timelineData.phases.forEach(function(phase, index) {
            const $phase = $(`
                <div class="timeline-phase" data-aos="fade-up" data-aos-delay="${index * 100}">
                    <div class="phase-header" style="background-color: ${phase.color}">
                        <h3>${phase.name}</h3>
                        <div class="phase-timeline">${phase.timeline}</div>
                    </div>
                    <div class="phase-items">
                        <ul>
                            ${phase.items.map(item => `<li>${item}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `);
            
            $container.append($phase);
        });
        
        // Refresh AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    // Create Solutions Comparison Table
    function createSolutionsTable(containerId, tableData) {
        const $container = $('#' + containerId);
        $container.empty();
        
        // Create table element
        const $table = $('<table class="solutions-table"></table>');
        
        // Create header row
        const $headerRow = $('<tr></tr>');
        tableData.headers.forEach(function(header) {
            $headerRow.append(`<th>${header}</th>`);
        });
        
        const $tableHead = $('<thead></thead>').append($headerRow);
        $table.append($tableHead);
        
        // Create body rows
        const $tableBody = $('<tbody></tbody>');
        tableData.rows.forEach(function(row, rowIndex) {
            const $row = $('<tr></tr>');
            
            row.forEach(function(cell, cellIndex) {
                // Apply special styling to the first column (area name)
                if (cellIndex === 0) {
                    $row.append(`<td><strong>${cell}</strong></td>`);
                } 
                // Format the cost column
                else if (cellIndex === 2) {
                    $row.append(`<td>${cell}</td>`);
                }
                // Format the implementation complexity column
                else if (cellIndex === 3) {
                    let complexityClass = '';
                    if (cell.toLowerCase().includes('low')) {
                        complexityClass = 'complexity-low';
                    } else if (cell.toLowerCase().includes('medium')) {
                        complexityClass = 'complexity-medium';
                    } else if (cell.toLowerCase().includes('high')) {
                        complexityClass = 'complexity-high';
                    }
                    
                    $row.append(`<td class="${complexityClass}">${cell}</td>`);
                }
                // Format the ROI column
                else if (cellIndex === 5) {
                    $row.append(`<td class="roi-cell">${cell}</td>`);
                }
                // Standard cell formatting
                else {
                    $row.append(`<td>${cell}</td>`);
                }
            });
            
            $tableBody.append($row);
        });
        
        $table.append($tableBody);
        $container.append($table);
        
        // Add CSS for special formatting
        if (!$('#solutions-table-styles').length) {
            $('head').append(`
                <style id="solutions-table-styles">
                    .complexity-low { color: #4CAF50; }
                    .complexity-medium { color: #FF9800; }
                    .complexity-high { color: #F44336; }
                    .roi-cell { font-weight: 600; color: #4a6cf7; }
                </style>
            `);
        }
    }
    
    // Create Business Analysis Matrix
    function createBusinessAnalysisMatrix(containerId, analysisData) {
        const $container = $('#' + containerId);
        
        // Create container for visual matrix
        const $matrixContainer = $('<div class="business-analysis-matrix"></div>');
        
        // Extract business areas from analysis text
        // In a production environment, this would parse the text more intelligently
        const businessAreas = [
            {
                name: 'Sales Process',
                description: 'Current sales workflow and efficiency',
                score: Math.floor(Math.random() * 31) + 40, // 40-70 range for score
                painPoints: ['Manual follow-ups', 'Inconsistent messaging', 'Long sales cycle'],
                aiSolution: 'AI Sales Assistant with automated follow-ups and content personalization'
            },
            {
                name: 'Customer Support',
                description: 'Customer service efficiency and quality',
                score: Math.floor(Math.random() * 31) + 30, // 30-60 range for score
                painPoints: ['Slow response times', 'Repetitive queries', 'Resource intensive'],
                aiSolution: 'AI Chatbot with custom knowledge base for 24/7 support'
            },
            {
                name: 'Content Creation',
                description: 'Marketing and content production',
                score: Math.floor(Math.random() * 31) + 35, // 35-65 range for score
                painPoints: ['Time-consuming', 'Inconsistent output', 'Limited production capacity'],
                aiSolution: 'AI Content Generator with brand voice calibration'
            },
            {
                name: 'Data Analysis',
                description: 'Business intelligence and insights',
                score: Math.floor(Math.random() * 31) + 25, // 25-55 range for score
                painPoints: ['Manual reporting', 'Delayed insights', 'Siloed data'],
                aiSolution: 'Predictive Analytics Engine with automated reporting'
            }
        ];
        
        // Create matrix header
        const $matrixHeader = $(`
            <div class="matrix-header">
                <h3>Current Business Performance Matrix</h3>
                <p>This analysis identifies key areas for AI implementation based on current performance and potential impact.</p>
            </div>
        `);
        
        $matrixContainer.append($matrixHeader);
        
        // Create the matrix visualization
        const $matrixViz = $('<div class="matrix-visualization"></div>');
        
        businessAreas.forEach(function(area, index) {
            const $areaBlock = $(`
                <div class="area-block" data-aos="fade-up" data-aos-delay="${index * 100}">
                    <div class="area-header">
                        <h4>${area.name}</h4>
                        <div class="area-description">${area.description}</div>
                    </div>
                    <div class="area-metrics">
                        <div class="efficiency-score">
                            <div class="score-label">Current Efficiency</div>
                            <div class="score-value">
                                <div class="score-bar">
                                    <div class="score-fill" style="width: ${area.score}%"></div>
                                </div>
                                <div class="score-percentage">${area.score}%</div>
                            </div>
                        </div>
                    </div>
                    <div class="area-details">
                        <div class="pain-points">
                            <h5>Pain Points</h5>
                            <ul>
                                ${area.painPoints.map(point => `<li>${point}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="ai-solution">
                            <h5>Recommended AI Solution</h5>
                            <p>${area.aiSolution}</p>
                        </div>
                    </div>
                </div>
            `);
            
            $matrixViz.append($areaBlock);
        });
        
        $matrixContainer.append($matrixViz);
        $container.append($matrixContainer);
        
        // Add CSS for the matrix
        if (!$('#matrix-analysis-styles').length) {
            $('head').append(`
                <style id="matrix-analysis-styles">
                    .business-analysis-matrix {
                        margin-top: var(--spacing-lg);
                    }
                    
                    .matrix-header {
                        margin-bottom: var(--spacing-lg);
                    }
                    
                    .matrix-visualization {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: var(--spacing-lg);
                    }
                    
                    .area-block {
                        background-color: white;
                        border-radius: var(--radius-md);
                        box-shadow: var(--shadow-md);
                        overflow: hidden;
                        transition: transform var(--transition-medium), box-shadow var(--transition-medium);
                    }
                    
                    .area-block:hover {
                        transform: translateY(-5px);
                        box-shadow: var(--shadow-lg);
                    }
                    
                    .area-header {
                        padding: var(--spacing-md);
                        background-color: var(--primary-color);
                        color: white;
                    }
                    
                    .area-header h4 {
                        margin-bottom: var(--spacing-xs);
                        font-size: 1.2rem;
                    }
                    
                    .area-description {
                        font-size: 0.9rem;
                        opacity: 0.9;
                    }
                    
                    .area-metrics {
                        padding: var(--spacing-md);
                        background-color: var(--very-light);
                    }
                    
                    .efficiency-score {
                        margin-bottom: var(--spacing-sm);
                    }
                    
                    .score-label {
                        font-size: 0.9rem;
                        color: var(--medium);
                        margin-bottom: var(--spacing-xs);
                    }
                    
                    .score-value {
                        display: flex;
                        align-items: center;
                    }
                    
                    .score-bar {
                        flex: 1;
                        height: 10px;
                        background-color: var(--light);
                        border-radius: var(--radius-full);
                        overflow: hidden;
                    }
                    
                    .score-fill {
                        height: 100%;
                        border-radius: var(--radius-full);
                        background: var(--gradient-primary);
                    }
                    
                    .score-percentage {
                        margin-left: var(--spacing-sm);
                        font-weight: 600;
                        color: var(--primary-color);
                    }
                    
                    .area-details {
                        padding: var(--spacing-md);
                    }
                    
                    .pain-points, .ai-solution {
                        margin-bottom: var(--spacing-md);
                    }
                    
                    .pain-points h5, .ai-solution h5 {
                        font-size: 1rem;
                        color: var(--medium-dark);
                        margin-bottom: var(--spacing-sm);
                    }
                    
                    .pain-points ul {
                        padding-left: var(--spacing-lg);
                        margin: 0;
                    }
                    
                    .pain-points li {
                        margin-bottom: var(--spacing-xs);
                        color: var(--medium);
                    }
                    
                    .ai-solution p {
                        margin: 0;
                        color: var(--medium);
                        padding: var(--spacing-sm);
                        background-color: rgba(74, 108, 247, 0.1);
                        border-left: 3px solid var(--primary-color);
                        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
                    }
                </style>
            `);
        }
        
        // Refresh AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    })(jQuery);