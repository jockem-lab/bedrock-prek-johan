@extends('layouts.app')

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Kontakta oss</h1>
  </div>
</div>

<section class="page-sektion">
  <div class="page-inner">
    <div class="kontakt-grid">
      <div class="kontakt-info">
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Adress</p>
          <p>Storgatan 1<br>582 24 Linköping</p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Telefon</p>
          <p><a href="tel:+4613123456">013-12 34 56</a></p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">E-post</p>
          <p><a href="mailto:info@prek.se">info@prek.se</a></p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Öppettider</p>
          <p>Måndag–Fredag: 09–17<br>Lördag: 10–14<br>Söndag: Stängt</p>
        </div>
      </div>

      <div class="kontakt-formular">
        <h2>Skicka ett meddelande</h2>
        <div class="kontakt-form">
          <div class="form-group">
            <label for="namn">Namn</label>
            <input type="text" id="namn" placeholder="Ditt namn">
          </div>
          <div class="form-group">
            <label for="email">E-post</label>
            <input type="email" id="email" placeholder="din@epost.se">
          </div>
          <div class="form-group">
            <label for="telefon">Telefon</label>
            <input type="tel" id="telefon" placeholder="070-123 45 67">
          </div>
          <div class="form-group">
            <label for="meddelande">Meddelande</label>
            <textarea id="meddelande" rows="5" placeholder="Ditt meddelande..."></textarea>
          </div>
          <button type="button" class="btn-primary">Skicka meddelande</button>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
