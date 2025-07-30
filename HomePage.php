<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub - Modern E-Learning Platform</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- External CSS -->
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                Learnify
            </div>
            <div class="nav-links">
                <a href="#">Home</a>
                <a href="#">Courses</a>
                <a href="#">Pricing</a>
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="Register.php">Login</a>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <!-- Video Container -->
        <div class="video-container">
            <video autoplay muted loop playsinline class="video-background">
                <source src="background_video.mp4" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
            <div class="video-fallback"></div>
        </div>
        
        <!-- Video overlay for better text contrast -->
        <div class="video-overlay"></div>
        
        <!-- Hero content -->
        <div class="hero-content">
            <h1>Unlock Your Potential with Online Learning</h1>
            <p>Access thousands of courses taught by industry experts</p>
            <div class="search-bar">
                <input type="text" placeholder="Search for courses...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
    </section>

            <section class="courses">
            <div class="courses-container">
                <div class="section-title">
                    <h2>Latest Courses</h2>
                    <p>Check out our newest course additions</p>
                </div>
                <div class="course-cards">
                    <?php
        require 'Databaseconnection.php'; // Adjust path if needed

        $result = $conn->query("SELECT course_id, course_name, description, price, image FROM courses ORDER BY created_at DESC LIMIT 3");

        if ($result && $result->num_rows > 0) {
            while ($course = $result->fetch_assoc()) {
                $imagePath = !empty($course['image']) ? 'uploads/' . $course['image'] : 'default.jpg';

                echo '<a href="view-course.php?course_id=' . htmlspecialchars($course['course_id']) . '" class="course-card">';
                echo '<div class="course-image" style="background-image: url(\'' . $imagePath . '\');"></div>';
                echo '<div class="course-info">';
                echo '<h3>' . htmlspecialchars($course['course_name']) . '</h3>';
                echo '<p>' . htmlspecialchars(mb_strimwidth($course['description'], 0, 100, '...')) . '</p>';
                echo '<div class="price">₹' . number_format($course['price'], 2) . '</div>';
                echo '</div></a>';
            }
        } else {
            echo '<p>No courses found.</p>';
        }
        ?>

                </div>
            </div>
        </section>
    
    <section class="features">
        <div class="section-title">
            <h2>Why Choose Learnify ?</h2>
            <p>We provide the best learning experience for students of all levels</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>Expert Instructors</h3>
                <p>Learn from industry professionals with years of practical experience in their fields.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Flexible Learning</h3>
                <p>Study at your own pace, anytime and anywhere with our mobile-friendly platform.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Certification</h3>
                <p>Earn recognized certificates upon completion to boost your career prospects.</p>
            </div>
        </div>
    </section>
    
    <section class="testimonials">
        <div class="section-title">
            <h2>What Our Students Say</h2>
            <p>Success stories from our learning community</p>
        </div>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="testimonial-text">
                    The web development course completely transformed my career. I went from zero coding knowledge to landing my first developer job in just 6 months!
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background-image: url('https://randomuser.me/api/portraits/women/44.jpg');"></div>
                    <div class="author-info">
                        <h4>Sarah Johnson</h4>
                        <p>Frontend Developer</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-text">
                    The instructors explain complex concepts in such an easy-to-understand way. I've taken several courses here and each one has been excellent.
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background-image: url('https://randomuser.me/api/portraits/men/32.jpg');"></div>
                    <div class="author-info">
                        <h4>Michael Chen</h4>
                        <p>Data Analyst</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-text">
                    LearnHub's flexible schedule allowed me to balance my studies with my full-time job. The certification helped me get a promotion at work.
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background-image: url('https://randomuser.me/api/portraits/men/75.jpg');"></div>
                    <div class="author-info">
                        <h4>David Martinez</h4>
                        <p>Marketing Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>
                    Learnify
                </div>
                <p>Empowering learners worldwide with accessible, high-quality education since 2023.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Courses</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Newsletter</h3>
                <div class="newsletter">
                    <p>Subscribe to get updates on new courses and offers</p>
                    <input type="email" placeholder="Your Email Address">
                    <button>Subscribe</button>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 LearnHub. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.feature-card, .course-card, .testimonial-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            animateElements.forEach(element => {
                element.style.opacity = 0;
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.6s ease';
                observer.observe(element);
            });
        });

        // Video fallback detection
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('.video-background');
            const fallback = document.querySelector('.video-fallback');
            
            // Check if video can play
            video.addEventListener('error', function() {
                video.style.display = 'none';
                fallback.style.display = 'block';
            });
            
            // Mobile detection (optional)
            if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                video.style.display = 'none';
                fallback.style.display = 'block';
            }
        });
    </script>
</body>
</html>