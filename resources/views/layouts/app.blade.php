
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Employee Tracking Management System')
    </title>


    {{-- Bootstrap CSS --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/bootstrap.min.css') }}"
    >


    {{-- Custom CSS --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/landing.css') }}"
    >

</head>


<body>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>TrackFlow - Employee Tracking Management</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
<body>

<!-- ==================================================
     HEADER
================================================== -->

<nav class="navbar navbar-expand-lg sticky-top">

    <div class="container">

        <a class="navbar-brand" href="#">
            Track<span>Flow</span>
        </a>
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div
            class="collapse navbar-collapse"
            id="navbarMenu">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link" href="#home">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#services">
                        Services
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#features">
                        Features
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#pricing">
                        Pricing
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#contact">
                        Contact
                    </a>
                </li>

            </ul>
            <div>

                <a
                    href="/login"
                    class="btn-login text-decoration-none">

                    Login

                </a>

                <a
                    href="{{asset('/company/create')}}"
                    class="btn btn-header">

                    Get Started

                </a>

            </div>

        </div>

    </div>

</nav>


    {{-- Page Content --}}
    <main>

        @yield('content')

    </main>


<!-- ==================================================
     FOOTER
================================================== -->

<footer>

    <div class="container">

        <div class="row g-5">


            <div class="col-lg-5">

                <div class="footer-brand">

                    Track<span>Flow</span>

                </div>

                <p class="footer-description">

                    A modern employee tracking and workforce
                    management platform designed to help
                    organizations manage employees,
                    attendance and field operations.

                </p>

            </div>


            <div class="col-lg-2 col-md-4">

                <div class="footer-title">
                    Product
                </div>

                <ul class="footer-links">

                    <li>
                        <a href="#features">
                            Features
                        </a>
                    </li>

                    <li>
                        <a href="#pricing">
                            Pricing
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Mobile App
                        </a>
                    </li>

                </ul>

            </div>


            <div class="col-lg-2 col-md-4">

                <div class="footer-title">
                    Company
                </div>

                <ul class="footer-links">

                    <li>
                        <a href="#">
                            About
                        </a>
                    </li>

                    <li>
                        <a href="#services">
                            Services
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Contact
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Careers
                        </a>
                    </li>

                </ul>

            </div>


            <div class="col-lg-3 col-md-4">

                <div class="footer-title">
                    Support
                </div>

                <ul class="footer-links">

                    <li>
                        <a href="#">
                            Help Center
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Privacy Policy
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Terms & Conditions
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            Documentation
                        </a>
                    </li>

                </ul>

            </div>


        </div>


        <div class="footer-bottom">

            <div class="row">

                <div class="col-md-6">

                    © 2026 TrackFlow.
                    All rights reserved.

                </div>


                <div class="col-md-6 text-md-end">

                    Employee Tracking Management System

                </div>

            </div>

        </div>

    </div>

</footer>
<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
<script src="{{ asset('assets/js/landing.js') }}"></script>

</body>
</html>
