<?php
/*
 * File: contact.php
 * Author: [Lena ALQahtani]
 * Group: [3]
 */

// Contact page - shows a contact form and store info
// When the form is submitted we just show a success message (no email sending needed)

// Start session for cart count in header
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pageTitle  = "Contact Us";
$activePage = "contact";
require_once 'includes/header.php';

// Check if the form was submitted
$submitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real project we would send an email here using mail() or PHPMailer
    // For now we just set a flag to show the thank-you message
    $submitted = true;
}
?>

<style>
  .contact-banner{background:#E9F2E3;padding:50px 40px;text-align:center;}
  .contact-banner h1{font-size:36px;color:#2F6B3A;font-weight:700;margin-bottom:10px;}
  .contact-banner p{color:#555;font-size:17px;max-width:600px;margin:0 auto;}
  .contact-wrap{max-width:1100px;margin:0 auto;padding:50px 40px;display:grid;grid-template-columns:3fr 2fr;gap:40px;align-items:start;}
  .contact-form{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:36px;}
  .contact-form h2{font-size:22px;color:#2F6B3A;font-weight:700;margin-bottom:22px;}
  .form-group{margin-bottom:18px;}
  .form-group label{display:block;font-weight:600;font-size:14px;color:#2F6B3A;margin-bottom:6px;}
  .form-group input,.form-group textarea{width:100%;padding:11px 14px;border:1.5px solid #d0d8d0;border-radius:8px;font-size:15px;color:#333;font-family:inherit;}
  .form-group input:focus,.form-group textarea:focus{outline:none;border-color:#4CAF50;}
  .form-group textarea{resize:vertical;min-height:130px;}
  .btn-send{background:#4CAF50;color:white;border:none;padding:13px 30px;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;width:100%;}
  .btn-send:hover{background:#2F6B3A;}
  .info-card{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:28px;margin-bottom:20px;}
  .info-card h2{font-size:20px;color:#2F6B3A;font-weight:700;margin-bottom:18px;}
  .info-item{display:flex;gap:14px;margin-bottom:16px;align-items:flex-start;}
  .info-icon{font-size:20px;margin-top:2px;}
  .info-item strong{display:block;color:#2F6B3A;font-size:14px;margin-bottom:2px;}
  .info-item div{font-size:14px;color:#555;line-height:1.6;}
  .map-box{border-radius:14px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.08);}
  .map-box iframe{width:100%;height:220px;border:0;display:block;}
  @media(max-width:768px){.contact-wrap{grid-template-columns:1fr;padding:30px 20px;}}
</style>

<main id="main-content">
  <div class="contact-banner">
    <h1>Get In Touch</h1>
    <p>We'd love to hear from you. Send us a message and we'll get back to you within 24 hours.</p>
  </div>

  <div class="contact-wrap">

    <!-- CONTACT FORM -->
    <div class="contact-form">
      <h2>✉️ Send Us a Message</h2>

      <?php if ($submitted) : ?>
        <!-- Show this after the form is submitted -->
        <div class="flash flash-success">✅ Thank you! Your message has been sent. We'll get back to you soon.</div>
      <?php else : ?>

        <form method="POST" action="contact.php">
          <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="What is this about?">
          </div>
          <div class="form-group">
            <label for="msg">Message *</label>
            <textarea id="msg" name="message" placeholder="Write your message here…" required></textarea>
          </div>
          <button class="btn-send" type="submit">Send Message 📨</button>
        </form>

      <?php endif; ?>
    </div>

    <!-- CONTACT INFO AND MAP -->
    <div>
      <div class="info-card">
        <h2>📍 Our Information</h2>
        <div class="info-item">
          <span class="info-icon">📍</span>
          <div><strong>Address</strong>King Faisal Road, Al Khobar,<br>Eastern Province, Saudi Arabia</div>
        </div>
        <div class="info-item">
          <span class="info-icon">📞</span>
          <div><strong>Phone</strong>+966 13 800 1234</div>
        </div>
        <div class="info-item">
          <span class="info-icon">✉️</span>
          <div><strong>Email</strong><a href="mailto:athar@gmail.com" style="color:#4CAF50;">athar@gmail.com</a></div>
        </div>
        <div class="info-item">
          <span class="info-icon">🕐</span>
          <div><strong>Business Hours</strong>Sun – Thu: 9:00 AM – 6:00 PM<br>Fri – Sat: 10:00 AM – 4:00 PM</div>
        </div>
      </div>
      <div class="map-box">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3578.5!2d50.2083!3d26.2172!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDEzJzAyLjAiTiA1MMKwMTInMjkuOSJF!5e0!3m2!1sen!2ssa!4v1234567890"
          allowfullscreen="" loading="lazy" title="Athar store location"></iframe>
      </div>
    </div>

  </div>
</main>

<script>
// JS validation for the contact form (Task 13)
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action="contact.php"]');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var name    = document.getElementById('name').value.trim();
        var email   = document.getElementById('email').value.trim();
        var message = document.getElementById('msg').value.trim();

        if (!name) {
            e.preventDefault();
            alert('Please enter your full name.');
            return;
        }
        // Basic email format check using a simple regex pattern
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailPattern.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return;
        }
        if (!message) {
            e.preventDefault();
            alert('Please write your message before sending.');
            return;
        }
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
