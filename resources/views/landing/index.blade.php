@extends('layouts.app')

@section('title', 'Employee Tracking Management System')


@section('content')



<!-- ==================================================
     HERO
================================================== -->

<section
    class="hero"
    id="home">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <span class="hero-badge">
                    Smart Employee Management Platform
                </span>
                <h1>
                    Manage Your
                    <span>Employees</span>
                    Smarter
                </h1>


                <p class="hero-text">

                    Track employee attendance, locations,
                    working hours and performance from one
                    powerful management platform.

                </p>


                <div class="hero-buttons">

                    <a
                        href="/register"
                        class="btn btn-primary-custom">

                        Start Free Trial

                    </a>


                    <a
                        href="#features"
                        class="btn btn-secondary-custom">

                        Explore Features

                    </a>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="hero-dashboard">

                    <div class="dashboard-top"></div>


                    <div class="row">

                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-number">
                                    248
                                </div>

                                <div class="dashboard-label">
                                    Total Employees
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-number">
                                    216
                                </div>

                                <div class="dashboard-label">
                                    Present Today
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-number">
                                    87%
                                </div>

                                <div class="dashboard-label">
                                    Attendance
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-number">
                                    32
                                </div>

                                <div class="dashboard-label">
                                    Active Locations
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- ==================================================
     SERVICES
================================================== -->

<section
    class="section services-section"
    id="services">

    <div class="container">

        <div class="section-header">

            <h2>
                Everything You Need
            </h2>

            <p>
                Powerful tools to manage your workforce,
                attendance and field employees efficiently.
            </p>

        </div>


        <div class="row g-4">


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        📍
                    </div>

                    <h4>
                        Real-Time Location Tracking
                    </h4>

                    <p>
                        Monitor employee locations in real
                        time and understand field activity
                        from a centralized dashboard.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        🕒
                    </div>

                    <h4>
                        Attendance Management
                    </h4>

                    <p>
                        Manage employee check-in, check-out,
                        attendance rules and working hours
                        automatically.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        🛡️
                    </div>

                    <h4>
                        Geofencing
                    </h4>

                    <p>
                        Define business locations and control
                        employee attendance based on approved
                        geographical areas.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        📊
                    </div>

                    <h4>
                        Reports & Analytics
                    </h4>

                    <p>
                        Get useful insights into attendance,
                        working hours, employee activity and
                        workforce performance.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        👥
                    </div>

                    <h4>
                        Employee Management
                    </h4>

                    <p>
                        Organize employees, departments,
                        territories, areas and reporting
                        hierarchies in one place.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">
                        🔔
                    </div>

                    <h4>
                        Smart Notifications
                    </h4>

                    <p>
                        Receive alerts for attendance events,
                        location activity and important
                        employee management actions.
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     FEATURES
================================================== -->

<section
    class="section features-section"
    id="features">

    <div class="container">

        <div class="row align-items-center">


            <div class="col-lg-6">

                <div class="section-header text-start mx-0 mb-4">

                    <h2>
                        Built for Modern Workforce Management
                    </h2>

                    <p>
                        One platform to manage your employees,
                        locations, attendance and organizational
                        hierarchy.
                    </p>

                </div>


                <ul class="feature-list">


                    <li>

                        <div class="feature-check">
                            ✓
                        </div>

                        <div class="feature-content">

                            <h5>
                                Employee Hierarchy
                            </h5>

                            <p>
                                Manage Country, Region, Zone,
                                Territory, Area and employee
                                assignments.
                            </p>

                        </div>

                    </li>


                    <li>

                        <div class="feature-check">
                            ✓
                        </div>

                        <div class="feature-content">

                            <h5>
                                Geofence Based Attendance
                            </h5>

                            <p>
                                Allow employees to check in
                                according to configured business
                                locations.
                            </p>

                        </div>

                    </li>


                    <li>

                        <div class="feature-check">
                            ✓
                        </div>

                        <div class="feature-content">

                            <h5>
                                Centralized Dashboard
                            </h5>

                            <p>
                                Monitor workforce activity from
                                a single administrative dashboard.
                            </p>

                        </div>

                    </li>


                    <li>

                        <div class="feature-check">
                            ✓
                        </div>

                        <div class="feature-content">

                            <h5>
                                Secure Access Control
                            </h5>

                            <p>
                                Manage users and permissions
                                according to organizational roles.
                            </p>

                        </div>

                    </li>


                </ul>

            </div>


            <div class="col-lg-6">

                <div class="feature-image">

                    <div class="dashboard-box">

                        <div class="dashboard-label">
                            Today's Attendance
                        </div>

                        <div class="dashboard-number">
                            87.4%
                        </div>

                    </div>


                    <div class="row">

                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-label">
                                    Checked In
                                </div>

                                <div class="dashboard-number">
                                    216
                                </div>

                            </div>

                        </div>


                        <div class="col-6">

                            <div class="dashboard-box">

                                <div class="dashboard-label">
                                    Absent
                                </div>

                                <div class="dashboard-number">
                                    32
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="dashboard-box">

                        <div class="dashboard-label mb-2">
                            Active Locations
                        </div>

                        <div class="progress">

                            <div
                                class="progress-bar"
                                style="width: 78%;">

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     PRICING
================================================== -->

