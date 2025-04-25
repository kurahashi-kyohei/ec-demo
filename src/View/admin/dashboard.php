<?php require __DIR__ . '/../layout/admin/header.php'; ?>

<div class="dashboard">
    <h1 class="dashboard__title">ダッシュボード</h1>

    <div class="dashboard__stats">
        <div class="dashboard__stat-card">
            <div>
                <h3>総商品数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalProducts) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/products">商品管理へ</a></button>
        </div>
        <div class="dashboard__stat-card">
            <div>
                <h3>総注文数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalOrders) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/orders">注文管理へ</a></button>
        </div>
        <div class="dashboard__stat-card">
            <div>
                <h3>ユーザー数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalUsers) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/users">ユーザー管理へ</a></button>
        </div>
    </div>
    
    <div class="dashboard__charts">
        <h1 class="dashboard__title">グラフ</h1>
        <div class="dashboard__chart-filters">
            <!-- <select id="sales-period" class="form-select">
                <option value="monthly">月別</option>
                <option value="daily">日別</option>
            </select> -->
            <select id="sales-year" class="form-select">
                <?php for($i = 0; $i <= 2; $i++): ?>
                    <?php $year = date('Y') - $i; ?>
                    <option value="<?= $year ?>"><?= $year ?>年</option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <div class="dashboard__charts">
        <div class="dashboard__chart-card">
            <div class="dashboard__chart-header">
                <h2>売上推移</h2>
            </div>
            <canvas id="salesChart"></canvas>
        </div>

        <div class="dashboard__chart-card">
            <div class="dashboard__chart-header">
                <h2>商品別売上</h2>
            </div>
            <canvas id="productChart"></canvas>
        </div>

        <div class="dashboard__chart-card">
            <div class="dashboard__chart-header">
                <h2>カテゴリー別売上</h2>
            </div>
            <canvas id="categoryChart"></canvas>
        </div>

        <div class="dashboard__chart-card">
            <div class="dashboard__chart-header">
                <h2>ユーザー別売上</h2>
            </div>
            <canvas id="userChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/ja.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

<script>
let salesChart = null;
let productChart = null;
let categoryChart = null;

document.addEventListener('DOMContentLoaded', function() {
    moment.locale('ja');

    Chart.defaults.font.family = "'Noto Sans JP', sans-serif";
    Chart.defaults.font.size = 14;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    Chart.defaults.plugins.tooltip.titleFont.size = 14;
    Chart.defaults.plugins.tooltip.bodyFont.size = 13;

    loadDashboardData();

    // document.getElementById('sales-period').addEventListener('change', loadDashboardData);
    document.getElementById('sales-year').addEventListener('change', loadDashboardData);
});

async function loadDashboardData() {
    try {
        const year = document.getElementById('sales-year').value;
        
        console.log('Fetching data with params:', {year });
        
        const response = await fetch(`/api/admin/dashboard/stats?year=${year}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        console.log('Received data:', data);

        if (data.error) {
            throw new Error(data.error);
        }

        renderSalesChart(data.sales);
        renderProductChart(data.products);
        renderCategoryChart(data.categories);
        renderUserChart(data.users);
    } catch (error) {
        console.error('データの取得に失敗しました:', error);
        alert('データの取得に失敗しました。ページをリロードしてください。');
    }
}

function renderSalesChart(salesData) {
    try {
        console.log('Rendering sales chart with data:', salesData);
        const ctx = document.getElementById('salesChart');
        if (!ctx) {
            console.error('Sales chart canvas not found');
            return;
        }

        const labels = salesData.map(d => moment(d.date).format('YYYY年M月'));
        const values = salesData.map(d => d.total_amount);

        if (salesChart instanceof Chart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '売上金額',
                    data: values,
                    borderColor: 'var(--color-primary)',
                    backgroundColor: 'rgba(76, 81, 191, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `¥${context.parsed.y.toLocaleString()}`
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `¥${value.toLocaleString()}`
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error rendering sales chart:', error);
    }
}

function renderProductChart(productData) {
    try {
        const ctx = document.getElementById('productChart');
        if (!ctx) {
            console.error('Product chart canvas not found');
            return;
        }

        const labels = productData.map(d => d.name);
        const values = productData.map(d => d.total_amount);

        if (productChart instanceof Chart) {
            productChart.destroy();
        }

        productChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '売上金額',
                    data: values,
                    backgroundColor: ['rgba(76, 81, 191, 0.8)', 'rgba(104, 109, 224, 0.8)', 'rgba(132, 137, 255, 0.8)', 'rgba(156, 160, 255, 0.8)', 'rgba(180, 183, 255, 0.8)'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `¥${context.parsed.x.toLocaleString()}`
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `¥${value.toLocaleString()}`
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error rendering product chart:', error);
    }
}

function renderCategoryChart(categoryData) {
    try {
        console.log('Rendering category chart with data:', categoryData);
        const ctx = document.getElementById('categoryChart');
        if (!ctx) {
            console.error('Category chart canvas not found');
            return;
        }

        const labels = categoryData.map(d => d.category_name || '未分類');
        const values = categoryData.map(d => d.total_amount);

        if (categoryChart instanceof Chart) {
            categoryChart.destroy();
        }

        categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(76, 81, 191, 0.8)',
                        'rgba(104, 109, 224, 0.8)',
                        'rgba(132, 137, 255, 0.8)',
                        'rgba(156, 160, 255, 0.8)',
                        'rgba(180, 183, 255, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `¥${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error rendering category chart:', error);
    }
}

function renderUserChart(userData) {
    try {
        console.log('Rendering user chart with data:', userData);
        const ctx = document.getElementById('userChart');
        if (!ctx) {
            console.error('User chart canvas not found');
            return;
        }

        const labels = userData.map(d => d.first_name + ' ' + d.last_name);
        const values = userData.map(d => d.total_amount);

        if (userChart instanceof Chart) {
            userChart.destroy();
        }

        userChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '売上金額',
                    data: values,
                    backgroundColor: ['rgba(76, 81, 191, 0.8)', 'rgba(104, 109, 224, 0.8)', 'rgba(132, 137, 255, 0.8)', 'rgba(156, 160, 255, 0.8)', 'rgba(180, 183, 255, 0.8)'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `¥${context.parsed.x.toLocaleString()}`
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `¥${value.toLocaleString()}`
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error rendering user chart:', error);
    }
}

</script>