<!DOCTYPE html>
<html dir="ltr" lang="en-US">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <meta name="description" content="Telekonsultasi dokter indonesia, Telekonsultasi Medis" />
        <link href="assets/images/favicon/favicon.png" rel="icon" />

        <link href="fonts.googleapis.com/css.css" rel="stylesheet" type="text/css" />

        <link href="assets/css/external.css" rel="stylesheet" />
        <link href="assets/css/style.css" rel="stylesheet" />

        <!--[if lt IE 9]>
            <script src="assets/js/html5shiv.js"></script>
            <script src="assets/js/respond.min.js"></script>
        <![endif]-->

        <title>PitKonsul | Telekonsultasi Dokter Online Indonesia</title>
    </head>
    <body class="body-scroll">
        <div id="wrapperParallax" class="wrapper clearfix">
            <header id="navbar-spy" class="header header-1 header-transparent header-fixed">
                <nav id="primary-menu" class="navbar navbar-expand-lg navbar-dark">
                    <div class="container">
                        <a class="navbar-brand" href="https://landing.zytheme.com/kear/index.html">
                            <img class="logo logo-dark" src="assets/images/logo/logo.png" alt="Kolaso Logo" />
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="toogle-inner"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarContent">
                            <ul class="navbar-nav mr-auto">
                                <li class="active">
                                    <a data-scroll="scrollTo" href="#department">Departemen</a>
                                </li>
                                <li>
                                    <a data-scroll="scrollTo" href="#doctors">Dokter</a>
                                </li>
                                <li>
                                    <a data-scroll="scrollTo" href="#aboutus">Tentang Kami</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>

            <section id="hero" class="section hero">
                <div class="bg-section">
                    <img src="assets/images/background/hero.jpg" alt="background" />
                </div>
                <div class="container">
                    <div class="row row-content">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="hero-headline">
                                Kesehatan anda adalah <br>
                                <span class="typed-text" data-typed-string="Tujuan Kami, Ambisi Kami"></span>
                            </div>
                            <div class="hero-bio">Kami selalu anda untuk menjaga anda tetap sehat.</div>
                            <div class="hero-action">
                                <a href="/redirect" class="btn btn--primary" data-scroll="scrollTo">Konsultasi Sekarang</a>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6"></div>
                    </div>
                </div>
            </section>

            <section id="department" class="section feature feature-3 bg-white pb-80">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="heading heading-1 text-center">
                                <h2 class="heading-title">Departemen Kami</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($departments as $item)
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="feature-panel">
                                <div class="feature-img">
                                    <img src="{{$item->pic}}" style="max-width: 45px" alt="target" />
                                </div>
                                <div class="feature-content">
                                    <h3>{{$item->name}}</h3>
                                    <p>{{$item->description}}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="bar" class="bg-white pt-0 pb-0">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <hr class="hr-bar" />
                        </div>
                    </div>
                </div>
            </section>

            <section id="doctors" class="team bg-white">
                <div class="container">
                    <div class="row clearfix">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="heading heading-1 text-center">
                                <h2 class="heading-title">Dokter Kami</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if($doctors->count() > 0)
                            @foreach($doctors as $item)
                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="member">
                                    <div class="member-img">
                                        <img src="{{$item->profile_pic}}" alt="member" />
                                    </div>

                                    <div class="member-info">
                                        <h5>{{$item->display_name}} <br><span>{{$item->specialist}}</span></h5>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="member">
                                    <div class="member-img">
                                        <img src="assets/images/team/team-1.png" alt="member" />
                                    </div>

                                    <div class="member-info">
                                        <h5>Mark Smith <span>Neurology</span></h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="member">
                                    <div class="member-img">
                                        <img src="assets/images/team/team-2.png" alt="member" />
                                    </div>

                                    <div class="member-info">
                                        <h5>Ryan Printz <span>Radiology</span></h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="member">
                                    <div class="member-img">
                                        <img src="assets/images/team/team-3.png" alt="member" />
                                    </div>

                                    <div class="member-info">
                                        <h5>Steve Martin <span>Cardiology</span></h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            
            <section id="bar" class="bg-white pt-0 pb-0">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <hr class="hr-bar" />
                        </div>
                    </div>
                </div>
            </section>

            <section id="aboutus" class="section feature feature-left bg-white">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="heading heading-1 mb-50 mt-30">
                                <h2 class="heading-title">Tentang Kami</h2>
                                <p class="heading-desc">Kesehatan anda adalah impian kami. PitKonsul menyediakan dokter handal yang sudah terjamin oleh dinas kesehatan dan siap membantu masalah kesehatan anda.</p>
                            </div>
                            <div class="feature-panel">
                                <div class="feature-img">
                                    <img src="https://landing.zytheme.com/kear/assets/images/icons/7.svg" alt="about" />
                                </div>
                                <div class="feature-content">
                                    <h3>Dokter Terbaik</h3>
                                    <p>Dokter kami menerima pasien dari berbagai usia dan keluhan apapun.</p>
                                </div>
                            </div>

                            <div class="feature-panel">
                                <div class="feature-img">
                                    <img src="https://landing.zytheme.com/kear/assets/images/icons/8.svg" alt="about" />
                                </div>
                                <div class="feature-content">
                                    <h3>Konsultasi dari rumah</h3>
                                    <p>Konsultasi dari rumah adalah salah satu opsi terbaik saat ini.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                            <img class="img-fluid img-shadow pull-right" src="assets/images/features/about.jpg" alt="about" />
                        </div>
                    </div>
                </div>
            </section>

            <footer id="footerParallax" class="footer">
                <div class="footer-top">
                    <div class="container">
                        <div class="row widget-boxes text-center">
                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="widget-info-box">
                                    <div class="info-img">
                                        <img src="https://landing.zytheme.com/kear/assets/images/footer/1.svg" alt="phone" />
                                    </div>
                                    <h4>Phone</h4>
                                    <p>+221 340 210 533</p>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="widget-info-box">
                                    <div class="info-img">
                                        <img src="https://landing.zytheme.com/kear/assets/images/footer/2.svg" alt="phone" />
                                    </div>
                                    <h4>Address</h4>
                                    <p>86 Stolham, PA 6550.</p>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4">
                                <div class="widget-info-box">
                                    <div class="info-img">
                                        <img src="https://landing.zytheme.com/kear/assets/images/footer/3.svg" alt="phone" />
                                    </div>
                                    <h4>Opening Time</h4>
                                    <p>Sat-Thu 5:00 to 8:00pm</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bar">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <hr />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-6">
                                <div class="footer-copyright">
                                    <span>&copy; PitKonsul 2021, All Rights Reserved.</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-6">
                                <div class="footer-social">
                                    <a class="facebook" href="#">
                                        <i class="fa fa-facebook"></i>
                                    </a>
                                    <a class="twitter" href="#">
                                        <i class="fa fa-twitter"></i>
                                    </a>
                                    <a class="dribbble" href="#">
                                        <i class="fa fa-dribbble"></i>
                                    </a>
                                    <a class="instagram" href="#">
                                        <i class="fa fa-instagram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <script src="assets/js/plugins.js"></script>
        <script src="assets/js/functions.js"></script>
    </body>
</html>
