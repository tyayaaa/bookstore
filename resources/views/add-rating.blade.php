<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Add Book Rating</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-5">
    <h1 class="text-center mb-4">Add Book Rating</h1>

    <div class="mb-3">
      <a href="/" class="btn btn-dark btn-sm me-2">List of Books</a>
      <a href="/top-author" class="btn btn-success btn-sm me-2">Top Author</a>
      <a href="/add-rating" class="btn btn-warning btn-sm text-white">Add Rating</a>
    </div>

    <form id="rating-form" class="card p-4 shadow-sm">
      <div class="mb-3">
        <label for="author_id" class="form-label">Select Author</label>
        <select id="author_id" class="form-select" required>
          <option value="">-- Select Author --</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="book_id" class="form-label">Select Book</label>
        <select id="book_id" class="form-select" required>
          <option value="">-- Select Book --</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="rating" class="form-label">Rating</label>
        <select id="rating" class="form-select" required>
          <option value="">-- Select Rating --</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
      <div id="rating-message" class="mt-3"></div>
    </form>
  </div>

  <script>
    async function loadAuthors() {
      const authorSelect = document.getElementById('author_id');
      authorSelect.innerHTML = `<option>Loading...</option>`;

      try {
        const res = await fetch('/api/authors');
        const data = await res.json();
        const authors = data.data ?? [];

        authorSelect.innerHTML = `<option value="">-- Select Author --</option>`;
        authors.forEach(a => {
          authorSelect.insertAdjacentHTML('beforeend', `<option value="${a.id}">${a.name}</option>`);
        });
      } catch {
        authorSelect.innerHTML = `<option>Failed retrieving author</option>`;
      }
    }

    async function loadBooksByAuthor(authorId) {
      const bookSelect = document.getElementById('book_id');
      bookSelect.innerHTML = `<option>Loading...</option>`;

      try {
        const res = await fetch(`/api/book?author_id=${authorId}`);
        const data = await res.json();
        const books = data.data?.books ?? [];

        bookSelect.innerHTML = `<option value="">-- Select Book  --</option>`;
        books.forEach(b => {
          bookSelect.insertAdjacentHTML('beforeend', `<option value="${b.id}">${b.title}</option>`);
        });
      } catch {
        bookSelect.innerHTML = `<option>Failed retrieving book</option>`;
      }
    }

    function loadRatings() {
      const ratingSelect = document.getElementById('rating');
      for (let i = 1; i <= 10; i++) {
        ratingSelect.insertAdjacentHTML('beforeend', `<option value="${i}">${i}</option>`);
      }
    }

    async function getUserId() {
      let userId = localStorage.getItem('user_id');
      if (!userId) {
        const res = await fetch('/api/auto-register', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
          userId = data.user_id;
          localStorage.setItem('user_id', userId);
        } else {
          throw new Error('Gagal membuat user anonim');
        }
      }
      return userId;
    }

    document.getElementById('rating-form').addEventListener('submit', async e => {
      e.preventDefault();
      const book_id = document.getElementById('book_id').value;
      const author_id = document.getElementById('author_id').value;
      const rating = document.getElementById('rating').value;
      const msg = document.getElementById('rating-message');

      msg.innerHTML = `<div class="text-muted">Sending rating...</div>`;

      try {
        const user_id = await getUserId(); 

        const res = await fetch('/api/rating', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ book_id, author_id, rating, user_id })
        });

        const data = await res.json();

        if (res.ok && data.success) {
          msg.innerHTML = `<div class="alert alert-success"> ${data.message ?? 'Rating berhasil dikirim!'}</div>`;
          setTimeout(() => window.location.href = '/', 1500);
        } else {
          msg.innerHTML = `<div class="alert alert-danger"> ${data.message ?? 'Gagal menambahkan rating'}</div>`;
        }
      } catch (error) {
        msg.innerHTML = `<div class="alert alert-danger"> Terjadi kesalahan: ${error.message}</div>`;
      }
    });

    document.addEventListener('DOMContentLoaded', () => {
      loadAuthors();
      loadRatings();

      document.getElementById('author_id').addEventListener('change', e => {
        const authorId = e.target.value;
        if (authorId) loadBooksByAuthor(authorId);
      });
    });
  </script>
</body>

</html>