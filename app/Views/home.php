<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sun Son Solar</title>
    <link href="https://fonts.googleapis.com/css2?family=Miniver&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body>
    <!-- Header / Navbar -->
    <header>
        <nav class="navbar section-content">
            <a href="#top" class="nav-logo">
                <h2 class="logo-text">
                    <img src="<?= base_url('images/Sun Son Solar.png') ?>" alt="Sun Son Solar logo" width="70" height="50">
                    <span>Sun Son Solar</span>
                </h2>
            </a>
            <ul class="nav-menu">
    <li class="nav-item"><a href="#top" class="nav-link">Home</a></li>
    <li class="nav-item"><a href="#services" class="nav-link">Services</a></li>
    <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
    <li class="nav-item"><a href="#reviews" class="nav-link">Reviews</a></li>
    <li class="nav-item"><a href="#faq" class="nav-link">FAQ</a></li>
    <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
    <?php if (session()->get('account_id')): ?>
    <li class="nav-item"><span class="nav-link">Hello, <?= esc(session()->get('account_name')) ?></span></li>
    <li class="nav-item"><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button class="nav-link" type="submit">Sign out</button></form></li>
    <?php else: ?>
    <li class="nav-item"><a href="<?= site_url('login') ?>" class="nav-link">Sign in</a></li>
    <li class="nav-item"><a href="<?= site_url('register') ?>" class="nav-link nav-link-signup">Sign Up</a></li>
    <?php endif; ?>
</ul>
        </nav>
    </header>

    <main id="top">
        <!-- Hero Section: background image and overlay kept -->
        <section class="hero-section">
            <div class="section-content hero-content">
                <div class="hero-details">
                    <p class="eyebrow">CLEAN ENERGY. BRIGHTER FUTURE.</p>
                    <h1 class="title">Power your Future with Solar</h1>
                    <h3 class="subtitle">We help homes and businesses switch to clean, affordable solar energy from the first consultation to long term maintenance.</h3>
                    <div class="buttons">
                        <a href="<?= site_url('requests?service=Quote') ?>" class="button Request-Quote">Request a Free Quote</a>
                        <a href="<?= site_url('requests?service=Maintenance') ?>" class="button Schedule-Service">Schedule a Service</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits -->
        <section class="benefits" aria-labelledby="bh">
            <div class="section-content">
                <div class="section-heading">
                    <p class="section-kicker">THE BENEFITS</p>
                    <h2 id="bh">Why go solar?</h2>
                    <p class="sub">Three good reasons people switch.</p>
                </div>
                <div class="grid3">
                    <article class="card">
                        <div class="ic" aria-hidden="true">💰</div>
                        <h3>Lower electricity bills</h3>
                        <p>Make your own power and cut what you pay the utility every month.</p>
                    </article>
                    <article class="card">
                        <div class="ic" aria-hidden="true">🌱</div>
                        <h3>Cleaner for the planet</h3>
                        <p>Solar produces energy without burning fuel, shrinking your carbon footprint.</p>
                    </article>
                    <article class="card">
                        <div class="ic" aria-hidden="true">🔋</div>
                        <h3>Energy independence</h3>
                        <p>Rely less on the grid and be less affected by rising rates and outages.</p>
                    </article>
                </div>
            </div>
        </section>
        
        <!-- About Section -->
        <section class="about-section" id="about">
            <div class="section-content about-layout">
                <div class="about-details">
                    <p class="section-kicker">GET TO KNOW US</p>
                    <h2 class="about-title">About Us</h2>
                    <p class="about-description">
                        Sun Son Solar is a solar company based in Pasig City,
                        founded by Katherine “Kat” Sinagaraw and her father,
                        Santino “Sonny” Sinagaraw. We supply solar panels,
                        inverters, batteries, racking and mounting systems,
                        and wires. We also provide end-to-end services,
                        including consultation, design, permitting,
                        installation, maintenance, repair, and monitoring.
                        Our goal is to make going solar simple, from your
                        first inquiry to long-term support.
                    </p>
                    <a class="about-button" href="#contact">Talk to our team</a>
                </div>
                <div class="facts">
                    <div class="fact"><b>Free consultation</b><span>We check your roof and bills before you spend anything.</span></div>
                    <div class="fact"><b>Licensed installers</b><span>Systems set up safely and to code.</span></div>
                    <div class="fact"><b>Ongoing support</b><span>We stay with you after installation.</span></div>
                </div>
            </div>
        </section>
        <!-- Services Section -->
        <section class="services-section" id="services">
            <div class="section-content">
                <div class="section-heading">
                    <p class="section-kicker">HOW WE CAN HELP</p>
                    <h2 class="service-title">Our Services</h2>
                    <p class="sub">Everything you need, in three simple steps.</p>
                </div>
                <div class="grid3">
                    <article class="card">
                        <div class="ic" aria-hidden="true">📋</div>
                        <h3>Consultation</h3>
                        <p>We assess your home or business, review your bills, and recommend the right system size.</p>
                        <ul><li>Site visit</li><li>Savings estimate</li></ul>
                        <a class="service-button" href="<?= site_url('requests?service=Consultation') ?>">Book a consultation</a>
                    </article>
                    <article class="card">
                        <div class="ic" aria-hidden="true">🔧</div>
                        <h3>Installation</h3>
                        <p>Our certified team installs your panels and connects everything safely.</p>
                        <ul><li>Quality equipment</li><li>Clean, on-time work</li></ul>
                        <a class="service-button" href="<?= site_url('requests?service=Installation') ?>">Get a quote</a>
                    </article>
                    <article class="card">
                        <div class="ic" aria-hidden="true">🛠️</div>
                        <h3>Maintenance</h3>
                        <p>Regular check-ups and cleaning keep your system producing at its best.</p>
                        <ul><li>Inspections</li><li>Repairs and support</li></ul>
                        <a class="service-button" href="<?= site_url('requests?service=Maintenance') ?>">Schedule maintenance</a>
                    </article>
                </div>
            </div>
        </section>

  <!-- Customer Feedback Section -->
