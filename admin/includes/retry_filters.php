<form method="GET" class="mb-3">
  <div class="row">
    <div class="col-md-3">
      <select name="status" class="form-control">
        <option value="">-- Filter by Status --</option>
        <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
        <option value="Error" <?= $statusFilter === 'Error' ? 'selected' : '' ?>>Error</option>
        <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="checkout" class="form-control" placeholder="Search by Checkout ID" value="<?= htmlspecialchars($checkoutFilter) ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-info">🔍 Apply Filters</button>
    </div>
  </div>
</form>
