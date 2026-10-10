@extends('layouts.public')
@section('title', 'Home')

@section('content')
{{-- Hero --}}
<section class="hero">
    <p class="where">Brgy. Banlic, City of Cabuyao, Laguna Philippines 4025</p>
    <h1>Community Complaint and Incident Report System</h1>
    <p class="tag">Speak Up. Report It. Make a Difference.</p>
    <div class="actions">
        @auth
            <a class="btn btn-glass" href="{{ route('dashboard') }}">Go to dashboard &rarr;</a>
        @else
            <a class="btn btn-glass" href="{{ route('login') }}">Log in &rarr;</a>
            <a class="btn btn-glass" href="{{ route('register') }}">Sign up</a>
        @endauth
        <a class="btn btn-glass" href="{{ route('track') }}">Track a report</a>
    </div>
</section>

{{-- Quote --}}
<section class="section">
    <div class="container quote">
        <h2>&ldquo;Your Voice Can Help Improve Our Community&rdquo;</h2>
        <p>Our system provides residents with a convenient way to report community concerns and incidents. By sharing information with the appropriate community authorities, issues can be documented, monitored, and addressed more efficiently.</p>
    </div>
</section>

{{-- How it works --}}
<section class="section how">
    <div class="container">
        <h2>HOW IT WORKS</h2>
        <div class="how-steps">
            <div class="how-step"><div class="ico"><x-icon name="file-plus" /></div><h3>REPORT</h3><p>Fill out the form and attach photos.</p></div>
            <span class="how-arrow" aria-hidden="true">&rarr;</span>
            <div class="how-step"><div class="ico"><x-icon name="search" /></div><h3>REVIEW</h3><p>Barangay staff review and assign your case.</p></div>
            <span class="how-arrow" aria-hidden="true">&rarr;</span>
            <div class="how-step"><div class="ico"><x-icon name="map-pin" /></div><h3>TRACK</h3><p>Follow progress with your reference number.</p></div>
            <span class="how-arrow" aria-hidden="true">&rarr;</span>
            <div class="how-step"><div class="ico"><x-icon name="check-circle" /></div><h3>RESOLVE</h3><p>Get notified once action has been taken.</p></div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="section glance">
    <div class="container">
        <h2>Our Community at a Glance</h2>
        <div class="glance-grid">
            <div class="glass"><small>Reports Submitted</small><b>{{ number_format($stats['submitted']) }}</b></div>
            <div class="glass"><small>Reports Resolved</small><b>{{ number_format($stats['resolved']) }}</b></div>
            <div class="glass"><small>Reports Reviewed</small><b>{{ $stats['reviewed'] }}%</b></div>
        </div>
    </div>
</section>

{{-- FAQs --}}
<section class="section faq" id="faqs">
    <h2>Frequently Asked Questions</h2>
    <details class="glass"><summary>Who can file a report?</summary><p>Any resident of the barangay with a registered account. Registration is free and only takes a minute.</p></details>
    <details class="glass"><summary>How do I know if my report is being handled?</summary><p>Every report gets a reference number (like BRGY-{{ date('Y') }}-00001). Use the Track page or log in to see its status, and watch the notification bell for updates.</p></details>
    <details class="glass"><summary>Is this for emergencies?</summary><p>No. For emergencies that need immediate help, call 911 or go directly to the barangay hall or the nearest police station.</p></details>
    <details class="glass"><summary>Who can see my report?</summary><p>Only you and authorized barangay personnel. See our <a href="{{ route('privacy') }}" style="color:#fff">Privacy Policy</a>.</p></details>
</section>

{{-- Contact (edit these details) --}}
<section class="section contact" id="contact">
    <h2>Contact Us</h2>
    <div class="glass">
        <div><b>Barangay Hall</b><small>Brgy. Banlic, Cabuyao City, Laguna 4025</small></div>
        <div><b>Office hours</b><small>Mon&ndash;Fri, 8:00 AM &ndash; 5:00 PM</small></div>
        <div><b>Email</b><small>banlic.barangay@example.com</small></div>
    </div>
</section>
@endsection