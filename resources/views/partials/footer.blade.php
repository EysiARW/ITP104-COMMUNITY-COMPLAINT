<footer class="pub-footer">
    <div class="links">
        <a href="{{ route('home') }}" class="footer-logo" aria-label="Barangay Banlic">
            <img src="{{ asset('images/logo.png') }}" alt="Barangay Banlic seal">
        </a>
        <div class="footer-nav">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('report') }}">Report</a>
            <a href="{{ route('track') }}">Track</a>
            <a href="{{ route('home') }}#faqs">FAQs</a>
            <a href="{{ route('home') }}#contact">Contact us</a>
            <a href="{{ route('privacy') }}">Privacy Policy</a>
        </div>
    </div>
    <p>&copy; {{ date('Y') }} Community Complaint &amp; Incident Report System. All rights reserved.</p>
</footer>
