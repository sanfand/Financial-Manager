<template>
  <div class="container-fluid py-5">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>
    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in recentTransactions" :key="transaction.id">
              <td>{{ transaction.title || 'N/A' }}</td>
              <td>$ {{ formatAmount(transaction.amount) }}</td>
              <td>{{ transaction.type ? transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1) : 'N/A' }}</td>
              <td>{{ transaction.category_name || 'Uncategorized' }}</td>
              <td>{{ transaction.occurred_at ? new Date(transaction.occurred_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A' }}</td>
            </tr>
            <tr v-if="!recentTransactions || recentTransactions.length === 0">
              <td colspan="5" class="text-center">No recent transactions</td>
            </tr>
          </tbody>
        </table>
        <router-link to="/transactions" class="btn btn-primary btn-sm">View All Transactions</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Chart } from 'chart.js/auto';

export default {
  data() {
    return {
      title: 'Financial Dashboard',
      summary: {
        income: 0,
        expense: 0,
        balance: 0
      },
      recentTransactions: [],
      errorMessage: ''
    };
  },
  mounted() {
    this.loadData();
    this.loadChart();
  },
  methods: {
    async loadData() {
    try {
        const response = await axios.get('/api/dashboard', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            withCredentials: true
        });
        const data = response.data;
        console.log('Dashboard response:', data);
        if (data.status === 'success') {
            this.summary = {
                income: parseFloat(data.summary?.income) || 0,
                expense: parseFloat(data.summary?.expense) || 0,
                balance: parseFloat(data.summary?.balance) || 0
            };
            this.recentTransactions = Array.isArray(data.recent_transactions) 
                ? data.recent_transactions.map(t => ({
                    ...t,
                    amount: parseFloat(t.amount) || 0
                }))
                : [];
            console.log('Loaded summary:', this.summary);
            console.log('Loaded recentTransactions:', this.recentTransactions);
        } else {
            this.errorMessage = data.message || 'Failed to load dashboard data';
        }
    } catch (error) {
        console.error('Data load error:', error);
        this.errorMessage = error.response?.data?.message || 'Server error. Please try again later.';
    }
    },

    formatAmount(amount) {
      const num = parseFloat(amount);
      return isNaN(num) ? '0.00' : num.toFixed(2);
    },
    
    async loadChart() {
      try {
        const response = await axios.get('/api/dashboard/get_chart_data', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = response.data.data; // Access nested data
        
        console.log('Chart data:', data);
        if (!data?.labels || !Array.isArray(data?.income) || !Array.isArray(data?.expense)) {
          this.errorMessage = 'Invalid chart data format. Please check server response.';
          console.error('Invalid chart data:', data);
          return;
        }

        new Chart(this.$refs.trendsChart, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [
              {
                label: 'Income',
                data: data.income.map(val => parseFloat(val) || 0),
                borderColor: '#a8d8ea',
                backgroundColor: 'rgba(168, 216, 234, 0.2)',
                fill: true,
                tension: 0.4
              },
              {
                label: 'Expenses',
                data: data.expense.map(val => parseFloat(val) || 0),
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
      } catch (error) {
        console.error('Chart load error:', error);
        this.errorMessage = error.response?.data?.message || 'Failed to load chart data. Please try again later.';
      }
    }
  }
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 400px; /* Fixed height for chart */
  width: 100%;
}
canvas {
  width: 100% !important;
  height: 100% !important;
  max-height: 400px; /* Match chart-container */
}
.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>