<section class="reviews-section" id="reviews">
    <div class="section-content">

        <div class="section-heading">
            <p class="section-kicker">CUSTOMER FEEDBACK</p>
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="sub">
                Your feedback helps us improve our solar services.
            </p>
        </div>

        <!-- Customer Reviews -->
        <div class="reviews-container" id="reviewsContainer">
            <div class="reviews-empty" id="emptyReviews">
                <h3>No customer reviews yet</h3>
                <p>Be the first to share your experience!</p>
            </div>
        </div>

        <!-- Feedback Form -->
        <div class="feedback-form-container">
            <h3>Share Your Experience</h3>
            <p>We would love to hear your feedback!</p>

            <form id="feedbackForm">

                <div class="form-group">
                    <label for="customerName">Your Name</label>
                    <input type="text" id="customerName"
                           placeholder="Enter your name" required>
                </div>

                <div class="form-group">
                    <label for="rating">Your Rating</label>
                    <select id="rating" required>
                        <option value="">Select your rating</option>
                        <option value="5">★★★★★ - Excellent</option>
                        <option value="4">★★★★☆ - Very Good</option>
                        <option value="3">★★★☆☆ - Good</option>
                        <option value="2">★★☆☆☆ - Fair</option>
                        <option value="1">★☆☆☆☆ - Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="feedbackMessage">Your Feedback</label>
                    <textarea id="feedbackMessage" rows="5"
                              placeholder="Write your experience here..."
                              required></textarea>
                </div>

                <button type="submit" class="feedback-btn">
                    Submit Feedback
                </button>

            </form>
        </div>

    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section" id="faq">
    <div class="section-content">

        <div class="section-heading">
            <p class="section-kicker">NEED HELP?</p>
            <h2 class="section-title">
                Frequently Asked Questions
            </h2>
            <p class="sub">
                Find answers to common questions about our
                solar products and services.
            </p>
        </div>

        <div class="faq-container">

            <details class="faq-item">
                <summary>
                    What is solar energy?
                </summary>
                <p>
                    Solar energy is electricity generated
                    from sunlight using solar panels.
                    It is a renewable and environmentally
                    friendly energy source.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    How much does solar panel installation cost?
                </summary>
                <p>
                    Installation costs depend on the size
                    of the solar system, equipment, and
                    energy requirements. Contact Sun Son Solar
                    for a personalized quotation.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    How long does solar installation take?
                </summary>
                <p>
                    Installation time depends on the system
                    size and site conditions. Permits and
                    other requirements may also affect
                    the project timeline.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    Do solar panels work during rainy days?
                </summary>
                <p>
                    Yes. Solar panels can still generate
                    electricity during cloudy or rainy
                    weather, but their energy production
                    is usually lower.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    Do solar panels require maintenance?
                </summary>
                <p>
                    Yes. Solar panels benefit from regular
                    inspection, cleaning when needed, and
                    equipment maintenance to maintain
                    performance and safety.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    Can solar panels reduce electricity bills?
                </summary>
                <p>
                    Yes. Solar panels can help reduce
                    electricity costs by generating
                    power for your home or business.
                    Actual savings depend on energy
                    consumption and system performance.
                </p>
            </details>

            <details class="faq-item">
                <summary>
                    How can I request a consultation?
                </summary>
                <p>
                    You can contact Sun Son Solar through
                    our contact information or use the
                    Request a Free Quote button on our website.
                </p>
            </details>

        </div>

    </div>
