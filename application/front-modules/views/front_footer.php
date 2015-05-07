</div><!-- #main -->
</div><!-- .page-box-content -->
</div><!-- .page-box -->
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row sidebar">
                <aside class="col-xs-12 col-sm-6 col-md-3 widget links AniMate fadeInUp" data-delay="200" data-speed="800" style="-webkit-transition: all 3s ease; -moz-transition: all 3s ease; -ms-transition: all 3s ease; -o-transition: all 3s ease; transition: all 3s ease;">
                    <div class="title-block">
                        <h3 class="title">Information</h3>
                    </div>
                    <nav>
                        <ul>
                            <li><a href="about_us.html">About us</a></li>
                            <li><a href="privacy_policy.html">Privacy Policy</a></li>
                            <li><a href="terms_conditions.html">Terms &amp; Conditions</a></li>
                            <li><a href="secure_payment.html">Secure payment</a></li>
                        </ul>
                    </nav>
                </aside>

                <aside class="col-xs-12 col-sm-6 col-md-6 widget links AniMate fadeInUp" data-delay="200" data-speed="800" style="-webkit-transition: all 3s ease; -moz-transition: all 3s ease; -ms-transition: all 3s ease; -o-transition: all 3s ease; transition: all 3s ease;">
                    <div class="title-block">
                        <h3 class="title">Who we are</h3>
                    </div>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet malesuada nunc. Aliquam commodo ac erat ac pulvinar. Pellentesque malesuada ligula et purus consequat, at porttitor lorem lacinia. Aliquam a erat nec libero euismod fringilla ac eu velit. Vivamus venenatis in ex id congue. Donec sit amet malesuada nunc. Aliquam commodo ac erat ac pulvinar. Pellentesque malesuada ligula et purus consequat, at porttitor lorem lacinia.</p>
                </aside>

                <aside class="col-xs-12 col-sm-6 col-md-3 widget newsletter AniMate fadeInUp" data-delay="400" data-speed="800" style="-webkit-transition: all 3s ease; -moz-transition: all 3s ease; -ms-transition: all 3s ease; -o-transition: all 3s ease; transition: all 3s ease;">
                    <div class="title-block">
                        <h3 class="title">Newsletter Signup</h3>
                    </div>
                    <div>
                        <p>Sign up for newsletter</p>
                        <div class="clearfix"></div>
                        <form>
                            <input class="form-control" type="email">
                            <button class="submit"><span class="glyphicon glyphicon-arrow-right"></span></button>
                        </form>
                    </div>
                </aside><!-- .newsletter -->
            </div>
        </div>
    </div>
    <!-- .footer-top -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="copyright col-xs-12 col-sm-4 col-md-4">
                    <p class="copy-right" style="margin-top:10px;">Copyright © 2015 Leanbusiness. All Rights Reserved</p>
                </div>

                <div class="address col-xs-12 col-sm-4 col-md-4">
                    <div class="Social-Icon">
                        <div class="social-block AniMate bounceIn">
                             <a class="sbtnf sbtnf-rounded color color-hover icon-facebook" href="https://www.facebook.com/leanbusiness.io" target="_blank"></a>
                        <a class="sbtnf sbtnf-rounded color color-hover icon-twitter" href="https://twitter.com/leanbusinessio" target="_blank"></a>
                        <a class="sbtnf sbtnf-rounded color color-hover icon-gplus" href="#" target="_blank"></a>
                        <a class="sbtnf sbtnf-rounded color color-hover icon-linkedin" href="https://www.linkedin.com/in/leanbusiness.io" target="_blank"></a>
                        </div>
                    </div>
                    &nbsp;
                </div>
                
                <div class="col-xs-12 col-sm-4 col-md-4">
                    <a href="#" class="up">
                        <span class="glyphicon glyphicon-arrow-up"></span>
                    </a>
                </div>
            </div>
        </div>
    </div><!-- .footer-bottom -->
</footer>
<div class="clearfix"></div>
<script>
    function init() {
        window.addEventListener('scroll', function (e) {
            var distanceY = window.pageYOffset || document.documentElement.scrollTop,
                    shrinkOn = 300,
                    header = document.querySelector(".header-wrapper");
            if (distanceY > shrinkOn) {
                classie.add(header, "smaller");
            } else {
                if (classie.has(header, "smaller")) {
                    classie.remove(header, "smaller");
                }
            }
        });
    }
    window.onload = init();
</script>
</body>
</html>