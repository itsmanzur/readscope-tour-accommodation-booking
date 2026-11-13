<?php

declare(strict_types=1);

namespace Tourivo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DocumentationPage
 *
 * Interactive, beginner-friendly documentation, feature catalog, and onboarding guide for Readscope Booking.
 *
 * @package Tourivo\Admin
 */
class DocumentationPage
{
    /**
     * Render the interactive documentation and features page view.
     *
     * @return void
     */
    public static function render(): void
    {
        $adminUrl = admin_url();
        ?>
        <div class="wrap tourivo-admin-wrap tourivo-docs-wrap">
            <!-- Hero Header -->
            <div class="tourivo-docs-hero">
                <div class="hero-content">
                    <span class="hero-tagline"><?php esc_html_e('The Complete Agency Guide & Feature Showcase', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <h1 class="hero-title"><?php esc_html_e('Readscope Booking Docs & Features Hub', 'the-readscope-tour-and-accommodation-booking'); ?></h1>
                    <p class="hero-desc">
                        <?php esc_html_e('Readscope Booking is your all-in-one travel booking engine and tour management powerhouse for WordPress. Explore our complete feature catalog, step-by-step guides, and copyable shortcodes to launch your booking website effortlessly—no coding required!', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </p>
                    <div class="hero-quick-actions">
                        <a href="<?php echo esc_url($adminUrl . 'admin.php?page=tourivo'); ?>" class="tourivo-btn-hero tourivo-btn-hero-primary">
                            <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e('Go to Dashboard', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </a>
                        <a href="<?php echo esc_url($adminUrl . 'admin.php?page=tourivo-settings'); ?>" class="tourivo-btn-hero tourivo-btn-hero-outline">
                            <span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Configure Settings', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Interactive Documentation Container -->
            <div class="tourivo-docs-container">
                <!-- Navigation Sidebar Tabs -->
                <div class="tourivo-docs-nav">
                    <ul>
                        <li class="active"><a href="#doc-intro"><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e('1. Introduction & Quick Start', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                        <li><a href="#doc-features"><span class="dashicons dashicons-awards" style="color:#0d9488;"></span> <strong><?php esc_html_e('2. All Features Catalog', 'the-readscope-tour-and-accommodation-booking'); ?></strong></a></li>
                        <li><a href="#doc-tours"><span class="dashicons dashicons-palmtree"></span> <?php esc_html_e('3. Creating Tour Packages', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                        <li><a href="#doc-hotels"><span class="dashicons dashicons-building"></span> <?php esc_html_e('4. Hotels & Room Listings', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                        <li><a href="#doc-embed"><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('5. Page Builders & Shortcodes', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                        <li><a href="#doc-bookings"><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('6. Managing Orders & Bookings', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                        <li><a href="#doc-availability"><span class="dashicons dashicons-calendar" style="color:#0d9488;"></span> <strong><?php esc_html_e('7. Availability & Pricing Manager', 'the-readscope-tour-and-accommodation-booking'); ?></strong></a></li>
                        <li><a href="#doc-pro"><span class="dashicons dashicons-star-filled" style="color:#f59e0b;"></span> <?php esc_html_e('8. Readscope Booking Pro Features', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
                    </ul>
                </div>

                <!-- Documentation Content Panes -->
                <div class="tourivo-docs-content">
                    
                    <!-- TAB 1: Introduction & Quick Start -->
                    <div id="doc-intro" class="tourivo-doc-pane active">
                        <h2><?php esc_html_e('What is Readscope Booking and How Does it Work?', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Think of Readscope Booking as your private booking engine (similar to Booking.com, Airbnb, or Viator) operating directly inside your own WordPress website.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <div class="feature-card-icon icon-tour"><span class="dashicons dashicons-location-alt"></span></div>
                                <h3><?php esc_html_e('Tour Packages & Day Trips', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Sell single-day adventures or multi-day guided trips with day-by-day itineraries, included meals, pickup locations, and passenger caps.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-card-icon icon-hotel"><span class="dashicons dashicons-admin-home"></span></div>
                                <h3><?php esc_html_e('Hotels, Resorts & Rooms', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('List hotels, resorts, or guest houses. Attach multiple room units with specific bed styles, guest capacities, and per-night rates.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-card-icon icon-lock"><span class="dashicons dashicons-shield"></span></div>
                                <h3><?php esc_html_e('Anti-Double-Booking Protection', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Our smart inventory engine automatically prevents two travelers from booking the same room or seat at the same time.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <hr class="doc-divider">

                        <h3><?php esc_html_e('4-Step Quick Launch Checklist', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="quick-launch-steps">
                            <div class="launch-step">
                                <div class="step-num">1</div>
                                <div class="step-details">
                                    <strong><?php esc_html_e('Set Your Currency & Details', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php esc_html_e('Go to Readscope Booking -> Settings. Select your currency code (e.g. USD $, EUR €, BDT ৳), symbol position, and sender email.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                    <a href="<?php echo esc_url($adminUrl . 'admin.php?page=tourivo-settings'); ?>" class="step-action-link"><?php esc_html_e('Open Settings', 'the-readscope-tour-and-accommodation-booking'); ?> &rarr;</a>
                                </div>
                            </div>
                            <div class="launch-step">
                                <div class="step-num">2</div>
                                <div class="step-details">
                                    <strong><?php esc_html_e('Import Starter Demo Data (Recommended for Beginners)', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php esc_html_e('Instantly create complete starter packages: 3 guided tours (Bali, Swiss Alps, Dubai) and 2 boutique hotels with 4 rooms to see full itineraries, rooms, and booking forms in action.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                    <div style="margin-top: 8px;">
                                        <button type="button" class="button button-primary tourivo-import-sample-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('tourivo_admin_nonce')); ?>" style="background: #0d9488; border-color: #0d9488; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                                            <span class="dashicons dashicons-cloud-upload"></span> <?php esc_html_e('Import Starter Demo Content Now', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </button>
                                        <div class="tourivo-seeder-notice" style="display:none; margin-top:8px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="launch-step">
                                <div class="step-num">3</div>
                                <div class="step-details">
                                    <strong><?php esc_html_e('Create or Edit Your Tours & Hotels', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php esc_html_e('Add your trip prices, itineraries, photos, room quantities, and pickup points using our beginner-friendly tabbed editor.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </div>
                            </div>
                            <div class="launch-step">
                                <div class="step-num">4</div>
                                <div class="step-details">
                                    <strong><?php esc_html_e('Publish on Pages via Shortcode or Elementor', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php esc_html_e('Paste shortcodes like [tourivo_tours] or [tourivo_filter_search] on any page or drag Readscope Booking widgets into Elementor.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                    <a href="#doc-embed" class="step-action-link"><?php esc_html_e('View Shortcodes Table', 'the-readscope-tour-and-accommodation-booking'); ?> &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: All Features Catalog -->
                    <div id="doc-features" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('Complete Readscope Booking Feature Catalog', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Explore the complete roster of features engineered into Readscope Booking to power modern travel agencies, tour operators, and boutique hoteliers.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <!-- Feature Category 1: Tours & Trips -->
                        <h3 style="margin-top: 24px;"><?php esc_html_e('Tour & Excursion Management', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Interactive Itinerary Builder', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Create day-by-day or stage-by-stage itineraries with custom titles, included meals (Breakfast/Lunch/Dinner), and detailed descriptions.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Inclusions & Exclusions Checklist', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Visual checkmark and cross-mark list displaying what is included (e.g. Flight, Hotel, Tour Guide) and excluded (e.g. Visa, Tips).', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('GPS Map Coordinates & Pickup Points', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Define exact departure points, return drop-offs, and GPS Latitude/Longitude for interactive map pinpoints.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <!-- Feature Category 2: Hotels & Accommodations -->
                        <h3 style="margin-top: 30px;"><?php esc_html_e('Hotels, Resorts & Accommodation Engine', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('2-Level Hierarchy (Hotel -> Rooms)', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Create parent hotels/resorts and attach unlimited individual room types (Deluxe Suite, Ocean Villa, Standard Room) with unique specs.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Star Ratings & Amenities', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Display 1 to 5 star rating badges, property check-in/out times, and assign custom amenities (Free WiFi, Pool, Spa).', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Bed Styles & Dimensions', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Specify room dimensions (m²/sqft) and bed configurations (King Bed, Twin Beds) for traveler clarity.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <!-- Feature Category 3: Search, Filter & Wishlist -->
                        <h3 style="margin-top: 30px;"><?php esc_html_e('Live Search, Filter & Traveler Wishlist', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Live AJAX Filter & Search [tourivo_filter_search]', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Real-time, zero page-reload filtering by keyword, destination, budget range slider, duration, and star ratings.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Traveler Wishlist & Favorites [tourivo_wishlist]', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('1-Click animated heart button on all cards. Snappy LocalStorage saving + cloud account sync for logged-in users.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Multi-Currency Switcher [tourivo_currency_switcher]', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Instant dropdown widget converting prices into USD, EUR, GBP, BDT ৳, INR ₹, AUD, CAD, and AED in real-time.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <!-- Feature Category 4: Reviews & Lead Capture -->
                        <h3 style="margin-top: 30px;"><?php esc_html_e('Trust, Reviews & Lead Management', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('5-Star Reviews & Rating Scorecards', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Interactive star rating submission form with aggregate 5★ to 1★ breakdown progress bars on single pages.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('✓ Verified Traveler Trust Badge', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Automatically detects confirmed booking emails in database and awards a glowing Verified Traveler badge to genuine reviews.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('1-Click "Trip Inquiry" Lead Capture', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Lead capture popup directly on booking panels with instant admin email alerts and full lead management table in wp-admin.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <!-- Feature Category 5: Operations & Safety -->
                        <h3 style="margin-top: 30px;"><?php esc_html_e('Booking Operations & Safety', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Anti-Double-Booking Inventory Engine', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('MySQL row-level pessimistic locking ensures zero double-booking or overselling even during high-traffic sales.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Admin Manual Booking Creator', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Log phone, WhatsApp, and walk-in offline bookings with inventory lock enforcement and reference code generation.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <span class="tourivo-badge tourivo-badge-paid" style="margin-bottom: 8px;"><?php esc_html_e('FREE CORE', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('1-Click Excel / CSV Traveler Export', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Download traveler manifest lists with UTF-8 BOM encoding for seamless viewing in Microsoft Excel & Google Sheets.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <!-- Feature Category 6: Readscope Booking Pro -->
                        <h3 style="margin-top: 30px;"><?php esc_html_e('Readscope Booking Pro Enterprise Upgrades', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('WooCommerce 100+ Gateways Bridge', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Accept Stripe, PayPal, bKash, Nagad, Mollie, Razorpay, Authorize.net, and COD with automated inventory commit on payment.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Bookable Extra Addons & Upsells', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Sell airport transfers, private guides, breakfast buffets, and extra beds with per-person, per-night calculations.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('iCal 2-Way Sync (Airbnb / Booking.com)', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Automatic two-way calendar sync with Airbnb, Booking.com, and VRBO via standard RFC 5545 iCalendar feeds.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Printable PDF Tickets & QR Check-in', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Generate beautiful PDF boarding passes with secure HMAC-verified QR codes for mobile smartphone scanning.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Customer Account Dashboard [tourivo_customer_dashboard]', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Dedicated traveler portal displaying upcoming trips, past itineraries, 1-click PDF vouchers, and guest lookup form.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card" style="border-color: #fde68a;">
                                <span class="tourivo-badge tourivo-badge-pending" style="margin-bottom: 8px;"><?php esc_html_e('PRO ADDON', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <h4><?php esc_html_e('Tiered Passenger & Group Discounts', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Independent rates for Adults, Children, and Infants + automated volume discounts (e.g. 5+ travelers get 10% off).', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Creating Tour Packages -->
                    <div id="doc-tours" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('How to Create & Configure Tour Packages', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Navigate to Tours -> Add New Tour in your WordPress admin menu. Readscope Booking uses a structured tabbed interface:', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <div class="doc-grid-2">
                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('1. Pricing & General Tab', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Tour Type:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Single-Day, Multi-Day, Hourly Activity, or Fixed Departure.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Base Price & Sale Price:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Set regular price and optional discounted sale price.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Duration Text:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('E.g. "3 Days / 2 Nights" or "4 Hours".', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Passenger Caps:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Set minimum and maximum travelers per booking.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>

                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('2. Itinerary Builder Tab', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Click "+ Add Day / Stage" to build a timeline:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Day Title:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('E.g. "Day 1: Arrival & Sunset Beach Dinner".', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Included Meals:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('E.g. "Breakfast, Dinner".', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Stage Description:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Detailed activities for that day.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="doc-grid-2" style="margin-top: 20px;">
                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('3. Inclusions & Exclusions Tab', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Clearly outline what travelers get with their ticket:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Inclusions:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Hotel pickup, English-speaking guide, Entry tickets.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Exclusions:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Personal expenses, International flights, Visa fees.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>

                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-location"></span> <?php esc_html_e('4. Map & FAQs Tab', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Pickup & Drop-off Points:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Meeting point instructions.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('GPS Coordinates:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Latitude and Longitude for map markers.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('FAQs Accordion:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Common questions answered directly on the page.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Hotels & Room Listings -->
                    <div id="doc-hotels" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('Understanding the 2-Level Hotel Structure', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Readscope Booking handles accommodation in an industry-standard 2-level hierarchy: 1. Hotel Property (the building / resort) and 2. Room Types (the bookable units).', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <div class="doc-grid-2">
                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-building"></span> <?php esc_html_e('Step 1: Create the Hotel Property', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Go to Hotels -> Add New Hotel:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><?php esc_html_e('Enter Hotel Title (e.g. "Grand Ocean Resort & Spa").', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Set Star Rating (1 to 5 Stars).', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Enter Street Address, City, Phone, and Email.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Assign Amenities (Pool, Free WiFi, Spa, Gym).', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Set Check-in and Check-out standard times.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>

                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e('Step 2: Add Room Units to that Hotel', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Go to Rooms -> Add New Room:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><?php esc_html_e('Enter Room Title (e.g. "Deluxe Sea View Suite").', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Select the Parent Hotel from the dropdown.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Set the Base Nightly Price (e.g., $180/night).', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Set Maximum Adults & Children capacity.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><?php esc_html_e('Set Total Units Available (e.g. 10 suites).', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="doc-callout callout-info">
                            <strong><span class="dashicons dashicons-info"></span> <?php esc_html_e('How Travelers Experience Your Hotel Page:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                            <p><?php esc_html_e('When travelers open any Hotel page, Readscope Booking displays all linked room types with their nightly prices, specs, bed styles, and an interactive "Reserve Room" dropdown for each room.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        </div>
                    </div>

                    <!-- TAB 5: Page Builders & Shortcodes -->
                    <div id="doc-embed" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('Displaying Tours, Hotels & Search Bars on Your Pages', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('You can embed Readscope Booking elements on your Homepage, Destination pages, or any Custom page using Gutenberg, Elementor, or simple Shortcodes.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <!-- Copyable Shortcodes Table -->
                        <h3><?php esc_html_e('1. Copy-and-Paste Shortcodes', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <table class="widefat tourivo-code-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Element / Purpose', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                    <th><?php esc_html_e('Shortcode', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                    <th style="width: 120px;"><?php esc_html_e('Action', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Travel Search Bar', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Destination dropdown, date picker, and traveler counter bar.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_search_bar]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code="[tourivo_search_bar]"><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('All Tours Grid (3 Columns)', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Displays 6 latest tours in a 3-column responsive card grid.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_tours columns="3" count="6"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_tours columns="3" count="6"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Tours by Destination (e.g. Bali)', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Filters tours belonging only to a specific destination slug.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_tours destination="bali" count="4"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_tours destination="bali" count="4"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('All Hotels & Stays Grid', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Displays your luxury hotels with star ratings and starting prices.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_hotels columns="3" count="6"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_hotels columns="3" count="6"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Live AJAX Filter & Search Grid', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Instant no-reload live search with destination, budget slider & duration filters.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_filter_search type="tour" columns="3"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_filter_search type="tour" columns="3"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Traveler Wishlist & Favorites Page', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Displays all saved tours and hotels stored in traveler wishlist.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_wishlist columns="3"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_wishlist columns="3"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Track My Booking & Voucher Portal', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Self-service portal for travelers to track status & download printable vouchers.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_booking_lookup]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_booking_lookup]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Multi-Currency Dropdown Switcher', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Allows travelers to toggle and convert pricing into USD, EUR, GBP, BDT, INR, AUD, CAD, AED.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_currency_switcher]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_currency_switcher]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Customer Account / My Bookings Portal', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Authenticated traveler portal showing past and upcoming reservations, status, calendar .ics, vouchers, and cancellation requests.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_my_bookings per_page="10" show_stats="yes"]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_my_bookings per_page="10" show_stats="yes"]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Booking Confirmation & Thank You Page', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                        <br><small><?php esc_html_e('Displays post-booking overview, token-secured printable voucher, and .ics calendar download.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_thank_you]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_thank_you]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?php esc_html_e('Customer Frontend Account Dashboard', 'the-readscope-tour-and-accommodation-booking'); ?> <span class="tourivo-badge tourivo-badge-pending" style="font-size: var(--trv-admin-text-xs); padding:2px 6px;"><?php esc_html_e('PRO', 'the-readscope-tour-and-accommodation-booking'); ?></span></strong>
                                        <br><small><?php esc_html_e('Self-service portal for travelers: past bookings, itinerary lookup, PDF tickets & invoices.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                                    </td>
                                    <td><code>[tourivo_customer_dashboard]</code></td>
                                    <td><button type="button" class="button copy-code-btn" data-code='[tourivo_customer_dashboard]'><?php esc_html_e('Copy', 'the-readscope-tour-and-accommodation-booking'); ?></button></td>
                                </tr>
                            </tbody>
                        </table>

                        <hr class="doc-divider">

                        <!-- Elementor & Gutenberg Info -->
                        <div class="doc-grid-2">
                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Elementor Page Builder', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('If you use Elementor, simply open the Elementor editor and search for "Readscope Booking" in the widget panel. You will find:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Tour Grid:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Visual count, column selector, and destination filter.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Hotel Grid:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Responsive card styling and star badges.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Search Bar:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Instant traveler search widget.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Booking Panel:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Embed a direct booking box anywhere.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>

                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-block-default"></span> <?php esc_html_e('Gutenberg Block Editor', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('In the WordPress standard block editor, click (+) and search for "Readscope Booking" blocks:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Readscope Tour Grid:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Live preview grid block.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Readscope Hotel Grid:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Hotel showcase block.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Readscope Search Bar:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Traveler destination lookup block.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Readscope Booking Panel:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Instant checkout block.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: Managing Orders & Bookings -->
                    <div id="doc-bookings" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('Managing Traveler Bookings & Inquiries', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Readscope Booking gives your front desk and agency administrators full control over customer reservations and pre-sale inquiries.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <div class="doc-grid-2">
                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e('Orders & Bookings Table', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Open Readscope Booking -> Bookings:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Booking Code:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Unique code generated for every reservation (e.g. #TRV-2026-X9A21B).', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('1-Click Confirm / Cancel:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Update status instantly with Ajax without page refreshes.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Activity Timeline & Notes:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Click "Details" to read history and record staff notes.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Advanced Export & Reporting (CSV):', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Click "Export Bookings" from the Dashboard or Bookings table to launch the export dialog. Filter by Booking vs Travel dates, booking/payment status, and specific tour/room. Generate standard booking lists, itemized line manifests, or financial summary reports with full Excel UTF-8 and formula-injection security.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>

                            <div class="doc-card">
                                <h3><span class="dashicons dashicons-format-chat"></span> <?php esc_html_e('Trip Inquiries & Leads Table', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Open Readscope Booking -> Inquiries:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                <ul class="simple-checklist">
                                    <li><strong><?php esc_html_e('Pre-sale Leads:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('View traveler questions, dates, and pax counts before they book.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('1-Click Email Reply:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Click "✉️ Reply" to open pre-filled email client.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                    <li><strong><?php esc_html_e('Status Pipeline:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Filter by New, Replied, or Closed.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 7: Availability & Pricing Manager -->
                    <div id="doc-availability" class="tourivo-doc-pane">
                        <h2><?php esc_html_e('Availability & Pricing Calendar Manager', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="lead-paragraph">
                            <?php esc_html_e('Take total control of your tour departures and hotel room availability with our visual monthly calendar grid. Block dates for maintenance or holidays, adjust seasonal capacities, and set custom date pricing with bulk actions.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <div class="tourivo-feature-cards-grid">
                            <div class="feature-card">
                                <div class="feature-card-icon icon-tour"><span class="dashicons dashicons-calendar-alt"></span></div>
                                <h3><?php esc_html_e('Visual Monthly Grid', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Found on every Tour and Room edit screen. Displays real-time capacity, booked spots, reserved holds, status, and active price.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-card-icon icon-lock"><span class="dashicons dashicons-lock"></span></div>
                                <h3><?php esc_html_e('Block & Unblock Dates', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Instantly close dates from receiving new bookings. Use the Force Block option to close dates that already contain reservations without affecting existing guests.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-card-icon icon-hotel"><span class="dashicons dashicons-tag"></span></div>
                                <h3><?php esc_html_e('Seasonal Price Overrides', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                <p><?php esc_html_e('Charge peak holiday rates or offer off-season promotions on specific dates without altering the global base price.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>

                        <hr class="doc-divider">

                        <h3><?php esc_html_e('How to Use the Availability Manager', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <ol style="line-height: 1.8; color: #334155; font-size: var(--trv-admin-text-base); padding-left: 20px;">
                            <li><strong><?php esc_html_e('Navigate to Tour or Room Edit Screen:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Open any published Tour or Room. Scroll down to the "Availability & Pricing Calendar" metabox.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                            <li><strong><?php esc_html_e('Select Dates:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Click any date to select it. Hold Shift and click another date to select an entire date range. Use the weekday checkboxes (Mo-Su) to filter your selection.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                            <li><strong><?php esc_html_e('Choose Actions to Apply:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('In the right-hand panel, choose your Status action (Available or Blocked), adjust total capacity, or set a custom Price Override.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                            <li><strong><?php esc_html_e('Apply Changes:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Click "Apply to Selected Dates". The system updates the inventory atomically in a transaction, invalidates SEO caches, and writes an audit log.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                        </ol>

                        <div style="background: #f0fdfa; border-left: 4px solid #0d9488; padding: 14px 18px; border-radius: 6px; margin-top: 16px;">
                            <strong style="color: #0f766e;"><?php esc_html_e('Developer Hook:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                            <p style="margin: 4px 0 0 0; font-size: var(--trv-admin-text-sm); color: #0c4a6e;">
                                <?php esc_html_e('Listen to availability updates via:', 'the-readscope-tour-and-accommodation-booking'); ?>
                                <code>do_action("tourivo/availability_updated", $itemId, $itemType, $startDate, $endDate, $changes);</code>
                            </p>
                        </div>
                    </div>

                    <!-- TAB 8: Readscope Booking Pro Features -->
                    <div id="doc-pro" class="tourivo-doc-pane">
                        <div class="pro-showcase-header">
                            <span class="pro-pill"><?php esc_html_e('ENTERPRISE EXTENSIONS', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                            <h2><?php esc_html_e('Unlock the Full Potential with Readscope Booking Pro', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                            <p class="lead-paragraph">
                                <?php esc_html_e('Readscope Booking Pro upgrades your booking engine into a fully automated travel marketplace with WooCommerce payments, calendar sync, and ticket check-in.', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </p>
                        </div>

                        <div class="pro-features-grid">
                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-cart"></span></div>
                                <h4><?php esc_html_e('1. WooCommerce Payment Gateway Bridge', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Seamlessly checkout via 100+ gateways: Stripe, PayPal, bKash, Nagad, Mollie, Razorpay, COD, and direct bank transfers.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>

                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-plus-alt"></span></div>
                                <h4><?php esc_html_e('2. Bookable Extra Add-ons & Upsells', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Sell optional extras like Airport Transfer, Private Tour Guide, Breakfast Buffet, and Extra Beds with per-person and per-night calculation options.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>

                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-groups"></span></div>
                                <h4><?php esc_html_e('3. Passenger Pricing Tiers & Group Discounts', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Set different rates for Adults, Children, and Infants, plus automatic volume discounts when booking for 5+ travelers.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>

                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-pdf"></span></div>
                                <h4><?php esc_html_e('4. PDF Tickets & QR Code Mobile Check-in', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Automated PDF boarding vouchers with cryptographically signed QR codes that staff can scan at boarding with any mobile camera.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>

                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-update"></span></div>
                                <h4><?php esc_html_e('5. Two-Way iCal Calendar Sync (Airbnb, Booking.com)', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Export and auto-import iCal feeds to sync hotel room availability with Airbnb, Booking.com, VRBO, and Google Calendar.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>

                            <div class="pro-feature-item">
                                <div class="pro-icon"><span class="dashicons dashicons-admin-users"></span></div>
                                <h4><?php esc_html_e('6. Customer Frontend Dashboard & Guest Lookup', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                                <p><?php esc_html_e('Shortcode [tourivo_customer_dashboard] gives travelers a self-service portal to view upcoming trips, order history, and download PDF tickets.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }
}
