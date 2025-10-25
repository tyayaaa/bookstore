<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List of Books</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-5">
    <h1 class="text-center mb-4">List of Books</h1>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
      <div class="mb-2">
        <a href="/" class="btn btn-dark btn-sm me-2">List of Books</a>
        <a href="/top-author" class="btn btn-success btn-sm me-2">Top Author</a>
        <a href="/add-rating" class="btn btn-warning btn-sm text-white">Add Rating</a>
      </div>

      <div class="mb-2">
        <input type="text" id="search-input" class="form-control form-control-sm d-inline-block w-auto"
          placeholder="Search Title / Author / ISBN / Publisher">
        <button id="search-btn" class="btn btn-sm btn-primary ms-1">Search</button>
      </div>

      <div class="mb-2">
        <label for="per-page" class="form-label me-2 mb-0 fw-bold">View:</label>
        <select id="per-page" class="form-select form-select-sm d-inline-block w-auto">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="ms-1">data / page</span>
      </div>
    </div>

    <div class="text-end mb-2">
      <span id="page-info" class="text-muted small"></span>
    </div>

    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Author</th>
          <th>category</th>
          <th>Rating</th>
          <th>Total Voters</th>
          <th>Trending</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="book-list">
        <tr>
          <td colspan="8" class="text-center text-muted">Retrieving data...</td>
        </tr>
      </tbody>
    </table>

    <div class="d-flex justify-content-between mt-3">
      <button id="prev-btn-bottom" class="btn btn-secondary btn-sm" disabled>Previous</button>
      <button id="next-btn-bottom" class="btn btn-primary btn-sm">Next</button>
    </div>
  </div>

  <script>
    let currentPage = 1;
    let perPage = 10;
    let searchQuery = '';

    async function loadBooks(page = 1) {
      const tableBody = document.getElementById('book-list');
      const prevBtnBottom = document.getElementById('prev-btn-bottom');
      const nextBtnBottom = document.getElementById('next-btn-bottom');
      const pageInfo = document.getElementById('page-info');

      tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">Retrieving data...</td></tr>`;

      try {
        const url = `/api/book?sort=rating&order=desc&page=${page}&per_page=${perPage}&search=${encodeURIComponent(searchQuery)}`;
        const res = await fetch(url);
        const result = await res.json();
        const books = result.data?.books ?? [];
        const current = result.data?.current_page ?? page;
        const last = result.data?.last_page ?? 1;
        const total = result.data?.total ?? 0;

        tableBody.innerHTML = '';
        if (books.length) {
          books.forEach(book => {
            tableBody.insertAdjacentHTML('beforeend', `
              <tr>
                <td>${book.id}</td>
                <td>${book.title}</td>
                <td>${book.author}</td>
                <td>${book.category}</td>
                <td>${book.average_rating}</td>
                <td>${book.total_voters}</td>
                <td>${book.trending}</td>
                <td>${book.availability_status}</td>
              </tr>
            `);
          });
        } else {
          tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No book found</td></tr>`;
        }

        currentPage = current;
        pageInfo.textContent = `Halaman ${current} dari ${last} (Total ${total} buku)`;
        prevBtnBottom.disabled = current === 1;
        nextBtnBottom.disabled = current >= last;

      } catch (error) {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed retrieving data</td></tr>`;
      }
    }

  document.addEventListener('DOMContentLoaded', () => loadBooks());
  document.getElementById('prev-btn-bottom').addEventListener('click', () => loadBooks(currentPage - 1));
  document.getElementById('next-btn-bottom').addEventListener('click', () => loadBooks(currentPage + 1));

  document.getElementById('per-page').addEventListener('change', (e) => {
    perPage = e.target.value;
    currentPage = 1;
    loadBooks(currentPage);
  });

  document.getElementById('search-btn').addEventListener('click', () => {
    searchQuery = document.getElementById('search-input').value.trim();
    currentPage = 1;
    loadBooks(currentPage);
  });

  document.getElementById('search-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      searchQuery = e.target.value.trim();
      currentPage = 1;
      loadBooks(currentPage);
    }
  });
  </script>
</body>

</html>