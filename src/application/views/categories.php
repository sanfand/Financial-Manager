<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5" id="dashboardApp">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>
    <!--  Pie Chart of Expenses by Category -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Expenses by Category</h6>
        </div>
        <div class="card-body">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</div>

<script>
const dashboardApp = Vue.createApp({
    data() {
        return {
            title: '<?php echo $title; ?>',
            summary: <?php echo json_encode($summary); ?>,
            recent_transactions: <?php echo json_encode($recent_transactions); ?>,
            categories: <?php echo json_encode($categories); ?>,
        }
    },
    mounted() {
        this.renderChart();
    },
    methods: {
        renderChart() {
            const ctx = document.getElementById('expenseChart').getContext('2d');
            const labels = this.categories.map(c => c.name);
            const data = this.categories.map(c => {
                const total = this.recent_transactions
                    .filter(t => t.category_id == c.id && t.type === 'expense')
                    .reduce((sum, t) => sum + parseFloat(t.amount), 0);
                return total;
            });
            new Chart(ctx, {
                type: 'pie',
                data: { labels: labels, datasets: [{ data: data, backgroundColor: ['#a8d8ea','#f78fb3','#7bed9f','#ee9bb7','#d8b9ff','#80c1e0','#ffe8a1','#f0a1b0'] }] }
            });
        }
    }
});
dashboardApp.mount('#dashboardApp');
</script>

<?php $this->load->view('footer'); ?>
