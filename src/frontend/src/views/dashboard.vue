<template>
  <div class="container-fluid py-5">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>
    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''"></button>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="card shadow mb-4" style="background-color: #b5ead7;">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Total Income</h6>
          </div>
          <div class="card-body">
            <h5 class="card-title">$ {{ formatAmount(summary.income) }}</h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow mb-4" style="background-color: #f7c5cc;">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Total Expenses</h6>
          </div>
          <div class="card-body">
            <h5 class="card-title">$ {{ formatAmount(summary.expense) }}</h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow mb-4" style="background-color: #a8d8ea;">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Balance</h6>
          </div>
          <div class="card-body">
            <h5 class="card-title">$ {{ formatAmount(summary.balance) }}</h5>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-dark">Monthly Trends</h6>
      </div>
      <div class="card-body chart-container">
        <canvas ref="trendsChart" style="width: 100%; height: 100%;"></canvas>
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
            <tr v-for="transaction in recentTransactions" :key="transaction.id">
              <td>{{ transaction.title }}</td>
              <td>$ {{ formatAmount(transaction.amount) }}</td>
              <td>
                <span :class="{'badge bg-success': transaction.type === 'income', 'badge bg-danger': transaction.type === 'expense'}">
                  {{ transaction.type }}
                </span>
              </td>
              <td>{{ transaction.category_name || 'None' }}</td>
              <td>{{ formatDate(transaction.occurred_at) }}</td>
              <td>{{ transaction.notes || '-' }}</td>
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
import axios from '../utils/auth';
import { clearToken } from '../utils/auth';
import Chart from 'chart.js/auto';

export default {
  data() {
    return {
      title: 'Dashboard',
      summary: { income: 0, expense: 0, balance: 0 },
      recentTransactions: [],
      errorMessage: '',
      chart: null
    };
  },
  mounted() {
    this.loadDashboard();
  },
  methods: {
    async loadDashboard() {
  try {
    console.log('Loading dashboard with token:', getToken());
    const response = await instance.get('/dashboard');
    console.log('Dashboard response:', response.data);
    this.userData = response.data.user;
  } catch (e) {
    console.error('Load dashboard error:', e);
    if (e.response?.status === 401) {
      clearToken();
      this.$router.push('/login');
    }
  }
  },
    async loadChart() {
      try {
        const response = await axios.get('/dashboard/get_chart_data');
        const data = response.data;
        console.log('Chart data response:', data);
        
        if (data.status === 'success' && data.data) {
          const chartData = data.data;
          
          // Destroy existing chart if it exists
          if (this.chart) {
            this.chart.destroy();
          }

          // Create new chart
          this.chart = new Chart(this.$refs.trendsChart, {
            type: 'line',
            data: {
              labels: chartData.labels,
              datasets: [
                {
                  label: 'Income',
                  data: chartData.income,
                  borderColor: '#28a745',
                  backgroundColor: 'rgba(40, 167, 69, 0.1)',
                  fill: true,
                  tension: 0.4
                },
                {
                  label: 'Expenses',
                  data: chartData.expense,
                  borderColor: '#dc3545',
                  backgroundColor: 'rgba(220, 53, 69, 0.1)',
                  fill: true,
                  tension: 0.4
                }
              ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { 
                  position: 'top',
                  labels: {
                    usePointStyle: true,
                  }
                },
                title: {
                  display: true,
                  text: 'Income vs Expenses (Last 7 Months)'
                }
              },
              scales: {
                y: { 
                  beginAtZero: true,
                  ticks: {
                    callback: function(value) {
                      return '$' + value;
                    }
                  }
                }
              }
            }
          });
        } else {
          this.errorMessage = 'Invalid chart data format';
          console.error('Invalid chart data:', data);
        }
      } catch (error) {
        console.error('Chart load error:', error);
        this.errorMessage = error.response?.data?.message || 'Failed to load chart data. Please try again later.';
        if (error.response?.status === 401 || error.response?.status === 403) clearToken();
      }
    },
    formatAmount(amount) {
      const num = parseFloat(amount);
      return isNaN(num) ? '0.00' : num.toFixed(2);
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      return new Date(dateString).toLocaleDateString();
    }
  }
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
}
canvas {
  width: 100% !important;
  height: 100% !important;
  max-height: 400px;
}
.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.badge {
  font-size: 0.75em;
  padding: 0.35em 0.65em;
}
</style>