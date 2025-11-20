const API_BASE_URL = '../backend/api';

$(document).ready(function() {
    $('#scraper-form').on('submit', function(e) {
        e.preventDefault();
        scrapeNews();
    });
});

// Function to scrape news from a website
function scrapeNews() {
    const url = $('#news-url').val().trim();
    
    if (!url) {
        showError('Please enter a valid URL');
        return;
    }

    $('#loader').show();
    $('#error').hide();
    $('#results').html('').hide();

    $.ajax({
        url: `${API_BASE_URL}/news-scraper.php`,
        method: 'GET',
        data: { url: url },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                displayResults(data);
            } else {
                showError(data.error || 'Failed to scrape news');
            }
        },
        error: function(xhr, status, error) {
            showError('Network error: ' + error);
        },
        complete: function() {
            $('#loader').hide();
        }
    });
}

// Display scraping results
function displayResults(data) {
    let html = `
        <div class="results-header bg-primary">
            <h2>Results for: ${data.url}</h2>
            <p class="timestamp">Fetched at: ${data.timestamp}</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>${data.stats.total_articles}</h3>
                <p>Articles</p>
            </div>
            <div class="stat-card">
                <h3>${data.stats.total_keywords}</h3>
                <p>Keywords</p>
            </div>
            <div class="stat-card">
                <h3>${data.stats.total_trends}</h3>
                <p>Trends</p>
            </div>
        </div>

        <div class="charts-row">
            ${renderTrendsChart(data.data.trends)}
            ${renderKeywordsChart(data.data.keywords)}
        </div>
        
        ${renderArticles(data.data.articles)}
    `;

    $('#results').html(html).fadeIn();
}


// Render trends section with pie chart
function renderTrendsChart(trends) {
    if (!trends || trends.length === 0) {
        return '<div class="section chart-half"><h3>Trends</h3><p>No trends identified</p></div>';
    }

    // Generate unique ID for canvas
    const chartId = 'trendsChart_' + Date.now();
    
    // Define colors for different categories
    const categoryColors = {
        'politics': '#FF6384',
        'business': '#36A2EB',
        'technology': '#FFCE56',
        'health': '#4BC0C0',
        'sport': '#9966FF',
        'entertainment': '#FF9F40',
        'environment': '#4CAF50',
        'crime': '#F44336'
    };
    
    // Prepare data
    const labels = trends.map(t => t.category.charAt(0).toUpperCase() + t.category.slice(1));
    const data = trends.map(t => t.score);
    const colors = trends.map(t => categoryColors[t.category] || '#999999');
    
    // After rendering, create the chart
    setTimeout(() => {
        const ctx = document.getElementById(chartId);
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        title: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }, 100);

    return `
        <div class="section chart-half">
            <h3>Trends</h3>
            <div class="chart-container chart-pie">
                <canvas id="${chartId}"></canvas>
            </div>
        </div>
    `;
}

// Render keywords section with bar chart
function renderKeywordsChart(keywords) {
    if (!keywords || keywords.length === 0) {
        return '<div class="section"><h3>Keywords</h3><p>No keywords extracted</p></div>';
    }

    // Generate unique ID for canvas
    const chartId = 'keywordsChart_' + Date.now();
    
    // Take top 25 keywords (increased to show more including less frequent but important terms)
    const topKeywords = keywords.slice(0, 25);
    
    // After rendering, create the chart
    setTimeout(() => {
        const ctx = document.getElementById(chartId);
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topKeywords.map(kw => kw.keyword),
                    datasets: [{
                        label: 'Frequency',
                        data: topKeywords.map(kw => kw.frequency),
                        backgroundColor: 'rgba(102, 126, 234, 0.6)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        }
                    }
                }
            });
        }
    }, 100);

    return `
        <div class="section chart-half">
            <h3>Top Keywords</h3>
            <div class="chart-container">
                <canvas id="${chartId}"></canvas>
            </div>
        </div>
    `;
}


// Render articles section
function renderArticles(articles) {
    if (!articles || articles.length === 0) {
        return '<div class="section"><h3>Articles</h3><p>No articles found</p></div>';
    }

    const articlesRows = articles.map((article, index) => `
        <tr>
            <td>${index + 1}</td>
            <td><strong>${escapeHtml(article.headline)}</strong></td>
            <td>${article.summary ? escapeHtml(article.summary) : '-'}</td>
        </tr>
    `).join('');

    return `
        <div class="section">
            <h3>Articles</h3>
            <div class="table-responsive">
                <table class="table table-hover articles-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 35%;">Headline</th>
                            <th>Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${articlesRows}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

// Show error message
function showError(message) {
    $('#error').html(`<strong>Error:</strong> ${message}`).fadeIn();
    setTimeout(() => {
        $('#error').fadeOut();
    }, 5000);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}