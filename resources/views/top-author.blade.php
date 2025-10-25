<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Top Author</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-5">
    <h1 class="text-center mb-4">Top Author</h1>

    <div class="mb-3">
      <a href="/" class="btn btn-dark btn-sm me-2">List of Books</a>
      <a href="/top-author" class="btn btn-success btn-sm me-2">Top Author</a>
      <a href="/add-rating" class="btn btn-warning btn-sm text-white">Add Rating</a>
    </div>

    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Author ID</th>
          <th>Author Name</th>
          <th>Total Voters</th>
          <th>Average Rating</th>
        </tr>
      </thead>
      <tbody id="author-list">
        <tr>
          <td colspan="6" class="text-center text-muted">Retrieving data...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    async function loadTopAuthors() {
    const tbody = document.getElementById('author-list');
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Memuat data author...</td></tr>`;

      try {
        const res = await fetch('/api/authors/top');
        const data = await res.json();
        const authors = data.data ?? [];

        tbody.innerHTML = '';
        if (authors.length) {
          authors.forEach(a => {
            tbody.insertAdjacentHTML('beforeend', `
              <tr>
                <td>${a.author_id}</td>
                <td>${a.author_name}</td>
                <td>${a.voter_count}</td>
                <td>${a.avg_rating}</td>
              </tr>`);
          });
        } else {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Tidak ada data author</td></tr>`;
        }
      } catch {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal memuat data author</td></tr>`;
      }
    }

    document.addEventListener('DOMContentLoaded', loadTopAuthors);
  </script>
</body>

</html>