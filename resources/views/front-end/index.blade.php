<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EFORM.VOTE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}"> -->
  <link rel="stylesheet" href="{{ asset('front-end/css/header.css') }}">
  <link rel="stylesheet" href="{{ asset('front-end/css/body.css') }}">
  <link rel="stylesheet" href="{{ asset('front-end/css/footer.css') }}">
</head>

<body>

  <!-- Header Section -->
  <nav class="navbar navbar-expand-lg py-5">
    <div class="container-fluid justify-content-center">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-light" href="#">HOME</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">VISION</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">BOOK</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">JOB</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">CONTACT</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">RADIO,TV,EVENT</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">TENDER</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">BLOG</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="{{ route('join') }}">LOG</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">INVESTORS</a></li>
      </ul>
    </div>
  </nav>

  <!-- Body Section -->
  <section class="main-section d-flex flex-column justify-content-center align-items-center text-center">
  <img src="{{ asset('/front-end/images/LOGO-GIF-FV.gif') }}" alt="EFORM.VOTE Logo" class="main-logo">
    <h3 class="main-subtitle">YOUR VOTING FORM</h3>
    <a href="{{ route('join') }}" class="btn btn-login mt-3 px-4 py-3">LOGIN/REGISTRATION</a>
  </section>

  <!-- Footer -->
  <footer class="footer bg-white text-center py-5">
    <div class="container">
      <a href="#" class="footer-link">terms of conditions</a>
      <a href="#" class="footer-link">terms of use</a>
      <a href="#" class="footer-link">privacy policy</a>
      <a href="#" class="footer-link">registration radio,tv,event</a>
      <a href="#" class="footer-link">sale of email databaze</a>
    </div>
  </footer>

</body>
</html>
