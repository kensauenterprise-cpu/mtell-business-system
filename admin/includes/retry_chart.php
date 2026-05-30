<?php if (isset($chartData)): ?>
  <!-- 📈 Retry Chart -->
  <canvas id="retryChart" height="100"></canvas>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  const ctx = document.getElementById('retryChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Success', 'Error', 'Pending'],
      datasets: [{
        label: 'Retry Counts',
        data: [<?= $chartData['Success'] ?>, <?= $chartData['Error'] ?>, <?= $chartData['Pending'] ?>],
        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: 'STK Retry Trends' }
      }
    }
  });
  </script>
<?php endif; ?>
