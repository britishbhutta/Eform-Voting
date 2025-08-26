<section style="background-color: #eee;">
  <div class="container py-5">

    {{-- Breadcrumb --}}
    <div class="row">
      <div class="col">
        <nav aria-label="breadcrumb" class="bg-body-tertiary rounded-3 p-3 mb-4">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item active" aria-current="page">User Profile</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- ✅ Success Message --}}
    @if (session('status') === 'profile-updated')
      <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> Profile updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- ✅ Error Messages --}}
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Whoops! Something went wrong.</strong>
        <ul class="mb-0 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="row">
      <!-- Profile Left Card -->
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" 
                 alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
            <h5 class="my-3">{{ $user->first_name }} {{ $user->last_name }}</h5>
            <p class="text-muted mb-1">
              {{ $user->role == 2 ? 'Creator' : 'Voter' }}
            </p>
            <p class="text-muted mb-4">{{ $user->email }}</p>
          </div>
        </div>
      </div>

      <!-- Profile Right Card -->
      <div class="col-lg-8">
        <form method="post" action="{{ route('profile.update') }}">
          @csrf
          @method('patch')

          <div class="card mb-4">
            <div class="card-body">

              {{-- First Name --}}
              <div class="row mb-3">
                <label for="first_name" class="col-sm-3 col-form-label">First Name</label>
                <div class="col-sm-9">
                  <input type="text" id="first_name" name="first_name" 
                         class="form-control @error('first_name') is-invalid @enderror"
                         value="{{ old('first_name', $user->first_name) }}" required autofocus>
                  @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <hr>

              {{-- Last Name --}}
              <div class="row mb-3">
                <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>
                <div class="col-sm-9">
                  <input type="text" id="last_name" name="last_name" 
                         class="form-control @error('last_name') is-invalid @enderror"
                         value="{{ old('last_name', $user->last_name) }}" required>
                  @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <hr>

              {{-- Email --}}
              {{-- <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" id="email" name="email" 
                         class="form-control @error('email') is-invalid @enderror"
                         value="{{ old('email', $user->email) }}" readonly>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror

                  @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2 text-warning">
                      Your email address is unverified. 
                      <button form="send-verification" class="btn btn-link p-0">Click here to re-send verification email.</button>
                    </div>
                  @endif
                </div>
              </div>
              <hr> --}}

              {{-- Current Password --}}
              <div class="row mb-3">
                <label for="current_password" class="col-sm-3 col-form-label">Current Password</label>
                <div class="col-sm-9">
                  <input type="password" id="current_password" name="current_password"
                         class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                  @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <hr>

              {{-- New Password --}}
              <div class="row mb-3">
                <label for="password" class="col-sm-3 col-form-label">New Password</label>
                <div class="col-sm-9">
                  <input type="password" id="password" name="password"
                         class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <hr>

              {{-- Confirm Password --}}
              <div class="row mb-3">
                <label for="password_confirmation" class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                  <input type="password" id="password_confirmation" name="password_confirmation"
                         class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                  @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          {{-- Save Button --}}
          <div class="text-end">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
    setTimeout(() => {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach((alert) => {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
</script>