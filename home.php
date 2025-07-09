<?php include('header.php'); 

?>

<main class="main-content" id="main-content">
    <section class="hero-section">
        <div class="text-boxes">
            <div class="text-box">Welcome to NoWaste ; Where Saving Food Meets Smart Shopping...</div>
            <div class="text-box">Save Food,Save Money,Save the Planet ! </div>
        </div>
        <div class="d-flex justify-content-center">
            <button class="signup-btn " id="signupBtn" style="width: 20%; color: #0e685c;">
                <?php if (isset($_SESSION['username']) || isset($_COOKIE['username'])) : ?>
                    <a href="products.php">Browse Products</a>
                <?php else : ?>
                    <a href="login.php">Sign Up</a>
                <?php endif; ?>
            </button>
        </div>
    </section>

    <section class="content-area">

        <!-- Banner -->
        <div class="banner-container">
            <div class="banner">
                <span>POKE</span>
                <span>BURRITO</span>
                <span>DONUTS</span>
                <span>SALADS</span>
                <span>PIZZA</span>
                <span>PASTRIES</span>
                <span>GROCERIES</span>
                <span>SANDWICHES</span>
                <span>SMOOTHIES</span>
                <span>SOUPS</span>
                <span>WRAPS</span>
                <span>BAGELS</span>
                <span>BURGERS</span>
                <span>MUFFINS</span>
                <span>PASTA</span>
                <span>FRUIT BOWLS</span>
                <span>RICE BOWLS</span>
                <span>BAKED GOODS</span>
                <span>SUSHI</span>
                <span>CURRIES</span>
                <span>TOASTIES</span>
                <span>MEAL BOXES</span>
                <span>LEFTOVERS</span>
                <span>VEGAN MEALS</span>
                <span>DAIRY PRODUCTS</span>
                <span>READY MEALS</span>
                <span>BREAKFAST BOXES</span>
                <span>DESSERTS</span>
            </div>


            <div class="content-placeholder row align-items-center">
                <div class="col-12 col-md-6 text-center">
                    <h1 class="title">WHY USE</h1>
                    <h2 class="subtitle">NoWaste</h2>
                    <div class="bag my-3">
                        <img src="Images/bag.png" alt="NoWaste bag" class="bag-image img-fluid">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="benefits">
                        <div class="benefit">🍽️ENJOY GOOD FOOD AT 1/2 PRICE OR LESS</div>
                        <div class="benefit">🛍️RESCUE FOOD NEAR YOU</div>
                        <div class="benefit">♻️HELP THE ENVIRONMENT BY REDUCING FOOD WASTE</div>
                        <div class="benefit">🥐TRY SOMETHING NEW FROM LOCAL CAFES, BAKERIES OR RESTAURANTS</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section" id="about-section">
        <div class="container">
            <div class="about-header text-center">
                <div class="about-tagline">About NoWaste</div>
                <p class="about-subtitle">WE SHARE A PLANET WITH <strong>no food waste</strong></p>
            </div>

            <div class="row stats-grid justify-content-center">
                <div class="col-6 col-md-4 mb-3">
                    <div class="stat-item">
                        <div class="stat-number" id="stat1">400+</div>
                        <div class="stat-label">MEALS SAVED</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 mb-3">
                    <div class="stat-item">
                        <div class="stat-number" id="stat2">800+</div>
                        <div class="stat-label">REGISTERD USERS</div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <div class="stat-item">
                        <div class="stat-number" id="stat3">200+</div>
                        <div class="stat-label">BUSINESS PARTNERS</div>
                    </div>
                </div>
            </div>

            <div class="mission-statement text-center">
                <h2 class="mission-title">WHAT'S OUR MISSION?</h2>
                <p class="mission-text">
                    We're committed to creating a world where good food doesn't go to waste.
                    Every meal we save makes our planet healthier and helps feed those in need.
                    Join us in this important movement to reduce food waste and nourish communities.
                </p>
            </div>
        </div>
    </section>

    <!--Blog section-->
    <section class="blog-section" id="blog-section">
        <div class="container">
            <h2 class="text-center">NoWaste Blog</h2>
            <div class="blog-intro text-center">
                <p>Welcome to the NoWaste Blog! Discover practical tips, local recipes, real stories, and impactful initiatives from around Sri Lanka. At NoWaste, we believe every meal deserves a second chance. Reducing food waste is not just a personal choice it's a powerful step toward a greener planet, a stronger economy, and a more caring society.</p>
            </div>

            <div class="row justify-content-center blog-grid">
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/BBU.webp" alt="Blog Post 1" class="img-fluid">
                        <h3>Understanding Best Before vs Use By in Sri Lanka</h3>
                        <p>Confused by expiry dates? Learn how to read food labels correctly and use your senses to reduce unnecessary food waste in your home.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/leftover.jpg" alt="Blog Post 2" class="img-fluid">
                        <h3>Top 10 Sri Lankan Leftover Recipes That Save Food</h3>
                        <p>From fried rice to roti rolls explore easy, tasty recipes that give a second life to your leftovers. Perfect for daily meals or quick snacks.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/logo.png" alt="Blog Post 3" class="img-fluid">
                        <h3>How Colombo's Households Waste Less with NoWaste</h3>
                        <p>A spotlight on families in Colombo using NoWaste to cut food waste, save money, and inspire their neighbors to do the same.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/hotelgalle.jpg" alt="Blog Post 4" class="img-fluid">
                        <h3>Hotel in Galle Saves 200kg of Food</h3>
                        <p>See how a partner hotel in Galle reduced food waste through smart kitchen practices and digital donations using NoWaste.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/fightfoodwaste.jpeg" alt="Blog Post 5" class="img-fluid">
                        <h3>Win a Month of Free Meals by Fighting Food Waste!</h3>
                        <p>Join our "Save to Win" monthly challenge. Track your saved food via the NoWaste website and stand a chance to win exciting eco-friendly prizes.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/savefood.jpg" alt="Blog Post 6" class="img-fluid">
                        <h3>How Much Food Does Sri Lanka Waste? The Real Numbers</h3>
                        <p>Nearly 3,963 tonnes of food is wasted in Sri Lanka daily. Learn where it happens and what we can do.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/world.jpg" alt="Blog Post 7" class="img-fluid">
                        <h3>Why Throwing Away Food Hurts the Planet</h3>
                        <p>Every rice grain tossed has a hidden cost. Understand the environmental damage of food waste and how your actions matter.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/shopping.jpg" alt="Blog Post 8" class="img-fluid">
                        <h3>5 Smart Shopping Habits to Reduce Waste</h3>
                        <p>Don't fall for bulk-buy traps. Learn shopping tricks that help you buy just enough and waste less. Includes a downloadable checklist.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/school.jpg" alt="Blog Post 9" class="img-fluid">
                        <h3>NoWaste for Schools: Teaching Kids the Value of Food</h3>
                        <p>A look at our pilot program educating children in Kandy and Jaffna about sustainable eating and food waste reduction</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/partner.jpg" alt="Blog Post 10" class="img-fluid">
                        <h3>How Food Stores Partner with NoWaste</h3>
                        <p>Learn how local shops and supermarkets partner with NoWaste to turn unsold food into affordable meals instead of throwing it away.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/shopowner.jpg" alt="Blog Post 11" class="img-fluid">
                        <h3>Meet Our Food Heroes: Vendors Fighting Waste Daily</h3>
                        <p>Interviews with NoWaste vendors and restaurant owners who are leading the charge against food waste in their communities.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <article class="blog-card">
                        <img src="images/festival.jpg" alt="Blog Post 12" class="img-fluid">
                        <h3>Festive Seasons and Food Waste: How to Celebrate Sustainably</h3>
                        <p>Whether it's Avurudu or Christmas, learn how to enjoy traditional feasts without throwing away food afterward.</p>
                        <a href="#">Read More</a>
                    </article>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>