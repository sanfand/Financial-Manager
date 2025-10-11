<template>
  <div class="container-fluid py-5">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''"></button>
    </div>

    <div class="row">
      <div class="col-md-4" v-for="(value, key) in summary" :key="key">
        <div
          class="card shadow mb-4"
          :style="{ backgroundColor: key === 'income' ? '#b5ead7' : key === 'expense' ? '#f7c5cc' : '#a8d8ea' }"
        >
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">
              {{ key === 'income' ? 'Total Income' : key === 'expense' ? 'Total Expenses' : 'Balance' }}
            </h6>
          </div>
          <div class="card-body">
            <h5 class="card-title">$ {{ formatAmount(value) }}</h5>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-dark">Monthly Trends</h6>
      </div>
      <div class="card-body chart-container">
        <canvas ref="trendsChart"></canvas>
      </div>
    </div>

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
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in recentTransactions" :key="t.id">
              <td>{{ t.title }}</td>
              <td>$ {{ formatAmount(t.amount) }}</td>
              <td>
                <span
                  :class="{
                    badge: true,
                    'bg-success': t.type === 'income',
                    'bg-danger': t.type === 'expense'
                  }"
                >
                  {{ t.type }}
                </span>
              </td>
              <td>{{ t.category_name || 'None' }}</td>
              <td>{{ formatDate(t.occurred_at) }}</td>
              <td>{{ t.notes || '-' }}</td>
            </tr>
            <tr v-if="recentTransactions.length === 0">
              <td colspan="6" class="text-center">No recent transactions</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import api, { clearToken } from '../utils/auth';
import Chart from 'chart.js/auto';

export default {
  data() {
    return {
      title: 'Dashboard',
      summary: { income: 0, expense: 0, balance: 0 },
      recentTransactions: [],
      errorMessage: '',
      chart: null,
    };
  },
  async mounted() {
    await this.loadDashboard();
    await this.loadChart();
  },
  methods: {
    async loadDashboard() {
      try {
        const res = await api.get('dashboard');
        console.log('Dashboard response:', res.data);

        if (res.data.status === 'success') {
          this.summary = res.data.summary || this.summary;
          this.recentTransactions = res.data.recent_transactions || []; // FIX: changed from 'recent' to 'recent_transactions'
        } else {
          this.errorMessage = 'Unexpected dashboard response.';
        }
      } catch (e) {
        console.error('Load dashboard error:', e);
        this.errorMessage = e.response?.data?.message || 'Failed to load dashboard.';
        if (e.response?.status === 401) {
          clearToken();
          this.$router.push('/login');
        }
      }
    },

    async loadChart() {
      try {
        const res = await api.get('dashboard/get_chart_data');
        console.log('Chart data response:', res.data);

        const data = res.data.data;
        if (!data?.labels) throw new Error('Invalid chart data');

        if (this.chart) this.chart.destroy();

        this.chart = new Chart(this.$refs.trendsChart, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [
              {
                label: 'Income',
                data: data.income,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4,
              },
              {
                label: 'Expenses',
                data: data.expense,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'top' },
              title: {
                display: true,
                text: 'Income vs Expenses (Last 6 Months)',
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: (v) => '$' + v,
                },
              },
            },
          },
        });
      } catch (e) {
        console.error('Chart load error:', e);
        this.errorMessage = e.response?.data?.message || 'Failed to load chart data.';
      }
    },

    formatAmount(a) {
      const num = parseFloat(a);
      return isNaN(num) ? '0.00' : num.toFixed(2);
    },
    formatDate(d) {
      if (!d) return '-';
      return new Date(d).toLocaleDateString();
    },
  },
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 400px;
}
.badge {
  font-size: 0.75em;
  padding: 0.35em 0.65em;
}


.badge {
  border-radius: 8px;
  padding: 6px 12px;
  font-weight: 600;
  font-size: 0.75rem;
}

.bg-success {
  background: linear-gradient(135deg, #5e8f48, #4a7e6b) !important;
}

.bg-danger {
  background: linear-gradient(135deg, #642020, #693333) !important;
}
</style>
