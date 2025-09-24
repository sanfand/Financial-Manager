<div class="container-fluid">
    <h1 class="h2 mb-4 text-gray-800"><?= $title; ?></h1>
    
    <!-- Financial Summary -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4" style="background-color: #b5ead7;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Total Income</h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$<?= number_format($summary['income'], 2); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4" style="background-color: #f7c5cc;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Total Expenses</h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$<?= number_format($summary['expense'], 2); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow mb-4" style="background-color: #a8d8ea;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Balance</h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$<?= number_format($summary['balance'], 2); ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Monthly Trends</h6>
        </div>
        <div class="card-body" style="min-height:400px;">
            <canvas id="trendsChart" style="height:400px; width:100%;"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Recent Transactions</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction->title); ?></td>
                            <td>$<?= number_format($transaction->amount, 2); ?></td>
                            <td><?= ucfirst($transaction->type); ?></td>
                            <td><?= htmlspecialchars($transaction->category_name ?: 'Uncategorized'); ?></td>
                            <td><?= date('M d, Y', strtotime($transaction->occurred_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="<?= base_url('transactions'); ?>" class="btn btn-primary btn-sm">View All Transactions</a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= base_url('dashboard/get_chart_data'); ?>')
        .then(response => response.json())
        .then(data => {
            console.log('Chart Data:', data);

            if (!data || !data.labels || !data.income || !data.expense) {
                console.error('Invalid chart data format:', data);
                return;
            }

            const ctx = document.getElementById('trendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: data.income,
                            borderColor: '#a8d8ea',
                            backgroundColor: 'rgba(168, 216, 234, 0.2)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Expenses',
                            data: data.expense,
                            borderColor: '#f7c5cc',
                            backgroundColor: 'rgba(247, 197, 204, 0.2)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Chart Error:', error);
        });
});
</script>
