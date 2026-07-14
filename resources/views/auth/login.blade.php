<x-guest-layout>
    <main class="form-signin">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <img class="mb-4" src="/assets/images/LogoFullTransparent.png" alt="" width="300">

            <div class="form-floating">
            <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com">
            <label for="floatingInput">Email address</label>
            </div>
            <div class="form-floating">
            <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password">
            <label for="floatingPassword">Password</label>
            </div>

            <div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me"> Remember me
            </label>
            </div>
            <button class="w-100 btn btn-lg btn-primary">Log in</button>
        </form>
    </main>
</x-guest-layout>
