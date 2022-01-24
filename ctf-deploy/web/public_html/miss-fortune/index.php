<?php
require_once("connection.php");
session_start();
if(!$_SESSION['username']){
    header('Location: login.php');
}?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>KMA CTF 2022</title>
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.1.1/bootstrap-social.min.css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css'><link rel="stylesheet" href="./stylesheet/style.css">
</head>
<body style="
    background-image: url(https://3.bp.blogspot.com/-aNAkx3MVUNQ/XDfT2dMAdHI/AAAAAAAAAFM/C9vSeTBZeFA4KKaM2q1EojcfpIB2sMOugCPcBGAYYCw/w0/coin-emperor-tahm-kench-splash-art.jpg);
    background-attachment: fixed;
    background-repeat: no-repeat;
    background-size: cover;
">
<!-- partial:index.partial.html -->
<div id="app" >
    <main  v-if="!tab"  class="site-wrapper">
        <div  class="pt-table desktop-768">
          <div  class="pt-tablecell page-home relative" style="background-image: url(/images/a.png);
          background-position: center;
          background-size: cover;">
                          <div   class="overlay"></div>
                          <div    class="container">
                              <div class="row">
                                  <div class="col-xs-12 col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
                                      <div class="page-title  home text-center">
                                        <span class="heading-page"> Welcome to KMA CTF
                                        </span>
                                          <p class="mt20"> 👳 Username : {{username}}</p>
                                          <p class="mt20">💵 Balance : {{balance}}💲</p>
                                          
                                      </div>
      
                                      <div class="hexagon-menu clear">
                                          <div @click="luckyDraw" class="hexagon-item">
                                              <div class="hex-item">
                                                  <div></div>
                                                  <div></div>
                                                  <div></div>
                                              </div>
                                              <div class="hex-item">
                                                  <div></div>
                                                  <div></div>
                                                  <div></div>
                                              </div>
                                              <a  class="hex-content">
                                                  <span class="hex-content-inner">
                                                      <span class="icon">
                                                          <i class="fa fa-bullseye"></i>
                                                      </span>
                                                      <span class="title">Lucky Draw</span>
                                                  </span>
                                                  <svg viewBox="0 0 173.20508075688772 200" height="200" width="174" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z" fill="#1e2530"></path></svg>
                                              </a>
                                          </div>
                                          <div @click="openStore" class="hexagon-item">
                                              <div class="hex-item">
                                                  <div></div>
                                                  <div></div>
                                                  <div></div>
                                              </div>
                                              <div class="hex-item">
                                                  <div></div>
                                                  <div></div>
                                                  <div></div>
                                              </div>
                                              <a  class="hex-content">
                                                  <span class="hex-content-inner">
                                                      <span class="icon">
                                                          <i class="fa fa-braille"></i>
                                                      </span>
                                                      <span class="title">Store</span>
                                                  </span>
                                                  <svg viewBox="0 0 173.20508075688772 200" height="200" width="174" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z" fill="#1e2530"></path></svg>
                                              </a>    
                                          </div>
                                          <div class="hexagon-item">
                                        <div class="hex-item">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                        <div  class="hex-item">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                        <a   href="logout.php" style=" color: #ababab; " class="hex-content">
                                            <span class="hex-content-inner">
                                                <span class="icon">
                                                    <i class="fa fa-map-signs"></i>
                                                </span>
                                                <span class="title">Logout</span>
                                            </span>
                                            <svg viewBox="0 0 173.20508075688772 200" height="200" width="174" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z" fill="#1e2530"></path></svg>
                                        </a>
                                    </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          
                      </div>
                  </div>
    </main>
    <lucky-draw @back="back" v-if="tab == 'lucky-draw'"></lucky-draw>
    <my-store @back="back" v-if="tab == 'my-store'"></my-store>
</div>

<!-- partial -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.23/vue.global.js"
  integrity="sha512-YnR/JFLhewG9bLSWd+nS31de8rOMjmiW3PoR+0EfxpydKwpOE7VIX2cWfht+xaXjdpvJejl0zUZxzENLs7Mh1g=="
  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  const js = document.createElement('script');
  js.type = 'text/javascript';
  js.src = 'app.js?_=' + Date.now()
  document.body.appendChild(js);
</script>
</body>
</html>