</section>

    <!-- Footer / Contact -->
    <footer id="contact" class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <h2 class="footer-logo">Sun Son Solar</h2>
                <p>Powering homes and businesses with clean and affordable solar energy.</p>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <p>📍 Pasig City, Philippines</p>
                <p>📞 +63 912 345 6789</p>
                <p>✉ info@sunsonsolar.com</p>
            </div>
            <div class="footer-column">
                <h3>Business Hours</h3>
                <p>Monday - Friday</p>
                <p>8:00 AM - 5:00 PM</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 Sun Son Solar. All Rights Reserved.</p>
        </div>
    </footer>
    <?php if (! empty($notice)): ?><p class="site-notice" role="status"><?= esc($notice) ?></p><?php endif; ?>
<script>
    const feedbackForm = document.getElementById("feedbackForm");
    const reviewsContainer = document.getElementById("reviewsContainer");
    const emptyReviews = document.getElementById("emptyReviews");

    feedbackForm.addEventListener("submit", function(event) {
        event.preventDefault();

        const name = document.getElementById("customerName").value.trim();
        const rating = Number(document.getElementById("rating").value);
        const message = document.getElementById("feedbackMessage").value.trim();

        if (!name || !message || rating < 1 || rating > 5) {
            return;
        }

        // Remove empty message
        if (emptyReviews) {
            emptyReviews.style.display = "none";
        }

        // Create review card
        const reviewCard = document.createElement("div");
        reviewCard.classList.add("review-card");

        const customerName = document.createElement("h3");
        customerName.textContent = name;

        const stars = document.createElement("div");
        stars.classList.add("review-stars");
        stars.textContent = "★".repeat(rating) + "☆".repeat(5 - rating);

        const feedbackText = document.createElement("p");
        feedbackText.textContent = message;

        reviewCard.appendChild(customerName);
        reviewCard.appendChild(stars);
        reviewCard.appendChild(feedbackText);

        reviewsContainer.prepend(reviewCard);

        // Clear form
        feedbackForm.reset();

        alert("Thank you for your feedback!");
    });
</script>
</body>
</html>