<section
    class="section pricing-section"
    id="pricing">

    <div class="container">


        <div class="section-header">

            <h2>
                Simple & Transparent Pricing
            </h2>

            <p>
                Choose the plan that fits your organization.
                Upgrade whenever your team grows.
            </p>


            <div class="billing-toggle">

                <button
                    id="monthlyBtn"
                    class="active">

                    Monthly

                </button>


                <button
                    id="yearlyBtn">

                    Yearly
                    <small>
                        Save 20%
                    </small>

                </button>

            </div>

        </div>


        <div class="row g-4">


            <!-- STARTER -->

            <div class="col-lg-4">

                <div class="pricing-card">

                    <div class="plan-name">
                        Starter
                    </div>

                    <div class="plan-description">
                        For small teams getting started.
                    </div>


                    <div class="price">

                        <span class="currency">
                            ৳
                        </span>

                        <span
                            class="amount"
                            data-monthly="999"
                            data-yearly="799">

                            999

                        </span>

                        <span class="period">
                            /month
                        </span>

                    </div>


                    <button
                        class="pricing-btn btn-secondary-custom">

                        Get Started

                    </button>


                    <div class="feature-title">
                        Includes:
                    </div>


                    <ul class="pricing-features">

                        <li>
                            Up to 20 Employees
                        </li>

                        <li>
                            Attendance Management
                        </li>

                        <li>
                            Basic Geofencing
                        </li>

                        <li>
                            Employee Management
                        </li>

                        <li>
                            Basic Reports
                        </li>

                    </ul>

                </div>

            </div>



            <!-- PROFESSIONAL -->

            <div class="col-lg-4">

                <div class="pricing-card popular">

                    <div class="popular-badge">
                        MOST POPULAR
                    </div>


                    <div class="plan-name">
                        Professional
                    </div>

                    <div class="plan-description">
                        For growing organizations.
                    </div>


                    <div class="price">

                        <span class="currency">
                            ৳
                        </span>

                        <span
                            class="amount"
                            data-monthly="2499"
                            data-yearly="1999">

                            2499

                        </span>

                        <span class="period">
                            /month
                        </span>

                    </div>


                    <button
                        class="pricing-btn btn-primary-custom">

                        Start Free Trial

                    </button>


                    <div class="feature-title">
                        Everything in Starter:
                    </div>


                    <ul class="pricing-features">

                        <li>
                            Up to 100 Employees
                        </li>

                        <li>
                            Real-Time Tracking
                        </li>

                        <li>
                            Advanced Geofencing
                        </li>

                        <li>
                            Employee Hierarchy
                        </li>

                        <li>
                            Advanced Reports
                        </li>

                        <li>
                            Notifications
                        </li>

                    </ul>

                </div>

            </div>



            <!-- ENTERPRISE -->

            <div class="col-lg-4">

                <div class="pricing-card">

                    <div class="plan-name">
                        Enterprise
                    </div>

                    <div class="plan-description">
                        For large organizations.
                    </div>


                    <div class="price">

                        <span class="currency">
                            ৳
                        </span>

                        <span
                            class="amount"
                            data-monthly="5999"
                            data-yearly="4799">

                            5999

                        </span>

                        <span class="period">
                            /month
                        </span>

                    </div>


                    <button
                        class="pricing-btn btn-secondary-custom">

                        Contact Sales

                    </button>


                    <div class="feature-title">
                        Everything in Professional:
                    </div>


                    <ul class="pricing-features">

                        <li>
                            Unlimited Employees
                        </li>

                        <li>
                            Advanced Analytics
                        </li>

                        <li>
                            Custom Hierarchy
                        </li>

                        <li>
                            API Integration
                        </li>

                        <li>
                            Dedicated Support
                        </li>

                        <li>
                            Custom Features
                        </li>

                    </ul>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- ==================================================
     CTA
================================================== -->

<section
    class="cta-section"
    id="contact">

    <div class="container">

        <div class="cta-box">

            <h2>
                Ready to Manage Your Workforce Smarter?
            </h2>

            <p>
                Start managing your employees,
                attendance and field operations today.
            </p>

            <a
                href="/register"
                class="btn btn-white">

                Start Free Trial

            </a>

        </div>

    </div>

</section>

@endsection