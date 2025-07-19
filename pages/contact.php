<?php
session_start();
require_once '../templates/header.php';
?>

<main class="flex-grow-1 container my-2 mb-5">
  <h2 class="text-center mb-2">Une question ? Un message ? On est là pour vous! 🌱</h2>
<form action="../config/traitement_contact.php" method="POST" class="row g-2">
    <div class="col-12 d-flex justify-content-center">
      <div class="input-wrapper">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-center">
      <div class="input-wrapper">
        <label for="sujet" class="form-label">Sujet</label>
        <input type="text" class="form-control" id="sujet" name="sujet" required>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-center">
      <div class="input-wrapper message">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
      </div>
    </div>

    <div class="col-12 text-center">
      <button type="submit" class="btn custom-btn px-4">Envoyer</button>
    </div>
  </form>
</main>



<?php
require_once '../templates/footer.php';
?>